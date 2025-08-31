<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('ai_cache', function (Blueprint $t) {
            $t->string('key', 64)->primary(); // sha1/sha256
            $t->jsonb('payload');
            $t->timestamps();
            $t->dateTime('expire_at')->nullable()->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_cache');
    }
};
