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
        Schema::create('insumos', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('unidad')->nullable(); // ml, metros, etc.
            $table->timestamps();
        });

        Schema::create('insumo_orden', function (Blueprint $table) {
            $table->id();
            $table->foreignId('orden_produccion_id')->constrained()->onDelete('cascade');
            $table->foreignId('insumo_id')->constrained()->onDelete('cascade');
            $table->integer('cantidad_requerida');
            $table->enum('estado', ['liberado', 'pendiente', 'solicitado'])->default('pendiente');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('insumos');
    }
};
