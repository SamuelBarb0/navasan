<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('item_ordens', function (Blueprint $table) {
            if (!Schema::hasColumn('item_ordens', 'producto_id')) {
                $table->foreignId('producto_id')
                    ->nullable()
                    ->constrained('productos')
                    ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('item_ordens', function (Blueprint $table) {
            // Elimina la clave foránea primero (por si existe)
            if (Schema::hasColumn('item_ordens', 'producto_id')) {
                $table->dropForeign(['producto_id']);
                $table->dropColumn('producto_id');
            }
        });
    }
};
