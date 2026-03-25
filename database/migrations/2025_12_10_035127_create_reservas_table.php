<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reservas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cliente_id')->constrained('clientes')->cascadeOnDelete();
            $table->foreignId('cancha_id')->constrained('canchas')->restrictOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedTinyInteger('subcancha')->default(1);
            $table->date('fecha');
            $table->time('hora');
            $table->unsignedSmallInteger('duracion_minutos')->default(60);
            $table->decimal('precio', 10, 2)->default(0);
            $table->decimal('anticipo', 10, 2)->default(0);
            $table->decimal('saldo_pendiente', 10, 2)->default(0);
            $table->enum('estado', ['pendiente', 'confirmada', 'pagada', 'cancelada'])->default('pendiente');
            $table->enum('estado_pago', ['pendiente', 'parcial', 'pagado'])->default('pendiente');
            $table->enum('metodo_pago_principal', ['efectivo', 'transferencia', 'otro'])->nullable();
            $table->text('notas')->nullable();
            $table->timestamps();

            $table->unique(['cancha_id', 'subcancha', 'fecha', 'hora'], 'reservas_unicas_subcancha');
            $table->index(['fecha', 'estado']);
            $table->index(['cliente_id', 'fecha']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reservas');
    }
};
