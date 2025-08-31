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
        Schema::create('masks', function (Blueprint $t) {
            $t->id();
            $t->string('title');
            $t->json('viewbox'); // [0,0,320,320]
            $t->text('path');    // SVG path d
            $t->json('meta')->default(DB::raw('(JSON_OBJECT())'));
            $t->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('masks');
    }
};
