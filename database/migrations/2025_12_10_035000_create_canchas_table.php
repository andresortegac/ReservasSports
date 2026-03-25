<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('canchas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_id')->nullable()->constrained('canchas')->nullOnDelete();
            $table->unsignedTinyInteger('orden')->default(1);
            $table->string('nombre');
            $table->string('tipo')->default('sintetica');
            $table->unsignedTinyInteger('subcanchas_count')->default(2);
            $table->decimal('precio_hora', 10, 2)->default(0);
            $table->time('hora_apertura')->default('06:00:00');
            $table->time('hora_cierre')->default('23:00:00');
            $table->unsignedSmallInteger('intervalo_minutos')->default(60);
            $table->boolean('activa')->default(true);
            $table->enum('estado_operativo', ['disponible', 'mantenimiento'])->default('disponible');
            $table->text('descripcion')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('canchas');
    }
};
