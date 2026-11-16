<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Job;
use Illuminate\Http\Request;

class JobController extends Controller
{
    public function index()
    {
        return response()->json(
            Job::latest()->get()
        );
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'titre' => 'required|string|max:255',
            'description' => 'required',
            'departement' => 'required',
            'localisation' => 'required',
            'type_contrat' => 'required',
            'niveau_etude' => 'required',
            'experience_requise' => 'required|integer',
            'competences' => 'required'
        ]);

        $job = Job::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Offre créée avec succès',
            'data' => $job
        ], 201);
    }

    public function update(Request $request, Job $job)
    {
        $job->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Offre mise à jour',
            'data' => $job
        ]);
    }

    public function destroy(Job $job)
    {
        $job->delete();

        return response()->json([
            'success' => true,
            'message' => 'Offre supprimée'
        ]);
    }
}
