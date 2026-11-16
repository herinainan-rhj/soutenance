<?php

namespace App\Http\Controllers;

use App\Models\Question;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class QuestionController extends Controller
{
    /**
     * Importer les questions depuis un fichier TXT.
     *
     * Format attendu :
     *
     * PYTHON
     *
     * Question 1 : Quel mot-clé permet de définir une fonction en Python ?
     * A - function
     * B - def
     * C - func
     * D - define
     * Réponse : B
     *
     * Les réponses multiples sont également acceptées :
     *
     * Réponse : A, C
     */
    public function import(Request $request)
    {
        $request->validate([
            'fichier' => 'required|file|mimes:txt|max:5120',
        ]);

        $file = $request->file('fichier');

        if (!$file || !$file->isValid()) {
            return response()->json([
                'success' => false,
                'message' => 'Le fichier envoyé est invalide.'
            ], 422);
        }

        $contenu = file_get_contents($file->getRealPath());

        if ($contenu === false) {
            return response()->json([
                'success' => false,
                'message' => 'Impossible de lire le fichier.'
            ], 500);
        }

        // Gestion des différents types de retour à la ligne
        $contenu = str_replace(
            ["\r\n", "\r"],
            "\n",
            $contenu
        );

        $lignes = explode("\n", $contenu);

        $categorieActuelle = null;
        $questionActuelle = null;

        $questionsAImporter = [];

        foreach ($lignes as $ligne) {

            $ligne = trim($ligne);

            // Ligne vide
            if ($ligne === '') {
                continue;
            }

            /*
             * ---------------------------------------------------------
             * 1. Détection de la catégorie
             * ---------------------------------------------------------
             *
             * Exemple :
             * PYTHON
             * PHP
             * LARAVEL
             * JAVA
             * REACT
             */
            if (
                preg_match(
                    '/^(PYTHON|PHP|LARAVEL|JAVA|REACT)$/iu',
                    $ligne,
                    $matches
                )
            ) {
                $categorieActuelle = strtoupper(
                    $matches[1]
                );

                continue;
            }

            /*
             * ---------------------------------------------------------
             * 2. Détection de la question
             * ---------------------------------------------------------
             *
             * Exemple :
             * Question 1 : Quel mot-clé permet de définir une fonction ?
             */
            if (
                preg_match(
                    '/^Question\s+\d+\s*:\s*(.+)$/iu',
                    $ligne,
                    $matches
                )
            ) {
                $questionActuelle = [
                    'categorie' => $categorieActuelle,
                    'question' => trim($matches[1]),
                    'choices' => [],
                    'correctes' => [],
                ];

                continue;
            }

            /*
             * ---------------------------------------------------------
             * 3. Détection des choix
             * ---------------------------------------------------------
             *
             * Exemple :
             * A - function
             * B - def
             * C - func
             * D - define
             *
             * On accepte également :
             * A: function
             * B: def
             */
            if (
                $questionActuelle !== null &&
                preg_match(
                    '/^([A-Z])\s*[-:]\s*(.+)$/u',
                    $ligne,
                    $matches
                )
            ) {
                $lettre = strtoupper(
                    trim($matches[1])
                );

                $texte = trim(
                    $matches[2]
                );

                $questionActuelle['choices'][$lettre] = $texte;

                continue;
            }

            /*
             * ---------------------------------------------------------
             * 4. Détection de la réponse correcte
             * ---------------------------------------------------------
             *
             * Exemple :
             * Réponse : B
             *
             * ou :
             *
             * Réponse : A, C
             */
            if (
                $questionActuelle !== null &&
                preg_match(
                    '/^Réponse\s*:\s*(.+)$/iu',
                    $ligne,
                    $matches
                )
            ) {
                $reponses = strtoupper(
                    trim($matches[1])
                );

                /*
                 * On transforme :
                 *
                 * A, C
                 *
                 * en :
                 *
                 * ['A', 'C']
                 */
                $correctes = preg_split(
                    '/\s*,\s*/',
                    $reponses
                );

                $correctes = array_map(
                    function ($lettre) {
                        return strtoupper(
                            trim($lettre)
                        );
                    },
                    $correctes
                );

                $correctes = array_values(
                    array_filter(
                        $correctes,
                        function ($lettre) {
                            return preg_match(
                                '/^[A-Z]$/',
                                $lettre
                            );
                        }
                    )
                );

                $questionActuelle['correctes'] = $correctes;

                /*
                 * La question est maintenant complète.
                 */
                $questionsAImporter[] = $questionActuelle;

                $questionActuelle = null;

                continue;
            }
        }

        /*
         * -------------------------------------------------------------
         * Vérification
         * -------------------------------------------------------------
         */
        if (empty($questionsAImporter)) {
            return response()->json([
                'success' => false,
                'message' => 'Aucune question valide trouvée dans le fichier.'
            ], 422);
        }

        /*
         * -------------------------------------------------------------
         * Importation en base de données
         * -------------------------------------------------------------
         */
        try {

            DB::transaction(function () use ($questionsAImporter) {

                /*
                 * Supprimer les anciennes questions.
                 *
                 * Les choices seront supprimées automatiquement
                 * si la clé étrangère ON DELETE CASCADE est configurée.
                 */
                Question::query()->delete();

                foreach ($questionsAImporter as $questionData) {

                    $question = Question::create([
                        'categorie' => $questionData['categorie'],
                        'question' => $questionData['question'],
                    ]);

                    foreach ($questionData['choices'] as $lettre => $texte) {

                        $question->choices()->create([
                            'lettre' => $lettre,
                            'texte' => $texte,
                            'correcte' => in_array(
                                $lettre,
                                $questionData['correctes'],
                                true
                            ),
                        ]);
                    }
                }
            });

            /*
             * ---------------------------------------------------------
             * Sauvegarder également le fichier original
             * ---------------------------------------------------------
             */
            Storage::disk('local')->put(
                'questionnaire.txt',
                $contenu
            );

            return response()->json([
                'success' => true,
                'message' => 'Questionnaire importé avec succès.',
                'nombre_questions' => count($questionsAImporter),
                'nombre_choix' => array_sum(
                    array_map(
                        function ($question) {
                            return count($question['choices']);
                        },
                        $questionsAImporter
                    )
                ),
            ]);

        } catch (\Throwable $e) {

            return response()->json([
                'success' => false,
                'message' => 'Une erreur est survenue pendant l\'importation.',
                'erreur' => $e->getMessage(),
            ], 500);
        }
    }


    /**
     * Retourner toutes les questions avec leurs choix.
     *
     * GET /api/questions
     */
    public function index()
    {
        $questions = Question::with([
            'choices' => function ($query) {
                $query->orderBy('lettre');
            }
        ])
        ->orderBy('id')
        ->get();

        return response()->json($questions);
    }


    /**
     * Retourner des questions aléatoires.
     *
     * GET /api/questions/random?nombre=10
     *
     * IMPORTANT :
     * La bonne réponse "correcte" n'est PAS envoyée au frontend.
     */
    public function random(Request $request)
    {
        $nombre = (int) $request->input(
            'nombre',
            10
        );

        /*
         * Minimum : 1
         */
        if ($nombre < 1) {
            $nombre = 1;
        }

        /*
         * Maximum : 200
         */
        if ($nombre > 200) {
            $nombre = 200;
        }

        $questions = Question::with([
            'choices' => function ($query) {

                /*
                 * On sélectionne volontairement uniquement :
                 *
                 * id
                 * question_id
                 * lettre
                 * texte
                 *
                 * PAS "correcte".
                 */
                $query->select(
                    'id',
                    'question_id',
                    'lettre',
                    'texte'
                )
                ->orderBy('lettre');
            }
        ])
        ->inRandomOrder()
        ->limit($nombre)
        ->get([
            'id',
            'categorie',
            'question'
        ]);

        return response()->json($questions);
    }


    /**
     * Corriger le test d'un candidat.
     *
     * POST /api/questions/submit
     *
     * Format envoyé par React :
     *
     * {
     *     "candidate_id": 1,
     *     "reponses": {
     *         "15": ["B"],
     *         "72": ["A", "C"]
     *     }
     * }
     */
    public function submit(Request $request)
    {
        $candidateId = $request->input(
            'candidate_id'
        );

        $reponses = $request->input(
            'reponses'
        );

        /*
         * ---------------------------------------------------------
         * Vérifier le candidat
         * ---------------------------------------------------------
         */
        if (!$candidateId) {

            return response()->json([
                'success' => false,
                'message' => 'Le candidat est obligatoire.'
            ], 422);
        }

        /*
         * ---------------------------------------------------------
         * Vérifier les réponses
         * ---------------------------------------------------------
         */
        if (!is_array($reponses)) {

            return response()->json([
                'success' => false,
                'message' => 'Les réponses sont invalides.'
            ], 422);
        }

        /*
         * ---------------------------------------------------------
         * Chercher le candidat
         * ---------------------------------------------------------
         */
        $candidate = \App\Models\Candidate::find(
            $candidateId
        );

        if (!$candidate) {

            return response()->json([
                'success' => false,
                'message' => 'Candidat introuvable.'
            ], 404);
        }

        /*
         * ---------------------------------------------------------
         * Récupérer les IDs des questions
         * ---------------------------------------------------------
         */
        $questionIds = array_keys(
            $reponses
        );

        if (empty($questionIds)) {

            return response()->json([
                'success' => false,
                'message' => 'Aucune réponse n\'a été envoyée.'
            ], 422);
        }

        /*
         * ---------------------------------------------------------
         * Récupérer les questions avec leurs bonnes réponses
         * ---------------------------------------------------------
         */
        $questions = Question::with('choices')
            ->whereIn(
                'id',
                $questionIds
            )
            ->get();

        if ($questions->isEmpty()) {

            return response()->json([
                'success' => false,
                'message' => 'Aucune question valide trouvée.'
            ], 422);
        }

        /*
         * ---------------------------------------------------------
         * Calcul du score
         * ---------------------------------------------------------
         */
        $nombreQuestions = $questions->count();

        $nombreCorrectes = 0;

        foreach ($questions as $question) {

            /*
             * Récupérer les bonnes réponses.
             *
             * Exemple :
             *
             * ['A', 'C']
             */
            $bonnesReponses = $question->choices
                ->where(
                    'correcte',
                    true
                )
                ->pluck('lettre')
                ->map(
                    function ($lettre) {
                        return strtoupper(
                            trim($lettre)
                        );
                    }
                )
                ->sort()
                ->values()
                ->toArray();

            /*
             * Réponses données par le candidat.
             */
            $reponsesCandidat =
                $reponses[$question->id]
                ?? [];

            /*
             * Si une seule réponse est envoyée
             * au lieu d'un tableau.
             */
            if (!is_array($reponsesCandidat)) {

                $reponsesCandidat = [
                    $reponsesCandidat
                ];
            }

            /*
             * Nettoyer les réponses.
             */
            $reponsesCandidat = array_map(
                function ($lettre) {

                    return strtoupper(
                        trim($lettre)
                    );
                },
                $reponsesCandidat
            );

            /*
             * Supprimer les doublons.
             */
            $reponsesCandidat = array_values(
                array_unique(
                    $reponsesCandidat
                )
            );

            /*
             * Trier les réponses.
             *
             * Cela permet de considérer :
             *
             * ['A', 'C']
             *
             * et :
             *
             * ['C', 'A']
             *
             * comme identiques.
             */
            sort(
                $reponsesCandidat
            );

            /*
             * Comparaison exacte.
             *
             * Exemple :
             *
             * bonnes = ['A', 'C']
             * candidat = ['A', 'C']
             *
             * => correct
             *
             * bonnes = ['A', 'C']
             * candidat = ['A']
             *
             * => faux
             */
            if (
                $reponsesCandidat ===
                $bonnesReponses
            ) {

                $nombreCorrectes++;
            }
        }

        /*
         * ---------------------------------------------------------
         * Calcul du pourcentage
         * ---------------------------------------------------------
         */
        $pourcentage = 0;

        if ($nombreQuestions > 0) {

            $pourcentage = round(
                (
                    $nombreCorrectes
                    / $nombreQuestions
                ) * 100,
                2
            );
        }

        /*
         * ---------------------------------------------------------
         * Etat du candidat
         * ---------------------------------------------------------
         *
         * 50% ou plus = accepté
         * moins de 50% = refusé
         */
        if ($pourcentage >= 50) {

            $etat = 'accepte';

        } else {

            $etat = 'refuse';
        }

        /*
         * ---------------------------------------------------------
         * Enregistrer le résultat
         * ---------------------------------------------------------
         */
        $candidate->note_test =
            $pourcentage;

        $candidate->etat_candidature =
            $etat;

        $candidate->save();

        /*
         * ---------------------------------------------------------
         * Réponse JSON
         * ---------------------------------------------------------
         */
        return response()->json([
            'success' => true,
            'message' => 'Test corrigé avec succès.',
            'nombre_questions' => $nombreQuestions,
            'bonnes_reponses' => $nombreCorrectes,
            'pourcentage' => $pourcentage,
            'etat' => $etat,
        ]);
    }
}
