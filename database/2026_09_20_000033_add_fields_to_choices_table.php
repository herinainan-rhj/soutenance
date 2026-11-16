<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('choices', function (Blueprint $table) {
            $table->foreignId('question_id')
                ->after('id')
                ->constrained('questions')
                ->cascadeOnDelete();

            $table->string('lettre', 10)
                ->after('question_id');

            $table->text('texte')
                ->after('lettre');

            $table->boolean('correcte')
                ->default(false)
                ->after('texte');
        });
    }

    public function down(): void
    {
        Schema::table('choices', function (Blueprint $table) {
            $table->dropForeign(['question_id']);
            $table->dropColumn([
                'question_id',
                'lettre',
                'texte',
                'correcte'
            ]);
        });
    }
};
