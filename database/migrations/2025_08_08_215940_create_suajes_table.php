<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('suajes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('orden_id')->constrained('orden_produccions')->onDelete('cascade');
            $table->enum('proceso', ['suaje', 'corte_guillotina']);
            $table->string('realizado_por');
            $table->integer('cantidad_pliegos_impresos')->nullable(); // Nuevo campo
            $table->timestamp('fecha_fin')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('suajes');
    }
};
