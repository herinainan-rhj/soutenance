<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('candidates', function (Blueprint $table) {
            // Supprimer l'ancienne clé étrangère qui pointe vers jobs
            $table->dropForeign('candidates_job_id_foreign');

            // Créer la bonne clé étrangère vers offres
            $table->foreign('job_id')
                ->references('id')
                ->on('offres')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('candidates', function (Blueprint $table) {
            // Supprimer la clé étrangère vers offres
            $table->dropForeign('candidates_job_id_foreign');

            // Restaurer l'ancienne relation vers jobs
            $table->foreign('job_id')
                ->references('id')
                ->on('jobs')
                ->onDelete('cascade');
        });
    }
};
