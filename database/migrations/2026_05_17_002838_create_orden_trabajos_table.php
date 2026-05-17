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
        Schema::create('orden_trabajos', function (Blueprint $table) {
            $table->id();
            $table->foreign('usuario_creador_id')->references('id')->on('users');
            $table->unsignedBigInteger('usuario_creador_id')->nullable();
            $table->foreign('usuario_modificador_id')->references('id')->on('users');
            $table->unsignedBigInteger('usuario_modificador_id')->nullable();
            $table->foreign('usuario_eliminador_id')->references('id')->on('users');
            $table->unsignedBigInteger('usuario_eliminador_id')->nullable();

            $table->foreign('orden_recepcion_id')->references('id')->on('orden_recepciones');
            $table->unsignedBigInteger('orden_recepcion_id')->nullable();

            $table->integer('numero_orden_secuencial')->nullable();
            $table->integer('anio')->nullable();
            $table->date('fecha_emision')->nullable();

            $table->json('mano_obra')->nullable();
            $table->json('repuestos')->nullable();
            $table->json('insumos')->nullable();
            $table->json('trabajos_tercero')->nullable();

            $table->decimal('subtotal_mano_obra', 10, 2)->nullable()->default(0);
            $table->decimal('subtotal_repuestos', 10, 2)->nullable()->default(0);
            $table->decimal('subtotal_insumos', 10, 2)->nullable()->default(0);
            $table->decimal('subtotal_trabajos_tercero', 10, 2)->nullable()->default(0);
            $table->decimal('total_general', 10, 2)->nullable()->default(0);

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
        Schema::dropIfExists('orden_trabajos');
    }
};
