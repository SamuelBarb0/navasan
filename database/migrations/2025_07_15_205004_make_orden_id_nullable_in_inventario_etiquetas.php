<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('inventario_etiquetas', function (Blueprint $table) {
            $table->unsignedBigInteger('orden_id')->nullable()->change();
            $table->unsignedBigInteger('item_orden_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('inventario_etiquetas', function (Blueprint $table) {
            $table->unsignedBigInteger('orden_id')->nullable(false)->change();
            $table->unsignedBigInteger('item_orden_id')->nullable(false)->change();
        });
    }
};

