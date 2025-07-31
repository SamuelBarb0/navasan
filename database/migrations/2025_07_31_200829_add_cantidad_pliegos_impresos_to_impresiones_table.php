<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('impresiones', function (Blueprint $table) {
            $table->integer('cantidad_pliegos_impresos')->nullable()->after('cantidad_pliegos');
        });
    }

    public function down(): void
    {
        Schema::table('impresiones', function (Blueprint $table) {
            $table->dropColumn('cantidad_pliegos_impresos');
        });
    }
};
