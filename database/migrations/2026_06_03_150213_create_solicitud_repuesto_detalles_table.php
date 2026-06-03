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
        Schema::create('solicitud_repuesto_detalles', function (Blueprint $table) {
            $table->id();
            $table->foreign('usuario_creador_id')->references('id')->on('users');
            $table->unsignedBigInteger('usuario_creador_id')->nullable();
            $table->foreign('usuario_modificador_id')->references('id')->on('users');
            $table->unsignedBigInteger('usuario_modificador_id')->nullable();
            $table->foreign('usuario_eliminador_id')->references('id')->on('users');
            $table->unsignedBigInteger('usuario_eliminador_id')->nullable();

            $table->foreign('solicitud_repuesto_id')->references('id')->on('solicitud_repuestos');
            $table->unsignedBigInteger('solicitud_repuesto_id')->nullable();
            $table->foreign('producto_id')->references('id')->on('productos');
            $table->unsignedBigInteger('producto_id')->nullable();

            $table->string('descripcion')->nullable();
            $table->integer('cantidad')->default(0)->nullable();
            $table->decimal('precio', 10, 2)->default(0)->nullable();
            $table->decimal('subtotal', 10, 2)->default(0)->nullable();
            $table->boolean('aprobado')->default(false)->nullable();
            $table->string('path_archivo')->nullable();

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
        Schema::dropIfExists('solicitud_repuesto_detalles');
    }
};
