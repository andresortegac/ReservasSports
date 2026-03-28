<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reservas_externas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cancha_id')->constrained('canchas')->restrictOnDelete();
            $table->string('external_reference')->unique();
            $table->string('nombre_cliente');
            $table->string('telefono_cliente', 30)->nullable();
            $table->date('fecha');
            $table->time('hora');
            $table->unsignedTinyInteger('numero_subcancha')->default(1);
            $table->enum('estado', ['pendiente', 'confirmada', 'cancelada'])->default('pendiente');
            $table->text('motivo_cancelacion')->nullable();
            $table->foreignId('reserva_id')->nullable()->constrained('reservas')->nullOnDelete();
            $table->timestamp('confirmada_at')->nullable();
            $table->timestamp('cancelada_at')->nullable();
            $table->timestamps();

            $table->index(['cancha_id', 'estado']);
            $table->index(['fecha', 'hora']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reservas_externas');
    }
};
