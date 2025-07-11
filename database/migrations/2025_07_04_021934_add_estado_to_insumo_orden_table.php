<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('insumo_orden', function (Blueprint $table) {
            if (!Schema::hasColumn('insumo_orden', 'estado')) {
                $table->enum('estado', ['pendiente', 'liberado', 'solicitado'])->default('pendiente');
            }
        });
    }

    public function down(): void
    {
        Schema::table('insumo_orden', function (Blueprint $table) {
            if (Schema::hasColumn('insumo_orden', 'estado')) {
                $table->dropColumn('estado');
            }
        });
    }
};
