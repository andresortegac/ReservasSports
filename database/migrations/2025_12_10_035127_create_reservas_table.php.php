<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
   // database/migrations/xxxx_xx_xx_create_reservas_table.php

public function up()
{
    Schema::create('reservas', function (Blueprint $table) {
        $table->id();

        $table->foreignId('cliente_id')->constrained('clientes')->cascadeOnDelete();

        $table->unsignedBigInteger('cancha_id');        // cancha (1,2,3…)
        $table->unsignedTinyInteger('subcancha'); // subcancha 1 ó 2

        $table->date('fecha');
        $table->time('hora');                           // Hora de reserva (slot)

        $table->decimal('precio', 10, 2)->default(0);
        $table->enum('estado', ['pendiente', 'pagada', 'cancelada'])->default('pendiente');

        $table->timestamps();

        // ✅ NO permitir dos reservas para misma cancha + subcancha + fecha + hora
        $table->unique(
            ['cancha_id', 'subcancha', 'fecha', 'hora'],
            'reservas_unicas_subcancha'
        );
    });
}
 

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
