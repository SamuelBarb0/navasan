<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRevisionesTable extends Migration
{
    public function up()
    {
        Schema::create('revisiones', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('orden_id');
            $table->string('revisado_por');
            $table->integer('cantidad');
            $table->enum('tipo', ['correcta', 'defectos', 'apartada', 'rechazada']);
            $table->text('comentarios')->nullable();
            $table->timestamps();

            $table->foreign('orden_id')->references('id')->on('orden_produccions')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('revisiones');
    }
}