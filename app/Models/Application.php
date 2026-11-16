<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Candidate extends Model
{
    protected $fillable = [
        'job_id',
        'nom',
        'prenom',
        'adresse',
        'email',
        'cv',
    ];
}
