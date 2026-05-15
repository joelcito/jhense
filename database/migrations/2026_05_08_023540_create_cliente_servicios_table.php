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
        Schema::create('cliente_servicios', function (Blueprint $table) {
            $table->id();
            $table->foreign('usuario_creador_id')->references('id')->on('users');
            $table->unsignedBigInteger('usuario_creador_id')->nullable();
            $table->foreign('usuario_modificador_id')->references('id')->on('users');
            $table->unsignedBigInteger('usuario_modificador_id')->nullable();
            $table->foreign('usuario_eliminador_id')->references('id')->on('users');
            $table->unsignedBigInteger('usuario_eliminador_id')->nullable();

            $table->foreign('grupo_cliente_id')->references('id')->on('grupo_clientes');
            $table->unsignedBigInteger('grupo_cliente_id')->nullable();

            $table->integer('item')->nullable();
            $table->string('categoria')->nullable();
            $table->string('sub_categoria')->nullable();
            $table->text('nombre')->nullable();
            $table->decimal('costo', 12, 2)->nullable();
            $table->string('unidad_medida')->nullable();
            $table->string('cantidad')->nullable();
            $table->decimal('total', 12, 2)->nullable();

            $table->string('estado')->nullable();
            $table->datetime('deleted_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cliente_servicios');
    }
};
