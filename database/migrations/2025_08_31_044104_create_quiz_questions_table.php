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
        Schema::create('quiz_questions', function (Blueprint $t) {
            $t->id();
            $t->string('topic', 32)->default('general');
            $t->string('level', 4)->default('A1');
            $t->text('question_tt');
            $t->text('answer_tt');
            $t->text('hint_tt')->nullable();
            $t->text('explanation_tt')->nullable();

            $t->json('synonyms_tt')->default(DB::raw('(JSON_OBJECT())'));
            $t->json('words')->default(DB::raw('(JSON_OBJECT())'));

            $t->string('status', 16)->default('published');
            $t->timestamps();
            $t->index(['status','level']);
        });

        Schema::create('quiz_attempts', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $t->foreignId('question_id')->constrained('quiz_questions')->cascadeOnDelete();
            $t->dateTime('opened_at')->nullable();
            $t->dateTime('revealed_at')->nullable();
            $t->timestamps();;
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quiz_attempts');
        Schema::dropIfExists('quiz_questions');
    }
};
