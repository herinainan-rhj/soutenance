<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Question extends Model
{
    protected $fillable = [
        'categorie',
        'question',
    ];

    public function choices(): HasMany
    {
        return $this->hasMany(Choice::class);
    }
}
