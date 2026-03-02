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
        Schema::create('egresos', function (Blueprint $table) {
            $table->id('id_egreso');
            $table->unsignedBigInteger('id_proveedor')->nullable(); // Gasto sin proveedor específico es posible

            $table->unsignedBigInteger('id_metodo_pago'); 
            $table->foreign('id_metodo_pago')->references('id_metodo_pago')->on('metodos_pago');
            
            $table->string('concepto'); // "Pago luz", "Compra toner"
            $table->text('descripcion')->nullable();
            $table->decimal('monto', 10, 2);
            $table->dateTime('fecha_egreso'); // Importante la hora
            
            $table->string('nro_factura')->nullable();
            $table->string('tipo_comprobante')->default('recibo'); 

            // En transacciones y egresos:
            $table->unsignedBigInteger('id_caja')->nullable();
            $table->foreign('id_caja')->references('id_caja')->on('caja');

            $table->timestamps();
            
            $table->foreign('id_proveedor')->references('id_proveedor')->on('proveedores');
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
