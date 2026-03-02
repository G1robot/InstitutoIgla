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
        Schema::create('categorias_articulo', function (Blueprint $table) {
            $table->id('id_categoria_articulo');
            $table->string('nombre', 50); // Ej: "Uniforme", "Librería", "Trámite"
            $table->timestamps();
        });

        Schema::create('articulos', function (Blueprint $table) {
            $table->id('id_articulo');
            $table->unsignedBigInteger('id_categoria_articulo');
            
            $table->string('nombre', 100); 
            $table->decimal('precio', 10, 2);
            $table->integer('stock')->nullable(); // Null = Infinito (Servicios)
            $table->boolean('es_obligatorio')->default(false); // Para insumos forzosos
            
            $table->timestamps();
            
            $table->foreign('id_categoria_articulo')->references('id_categoria_articulo')->on('categorias_articulo');
        });

        Schema::create('ventas', function (Blueprint $table) {
            $table->id('id_venta');
            $table->unsignedBigInteger('id_estudiante');
            
            $table->dateTime('fecha_venta'); // DateTime para saber hora exacta
            $table->decimal('monto_total', 10, 2);
            $table->enum('estado', ['finalizada', 'anulada'])->default('finalizada');
            
            // Opcional: Usuario que hizo la venta
            // $table->unsignedBigInteger('id_usuario'); 

            $table->timestamps();
            
            $table->foreign('id_estudiante')->references('id_estudiante')->on('estudiante');
        });

        Schema::create('detalle_ventas', function (Blueprint $table) {
            $table->id('id_detalle_venta');
            $table->unsignedBigInteger('id_venta');
            $table->unsignedBigInteger('id_articulo');
            
            $table->integer('cantidad');
            $table->decimal('precio_unitario', 10, 2);
            $table->decimal('subtotal', 10, 2);
            
            $table->timestamps();

            $table->foreign('id_venta')->references('id_venta')->on('ventas')->onDelete('cascade');
            $table->foreign('id_articulo')->references('id_articulo')->on('articulos');
        });

        Schema::create('proveedores', function (Blueprint $table) {
            $table->id('id_proveedor');
            $table->string('nombre_empresa', 100);
            $table->string('nombre_contacto', 100)->nullable();
            $table->string('telefono', 30)->nullable();
            $table->string('nit_ci', 30)->nullable();
            $table->timestamps();
        });

        Schema::create('arqueos', function (Blueprint $table) {
            $table->id('id_arqueo');
            $table->date('fecha_arqueo')->unique(); // Solo uno por día
            
            $table->decimal('monto_inicial', 10, 2)->default(0); // Caja chica mañana
            $table->decimal('total_ingresos', 10, 2); 
            $table->decimal('total_egresos', 10, 2);
            
            $table->decimal('saldo_sistema', 10, 2); // Lo que debería haber
            $table->decimal('saldo_real', 10, 2);    // Lo que contaron manualmente
            $table->decimal('diferencia', 10, 2);    // Sobrante o faltante
            
            $table->text('observaciones')->nullable();
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
