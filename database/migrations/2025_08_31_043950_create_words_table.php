<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('words', function (Blueprint $t) {
            $t->id();
            $t->string('lemma_tt', 255);
            $t->text('lemma_ru');
            $t->string('pos', 16)->nullable();
            $t->text('audio_url')->nullable();
            $t->json('meta')->default(DB::raw('(JSON_OBJECT())'));
            $t->timestamps();
            $t->index('lemma_tt');
        });

        Schema::create('user_words', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->constrained()->cascadeOnDelete();
            $t->foreignId('word_id')->constrained()->cascadeOnDelete();
            $t->decimal('ef', 3, 2)->default(2.5);
            $t->integer('interval_days')->default(0);
            $t->date('due_at')->nullable();
            $t->decimal('strength', 3, 2)->default(0.0);
            $t->timestamps();
            $t->unique(['user_id','word_id']);
            $t->index('due_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('words');
        Schema::dropIfExists('user_words');
    }
};
