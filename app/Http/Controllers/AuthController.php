<?php

namespace App\Http\Controllers;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use App\Models\User;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name'     => 'required|string|max:191',
            'email'    => 'required|email|max:191|unique:users',
        ]);

        // Création du compte en attente (sans mot de passe pour l'instant)
        $user = User::create([
            'name'     => $validated['name'],
            'email'    => $validated['email'],
            'password' => null, // Autorisé si la colonne password est nullable
            'status'   => 'en_attente', // Statut par défaut
        ]);

        return response()->json([
            'message' => 'Inscription réussie ! Votre compte est en attente de validation par un administrateur.',
            'user'    => $user,
        ], 201);
    }

    public function login(Request $request)
    {
        $validated = $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $validated['email'])->first();

        // Vérification de l'existence de l'utilisateur et du mot de passe
        if (! $user || ! $user->password || ! Hash::check($validated['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Les identifiants sont incorrects.'],
            ]);
        }

        // 🛑 BLOCAGE : Empêcher la connexion si le compte n'est pas validé
        if ($user->status !== 'valide') {
            $message = $user->status === 'refuse' 
                ? 'Votre compte a été refusé.' 
                : 'Votre compte est toujours en attente de validation.';

            throw ValidationException::withMessages([
                'email' => [$message],
            ]);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Connexion réussie.',
            'user'    => $user,
            'token'   => $token,
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Déconnecté avec succès.']);
    }

    public function me(Request $request)
    {
        return response()->json($request->user());
    }
}