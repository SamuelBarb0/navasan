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
        Schema::table('empalmados', function (Blueprint $table) {
            $table->foreignId('producto_id')->after('orden_id')->constrained('productos')->restrictOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('empalmados', function (Blueprint $table) {
            //
        });
    }
};
