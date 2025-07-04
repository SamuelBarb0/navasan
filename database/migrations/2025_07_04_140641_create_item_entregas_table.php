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
        Schema::create('item_entregas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_orden_id')->constrained('item_ordens')->onDelete('cascade');
            $table->date('fecha_entrega');
            $table->integer('cantidad');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('item_entregas');
    }
};
