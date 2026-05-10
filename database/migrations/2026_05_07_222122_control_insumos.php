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
        Schema::create('control_insumos', function (Blueprint $table) {
            $table->id('id_control_insumo');
            $table->unsignedBigInteger('id_estudiante');
            
            $table->date('fecha_semana'); 
            
            $table->enum('estado', ['pendiente', 'pagado', 'falta', 'licencia', 'anulado'])->default('pendiente');
            
            $table->unsignedBigInteger('id_venta')->nullable(); 
            
            $table->string('observacion')->nullable(); 

            $table->timestamps();

            $table->foreign('id_estudiante')->references('id_estudiante')->on('estudiante');
            $table->foreign('id_venta')->references('id_venta')->on('ventas');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
