<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // empalmados
        Schema::table('empalmados', function (Blueprint $table) {
            // Cantidad recibida (entera, no negativa). Se deja nullable para no romper datos existentes.
            $table->unsignedInteger('cantidad_liberada')->nullable();
        });

        // laminados
        Schema::table('laminados', function (Blueprint $table) {
            $table->unsignedInteger('cantidad_liberada')->nullable();
        });
    }

    public function down(): void
    {
        if (Schema::hasColumn('empalmados', 'cantidad_liberada')) {
            Schema::table('empalmados', function (Blueprint $table) {
                $table->dropColumn('cantidad_liberada');
            });
        }

        if (Schema::hasColumn('laminados', 'cantidad_liberada')) {
            Schema::table('laminados', function (Blueprint $table) {
                $table->dropColumn('cantidad_liberada');
            });
        }
    }
};
