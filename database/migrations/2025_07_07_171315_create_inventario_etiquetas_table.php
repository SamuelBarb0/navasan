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
        Schema::create('inventario_etiquetas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('orden_id');
            $table->integer('cantidad');
            $table->date('fecha_programada')->nullable(); // entrega futura opcional
            $table->boolean('alertado')->default(false); // para saber si ya se alertó
            $table->timestamps();

            $table->foreign('orden_id')->references('id')->on('orden_produccions')->onDelete('cascade');
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventario_etiquetas');
    }
};
