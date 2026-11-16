<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;

class AdminUserController extends Controller
{
    // Afficher la liste de tous les utilisateurs pour l'admin
    public function index()
    {
        return response()->json(User::all());
    }

    // Mettre à jour le statut d'un utilisateur (valide ou refuse)
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:valide,refuse'
        ]);

        $user = User::findOrFail($id);
        $user->status = $request->status;
        $user->save();

        // Si le compte est validé, on génère un token et on envoie un e-mail
        if ($request->status === 'valide') {
            $token = Password::createToken($user);
            $url = "http://localhost:5173/reset-password?token=$token&email=" . urlencode($user->email);

            Mail::raw("Votre compte a été validé avec succès. Cliquez sur ce lien pour créer votre mot de passe : $url", function ($message) use ($user) {
                $message->to($user->email)->subject('Validation de votre compte - E RAY');
            });
        }

        return response()->json([
            'message' => 'Statut mis à jour avec succès.',
            'user' => $user
        ]);
    }
}
