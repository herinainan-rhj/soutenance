<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Job extends Model
{
    protected $table = 'offres';
    protected $fillable = [
        'titre',
        'description',
        'departement',
        'localisation',
        'type_contrat',
        'niveau_etude',
        'experience_requise',
        'competences',
        'statut'
    ];
}
