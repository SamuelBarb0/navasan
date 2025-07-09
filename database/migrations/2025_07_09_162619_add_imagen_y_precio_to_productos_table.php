<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('productos', function (Blueprint $table) {
            $table->string('imagen')->nullable()->after('unidad');
            $table->decimal('precio', 10, 2)->default(0)->after('imagen');
        });
    }

    public function down()
    {
        Schema::table('productos', function (Blueprint $table) {
            $table->dropColumn(['imagen', 'precio']);
        });
    }
};