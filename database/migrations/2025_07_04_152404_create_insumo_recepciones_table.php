<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('insumo_recepciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('insumo_id')->constrained()->onDelete('cascade');
            $table->decimal('cantidad_recibida', 10, 2);
            $table->string('tipo_recepcion')->nullable();
            $table->date('fecha_recepcion')->nullable();
            $table->string('archivo_factura')->nullable(); // Ruta al archivo, si aplica
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('insumo_recepciones');
    }
};
