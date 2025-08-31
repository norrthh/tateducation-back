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
        Schema::create('lessons', function (Blueprint $t) {
            $t->id();
            $t->string('slug')->unique();
            $t->string('title');
            $t->string('level', 4); // A0|A1
            $t->string('status', 16)->default('published');
            $t->json('meta')->default(DB::raw('(JSON_OBJECT())'));
            $t->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lessons');
    }
};
