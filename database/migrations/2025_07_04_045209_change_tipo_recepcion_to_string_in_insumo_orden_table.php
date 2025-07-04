<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('insumo_orden', function (Blueprint $table) {
            // Primero elimina el enum si existe (MySQL lo requiere)
            $table->dropColumn('tipo_recepcion');
        });

        Schema::table('insumo_orden', function (Blueprint $table) {
            $table->string('tipo_recepcion')->nullable()->after('cantidad_recibida');
        });
    }

    public function down(): void
    {
        Schema::table('insumo_orden', function (Blueprint $table) {
            $table->dropColumn('tipo_recepcion');
        });

        Schema::table('insumo_orden', function (Blueprint $table) {
            $table->enum('tipo_recepcion', ['compra', 'inventario'])->nullable();
        });
    }
};
