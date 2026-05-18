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
        Schema::create('formulario_autorizaciones', function (Blueprint $table) {
            $table->id();
            $table->foreign('usuario_creador_id')->references('id')->on('users');
            $table->unsignedBigInteger('usuario_creador_id')->nullable();
            $table->foreign('usuario_modificador_id')->references('id')->on('users');
            $table->unsignedBigInteger('usuario_modificador_id')->nullable();
            $table->foreign('usuario_eliminador_id')->references('id')->on('users');
            $table->unsignedBigInteger('usuario_eliminador_id')->nullable();

            $table->foreign('orden_recepcion_id')->references('id')->on('orden_recepciones');
            $table->unsignedBigInteger('orden_recepcion_id')->nullable();

            $table->date('fecha')->nullable();
            $table->string('cite')->nullable();
            $table->text('observaciones')->nullable();
            $table->json('preventivo')->nullable();
            $table->json('correctivo')->nullable();
            $table->json('repuestos_suministros')->nullable();
            $table->json('otros')->nullable();
            $table->decimal('total_general', 10, 2)->nullable()->default(0);

            $table->string('estado')->nullable();
            $table->datetime('deleted_at')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('formulario_autorizaciones');
    }
};
