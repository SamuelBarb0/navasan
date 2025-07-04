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
        Schema::create('item_ordens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('orden_produccion_id')->constrained()->onDelete('cascade');
            $table->string('nombre'); // ej. MILK TOUCH
            $table->integer('cantidad');
            $table->timestamps();
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('item_ordens');
    }
};
