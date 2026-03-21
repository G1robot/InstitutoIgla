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
        Schema::create('otros_ingresos', function (Blueprint $table) {
            $table->id('id_ingreso');
            
            $table->string('nombre_origen', 150)->nullable(); 
            
            $table->string('concepto', 100);
            $table->text('descripcion')->nullable();
            $table->decimal('monto_total', 10, 2);
            $table->dateTime('fecha_ingreso');

            $table->timestamps();
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
