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
            $table->string('tipo')->default('independiente');
            $table->unsignedTinyInteger('subcanchas_count')->default(1);
            $table->decimal('precio_hora', 10, 2)->default(0);
            $table->json('dias_operacion');
            $table->json('bloques_horarios');
            $table->enum('estado_operativo', ['disponible', 'mantenimiento', 'fuera_de_servicio'])->default('disponible');
            $table->text('descripcion')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('canchas');
    }
};
