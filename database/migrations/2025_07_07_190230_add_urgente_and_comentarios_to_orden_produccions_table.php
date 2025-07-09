<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('orden_produccions', function (Blueprint $table) {
            $table->boolean('urgente')->default(false)->after('estado');
            $table->text('comentarios')->nullable()->after('urgente');
        });
    }

    public function down(): void
    {
        Schema::table('orden_produccions', function (Blueprint $table) {
            $table->dropColumn(['urgente', 'comentarios']);
        });
    }
};

