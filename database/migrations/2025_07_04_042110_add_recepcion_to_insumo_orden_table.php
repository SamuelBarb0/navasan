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
        Schema::table('insumo_orden', function (Blueprint $table) {
            $table->integer('cantidad_recibida')->nullable()->after('cantidad_requerida');
            $table->enum('tipo_recepcion', ['inventario', 'compra'])->nullable()->after('estado');
            $table->string('factura_archivo')->nullable()->after('tipo_recepcion');
            $table->timestamp('fecha_recepcion')->nullable()->after('factura_archivo');
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('insumo_orden', function (Blueprint $table) {
            //
        });
    }
};
