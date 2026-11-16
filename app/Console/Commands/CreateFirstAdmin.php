<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class CreateFirstAdmin extends Command
{
    protected $signature = 'admin:create';
    protected $description = 'Crée le premier administrateur (compte validé)';

    public function handle()
    {
        $email = $this->ask('Email de l’admin ?');
        $password = $this->secret('Mot de passe de l’admin ?');
        $name = $this->ask('Nom de l’admin ?');

        User::create([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make($password),
            'status' => 'valide',
        ]);

        $this->info('Premier administrateur créé avec succès !');
    }
}
