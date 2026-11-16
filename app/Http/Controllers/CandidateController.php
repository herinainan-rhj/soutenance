<?php

namespace App\Http\Controllers;

use App\Models\Candidate;
use App\Models\Job;
use Illuminate\Http\Request;
use App\Services\PdfReaderService;
use App\Services\CvAnalyzerService;


class CandidateController extends Controller
{
    public function store(
        Request $request,
        PdfReaderService $pdfReader,
        CvAnalyzerService $analyzer
    ) {


        $validated = $request->validate([
            'job_id' => 'required|exists:offres,id',
            'nom' => 'required|string|max:255',
            'prenom' => 'required|string|max:255',
            'adresse' => 'required|string|max:255',
            'email' => 'required|email|max:255',
        ]);


        if (!$request->hasFile('cv')) {
            return response()->json([
                'success' => false,
                'message' => 'Le fichier CV n\'a pas été reçu par Laravel.'
            ], 422);
        }

        $cv = $request->file('cv');

        if (!$cv->isValid()) {
            return response()->json([
                'success' => false,
                'message' => 'Le fichier CV envoyé est invalide.'
            ], 422);
        }

        $extension = strtolower(
            $cv->getClientOriginalExtension()
        );

        if ($extension !== 'pdf') {
            return response()->json([
                'success' => false,
                'message' => 'Le CV doit être un fichier PDF.'
            ], 422);
        }


        if ($cv->getSize() > 5 * 1024 * 1024) {
            return response()->json([
                'success' => false,
                'message' => 'Le CV ne doit pas dépasser 5 Mo.'
            ], 422);
        }

        $realPath = $cv->getRealPath();

        if (!$realPath || !file_exists($realPath)) {
            return response()->json([
                'success' => false,
                'message' => 'Impossible d\'accéder au fichier CV.'
            ], 422);
        }

        $handle = fopen($realPath, 'rb');

        if ($handle === false) {
            return response()->json([
                'success' => false,
                'message' => 'Impossible de lire le fichier CV.'
            ], 422);
        }

        $header = fread($handle, 5);

        fclose($handle);

        if ($header !== '%PDF-') {
            return response()->json([
                'success' => false,
                'message' => 'Le fichier sélectionné n\'est pas un PDF valide.'
            ], 422);
        }


        $cvPath = $cv->store('cvs', 'public');

        if (!$cvPath) {
            return response()->json([
                'success' => false,
                'message' => 'Impossible d\'enregistrer le CV.'
            ], 500);
        }


        $pdfPath = storage_path(
            'app/public/' . $cvPath
        );


        try {
            $cvText = $pdfReader->extractText(
                $pdfPath
            );
        } catch (\Throwable $e) {

            return response()->json([
                'success' => false,
                'message' => 'Impossible de lire le contenu du CV PDF.',
                'erreur' => $e->getMessage()
            ], 500);
        }


        $job = Job::findOrFail(
            $request->job_id
        );


        try {

            $pourcentage = $analyzer->analyze(
                $cvText,
                $job->competences,
                $job->niveau_etude,
                $job->experience_requise
            );

        } catch (\Throwable $e) {

            return response()->json([
                'success' => false,
                'message' => 'Une erreur est survenue pendant l\'analyse du CV.',
                'erreur' => $e->getMessage()
            ], 500);
        }


        $etat = 'refuse';

        if ($pourcentage >= 50) {
            $etat = 'test_a_passer';
        }


        $candidate = Candidate::create([
            'job_id' => $job->id,

            'nom' => $request->nom,

            'prenom' => $request->prenom,

            'adresse' => $request->adresse,

            'email' => $request->email,

            'cv' => $cvPath,

            'note_cv' => $pourcentage,

            'note_test' => null,

            'etat_candidature' => $etat,
        ]);


        return response()->json([

            'success' => true,

            'message' => 'Candidature enregistrée',

            'pourcentage' => $pourcentage,

            'etat' => $etat,

            'candidate' => $candidate

        ]);
    }
    public function index()
    {
        $candidates = Candidate::with('job')
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'candidates' => $candidates,
        ]);
    }

    public function downloadCv(Candidate $candidate)
    {
        if (!$candidate->cv) {
            return response()->json([
                'success' => false,
                'message' => 'CV non disponible.'
            ], 404);
        }

        $path = storage_path(
            'app/public/' . $candidate->cv
        );

        if (!file_exists($path)) {
            return response()->json([
                'success' => false,
                'message' => 'Le fichier CV est introuvable.'
            ], 404);
        }

        return response()->download($path);
    }
}
