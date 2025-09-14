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
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();

            // Ключ вида "t1", "t2" для маршрутизации
            $table->unsignedBigInteger('sub_lessons_id');

            // input | choice | audio_choice
            $table->enum('type', ['input','choice','audio_choice']);

            // Тексты
            $table->string('ru');
            $table->string('tt')->nullable();

            // Медиа для аудио-задач
            $table->string('audio')->nullable();

            // JSON-поля
            $table->json('answers')->nullable();       // для input
            $table->json('options')->nullable();       // для choice
            $table->unsignedInteger('correct_index')->nullable(); // для choice

            // Порядок вывода
            $table->unsignedInteger('sort')->default(0);

            $table->index(['sub_lessons_id']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
