<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('facturaciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('orden_id')->constrained('orden_produccions')->onDelete('cascade');
            $table->integer('cantidad_final');
            $table->decimal('costo_unitario', 10, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);
            $table->enum('estado_facturacion', ['pendiente', 'facturado', 'entregado'])->default('pendiente');
            $table->date('fecha_entrega');
            $table->string('metodo_entrega')->nullable();
            $table->timestamps();
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('facturaciones');
    }
};
