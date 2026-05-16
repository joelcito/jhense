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
        Schema::create('orden_recepciones', function (Blueprint $table) {
            $table->id();
            $table->foreign('usuario_creador_id')->references('id')->on('users');
            $table->unsignedBigInteger('usuario_creador_id')->nullable();
            $table->foreign('usuario_modificador_id')->references('id')->on('users');
            $table->unsignedBigInteger('usuario_modificador_id')->nullable();
            $table->foreign('usuario_eliminador_id')->references('id')->on('users');
            $table->unsignedBigInteger('usuario_eliminador_id')->nullable();

            $table->foreign('grupo_cliente_id')->references('id')->on('grupo_clientes');
            $table->unsignedBigInteger('grupo_cliente_id')->nullable();
            $table->foreign('auto_id')->references('id')->on('autos');
            $table->unsignedBigInteger('auto_id')->nullable();

            $table->date('fecha_recepcion');
            $table->string('tipo_unidad', 50)->nullable(); // LIVIANO o PESADO
            $table->string('kilometraje')->nullable();
            $table->string('objeto_contratacion')->nullable();
            $table->integer('porcentaje_combustible')->nullable();
            $table->json('checklist')->nullable();
            $table->text('observacion_general')->nullable();

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
        Schema::dropIfExists('orden_recepcions');
    }
};
