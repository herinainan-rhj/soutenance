<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Qcm extends Model
{
    protected $fillable = [
        'candidate_id',
        'question',
        'option_a',
        'option_b',
        'option_c',
        'option_d',
        'bonne_reponse'
    ];
}
