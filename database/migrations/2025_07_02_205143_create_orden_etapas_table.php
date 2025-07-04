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
        Schema::create('orden_etapas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('orden_produccion_id')->constrained()->onDelete('cascade');
            $table->foreignId('etapa_produccion_id')->constrained()->onDelete('cascade');
            $table->foreignId('usuario_id')->nullable()->constrained('users')->nullOnDelete(); // responsable
            $table->enum('estado', ['pendiente', 'en_proceso', 'completado', 'rechazado'])->default('pendiente');
            $table->timestamp('inicio')->nullable();
            $table->timestamp('fin')->nullable();
            $table->text('observaciones')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orden_etapas');
    }
};
