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
        Schema::create('cotizacions', function (Blueprint $table) {
            $table->id();
            $table->foreign('usuario_creador_id')->references('id')->on('users');
            $table->unsignedBigInteger('usuario_creador_id')->nullable();
            $table->foreign('usuario_modificador_id')->references('id')->on('users');
            $table->unsignedBigInteger('usuario_modificador_id')->nullable();
            $table->foreign('usuario_eliminador_id')->references('id')->on('users');
            $table->unsignedBigInteger('usuario_eliminador_id')->nullable();

            $table->foreign('orden_recepcion_id')->references('id')->on('orden_recepciones');
            $table->unsignedBigInteger('orden_recepcion_id')->nullable();

            $table->string('servicio_taller')->nullable();
            $table->date('fecha_salida')->nullable();
            $table->string('dias_habiles')->nullable();

            $table->json('preventivos')->nullable();
            $table->json('correctivos')->nullable();
            $table->json('repuestos')->nullable();

            $table->decimal('subtotal_preventivos', 10, 2)->default(0);
            $table->decimal('subtotal_correctivos', 10, 2)->default(0);
            $table->decimal('subtotal_repuestos', 10, 2)->default(0);
            $table->decimal('total_general', 10, 2)->default(0);

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
        Schema::dropIfExists('cotizacions');
    }
};
