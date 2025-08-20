<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('suajes', function (Blueprint $table) {
            $table->dropForeign(['orden_id']); // o el nombre real de la FK
            $table->foreign('orden_id')
                ->references('id')->on('orden_produccions')
                ->onDelete('cascade');
        });
    }

    public function down(): void {
        Schema::table('suajes', function (Blueprint $table) {
            $table->dropForeign(['orden_id']);
            $table->foreign('orden_id')
                ->references('id')->on('orden_produccions')
                ->onDelete('restrict'); // o como estaba
        });
    }
};