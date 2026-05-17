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
        Schema::create('formulario_diagnosticos', function (Blueprint $table) {
            $table->id();
            $table->foreign('usuario_creador_id')->references('id')->on('users');
            $table->unsignedBigInteger('usuario_creador_id')->nullable();
            $table->foreign('usuario_modificador_id')->references('id')->on('users');
            $table->unsignedBigInteger('usuario_modificador_id')->nullable();
            $table->foreign('usuario_eliminador_id')->references('id')->on('users');
            $table->unsignedBigInteger('usuario_eliminador_id')->nullable();

            $table->foreign('orden_recepcion_id')->references('id')->on('orden_recepciones');
            $table->unsignedBigInteger('orden_recepcion_id')->nullable();

            $table->string('responsable_vehiculo')->nullable();
            $table->string('vehiculo_asignado_a')->nullable();

            $table->json('servicios_preventivos')->nullable();
            $table->json('servicios_correctivos')->nullable();
            $table->json('servicios_otros')->nullable();

            $table->text('recepcion_taller')->nullable();

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
        Schema::dropIfExists('formulario_diagnosticos');
    }
};
