<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('inventario_etiquetas', function (Blueprint $table) {
            $table->unsignedBigInteger('cliente_id')->nullable()->after('producto_id');
            $table->string('imagen_path')->nullable()->after('cliente_id'); // ruta en storage/public

            $table->foreign('cliente_id')->references('id')->on('clientes')->nullOnDelete();
        });
    }

    public function down(): void {
        Schema::table('inventario_etiquetas', function (Blueprint $table) {
            $table->dropForeign(['cliente_id']);
            $table->dropColumn(['cliente_id', 'imagen_path']);
        });
    }
};