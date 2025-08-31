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
        Schema::create('attempts', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $t->foreignId('step_id')->constrained()->cascadeOnDelete();
            $t->jsonb('input');
            $t->decimal('score', 3, 2)->default(0);
            $t->json('meta')->default(DB::raw('(JSON_OBJECT())'));
            $t->timestamps();
            $t->unsignedInteger('took_ms')->default(0);
            $t->boolean('used_ai')->default(false);
            $t->index(['user_id', 'step_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attempts');
    }
};
