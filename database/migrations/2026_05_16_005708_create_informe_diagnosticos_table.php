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
        Schema::create('informe_diagnosticos', function (Blueprint $table) {
            $table->id();
            $table->foreign('usuario_creador_id')->references('id')->on('users');
            $table->unsignedBigInteger('usuario_creador_id')->nullable();
            $table->foreign('usuario_modificador_id')->references('id')->on('users');
            $table->unsignedBigInteger('usuario_modificador_id')->nullable();
            $table->foreign('usuario_eliminador_id')->references('id')->on('users');
            $table->unsignedBigInteger('usuario_eliminador_id')->nullable();

            $table->foreign('orden_recepcion_id')->references('id')->on('orden_recepciones');
            $table->unsignedBigInteger('orden_recepcion_id')->nullable();
            $table->foreign('mecanico_id')->references('id')->on('users');
            $table->unsignedBigInteger('mecanico_id')->nullable();

            $table->text('inspeccion_exterior_texto')->nullable();
            $table->longText('inspeccion_exterior_imagen')->nullable(); // Guardar path o base64

            $table->text('inspeccion_interior_texto')->nullable();
            $table->longText('inspeccion_interior_imagen')->nullable(); // Guardar path o base64

            $table->json('diagnosticos')->nullable(); // Array de diagnósticos (imagen, componente, etc.)

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
        Schema::dropIfExists('informe_diagnosticos');
    }
};
