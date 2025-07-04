<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('insumos', function (Blueprint $table) {
            $table->text('descripcion')->nullable();
        });
    }

    public function down()
    {
        Schema::table('insumos', function (Blueprint $table) {
            $table->dropColumn('descripcion');
        });
    }
};
