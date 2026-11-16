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
        'Telephone',
        'email',
        'cv',
        'note_cv',
        'note_test',
        'etat_candidature',
    ];

    protected $casts = [
        'note_cv' => 'decimal:2',
        'note_test' => 'decimal:2',
    ];

    public function job()
    {
        return $this->belongsTo(Job::class);
    }
}
