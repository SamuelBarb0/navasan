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
        Schema::create('devoluciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('orden_id')->constrained('orden_produccions')->onDelete('cascade');
            $table->text('motivo_cliente');
            $table->string('revisadora_asignada');
            $table->string('tipo_error')->nullable();
            $table->boolean('codigo_rojo')->default(false);
            $table->text('comentarios_adicionales')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('devoluciones');
    }
};
