<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\CandidateController;
use App\Http\Controllers\QuestionController;

use App\Http\Controllers\Admin\JobController as AdminJobController;
use App\Http\Controllers\PublicSite\JobController as PublicJobController;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminUserController;

use App\Http\Controllers\NewPasswordController;

Route::post('/reset-password', [NewPasswordController::class, 'store']);

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Routes protégées (nécessitent d'être connecté, par exemple avec Sanctum)
Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/admin/users', [AdminUserController::class, 'index']);
    Route::post('/admin/users/{id}/status', [AdminUserController::class, 'updateStatus']);
});


// =====================================================
// OFFRES D'EMPLOI - PUBLIC
// =====================================================

Route::get('/jobs', [PublicJobController::class, 'index']);

Route::get('/jobs/{job}', [PublicJobController::class, 'show']);


// =====================================================
// OFFRES D'EMPLOI - ADMIN
// =====================================================

Route::get('/admin/jobs', [AdminJobController::class, 'index']);

Route::post('/admin/jobs', [AdminJobController::class, 'store']);

Route::put('/admin/jobs/{job}', [AdminJobController::class, 'update']);

Route::delete('/admin/jobs/{job}', [AdminJobController::class, 'destroy']);


// =====================================================
// CANDIDATURES
// =====================================================

// Envoyer une candidature + CV
Route::post('/candidates', [CandidateController::class, 'store']);

// Récupérer la liste des candidats
Route::get('/candidates', [CandidateController::class, 'index']);

// Télécharger le CV d'un candidat
Route::get('/candidates/{candidate}/cv', [CandidateController::class, 'downloadCv']);


// =====================================================
// QUESTIONS / TEST
// =====================================================

// Importer les questions depuis un fichier
Route::post('/questions/import', [QuestionController::class, 'import']);

// Toutes les questions
Route::get('/questions', [QuestionController::class, 'index']);

// Questions aléatoires
Route::get('/questions/random', [QuestionController::class, 'random']);

// Soumettre les réponses du test
Route::post('/questions/submit', [QuestionController::class, 'submit']);


// =====================================================
// UTILISATEUR AUTHENTIFIÉ
// =====================================================

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
