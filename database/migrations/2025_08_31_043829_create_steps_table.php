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
        Schema::create('steps', function (Blueprint $t) {
            $t->id();
            $t->foreignId('lesson_id')->constrained()->cascadeOnDelete();
            $t->string('type', 24); // matching|fill_in|free_answer|trace_mask
            $t->jsonb('prompt');    // видимый контент
            $t->jsonb('answer');    // эталоны (сервер хранит)
            $t->json('meta')->default(DB::raw('(JSON_OBJECT())'));
            $t->timestamps();
            $t->smallInteger('pos')->default(1);
            $t->index(['lesson_id', 'pos']);
            $t->index('type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('steps');
    }
};
