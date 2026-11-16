<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('candidates', function (Blueprint $table) {
            $table->foreignId('job_id')
                ->constrained('offres')
                ->onDelete('cascade');

            $table->string('nom');
            $table->string('prenom');
            $table->string('adresse');
            $table->string('email');
            $table->string('cv');

            $table->decimal('note_cv', 5, 2)->nullable();
            $table->decimal('note_test', 5, 2)->nullable();

            $table->string('etat_candidature')
                ->default('en_attente');
        });
    }

    public function down(): void
    {
        Schema::table('candidates', function (Blueprint $table) {
            $table->dropForeign(['job_id']);

            $table->dropColumn([
                'job_id',
                'nom',
                'prenom',
                'adresse',
                'email',
                'cv',
                'note_cv',
                'note_test',
                'etat_candidature',
            ]);
        });
    }
};
