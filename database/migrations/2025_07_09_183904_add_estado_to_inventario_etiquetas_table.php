<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('inventario_etiquetas', function (Blueprint $table) {
            $table->string('estado')->default('pendiente');
        });
    }

    public function down()
    {
        Schema::table('inventario_etiquetas', function (Blueprint $table) {
            $table->dropColumn('estado');
        });
    }
};
