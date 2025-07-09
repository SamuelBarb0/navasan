<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('inventario_etiquetas', function (Blueprint $table) {
            $table->unsignedBigInteger('item_orden_id')->nullable()->after('orden_id');

            // Relación opcional (si tienes tabla 'item_ordens')
            $table->foreign('item_orden_id')->references('id')->on('item_ordens')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('inventario_etiquetas', function (Blueprint $table) {
            $table->dropForeign(['item_orden_id']);
            $table->dropColumn('item_orden_id');
        });
    }
};
