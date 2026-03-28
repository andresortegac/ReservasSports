<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('canchas', function (Blueprint $table) {
            $table->string('integration_identifier')->nullable()->unique()->after('nombre');
            $table->string('integration_token')->nullable()->after('integration_identifier');
        });
    }

    public function down(): void
    {
        Schema::table('canchas', function (Blueprint $table) {
            $table->dropUnique(['integration_identifier']);
            $table->dropColumn(['integration_identifier', 'integration_token']);
        });
    }
};
