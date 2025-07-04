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
        Schema::create('impresiones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('orden_id')->constrained('orden_produccions')->onDelete('cascade');
            $table->string('tipo_impresion'); // MO, GTO, etc.
            $table->string('maquina')->nullable();
            $table->integer('cantidad_pliegos')->nullable();
            $table->dateTime('inicio_impresion')->nullable();
            $table->dateTime('fin_impresion')->nullable();
            $table->enum('estado', ['espera', 'proceso', 'completado', 'rechazado'])->default('espera');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('impresiones');
    }
};
