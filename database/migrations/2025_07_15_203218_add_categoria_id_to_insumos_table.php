<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('insumos', function (Blueprint $table) {
            $table->unsignedBigInteger('categoria_id')->nullable()->after('unidad');

            $table->foreign('categoria_id')
                ->references('id')
                ->on('categorias')
                ->onDelete('set null'); // si se borra la categoría, se limpia el campo
        });
    }

    public function down(): void
    {
        Schema::table('insumos', function (Blueprint $table) {
            $table->dropForeign(['categoria_id']);
            $table->dropColumn('categoria_id');
        });
    }
};