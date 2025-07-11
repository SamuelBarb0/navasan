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
        Schema::table('orden_produccions', function (Blueprint $table) {
            $table->string('area_actual')->nullable()->after('estado'); // o donde prefieras
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orden_produccions', function (Blueprint $table) {
            //
        });
    }
};
