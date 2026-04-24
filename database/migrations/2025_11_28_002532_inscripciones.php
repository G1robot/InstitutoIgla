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
        Schema::create('usuario', function (Blueprint $table) {
            $table->id('id_usuario');
            $table->string('nombre', 80);
            $table->string('telefono', 30)->nullable(); 
            $table->string('usuario', 80)->unique(); 
            
            
            $table->string('password', 255); 
            
            $table->enum('rol', ['administrador', 'personal']);
            $table->rememberToken(); 
            $table->timestamps();
        });

        Schema::create('turno', function (Blueprint $table) {
            $table->id('id_turno');
            $table->string('nombre', 50);
            $table->timestamps();
        });

        Schema::create('caja', function (Blueprint $table) {
            $table->id('id_caja');
            $table->unsignedBigInteger('id_usuario');
            $table->unsignedBigInteger('id_turno');
            
            $table->dateTime('fecha_apertura');
            $table->dateTime('fecha_cierre')->nullable();
            
            $table->decimal('monto_inicial', 10, 2);
            $table->decimal('monto_final_sistema', 10, 2)->nullable();
            $table->decimal('monto_final_fisico', 10, 2)->nullable(); 
            $table->decimal('diferencia', 10, 2)->nullable();
            
            $table->enum('estado', ['abierta', 'cerrada'])->default('abierta');
            $table->text('observaciones')->nullable();
            
            $table->timestamps();

            $table->foreign('id_usuario')->references('id_usuario')->on('usuario');
        
            $table->foreign('id_turno')->references('id_turno')->on('turno'); 
        });


        Schema::create('estudiante', function (Blueprint $table) {
            $table->id('id_estudiante');
            $table->string('nombre', 80);
            $table->string('apellido', 80);
            $table->string('ci', 30)->unique();
            $table->string('telefono', 30);
            $table->date('fecha_nacimiento');
            $table->string('genero', 20);
            $table->timestamps();
        });

        Schema::create('plan', function (Blueprint $table) {
            $table->id('id_plan');
            $table->string('nombre', 80);
            $table->integer('duracion_anios')->nullable();
            $table->integer('duracion_meses')->nullable();
            $table->decimal('costo_anual', 8, 2)->nullable();
            $table->decimal('costo_mensual', 8, 2)->nullable();
            $table->enum('tipo_pago', ['mensual', 'anual', 'unico', 'beca']);
            $table->timestamps();
        });

        Schema::create('inscripcion', function (Blueprint $table) {
            $table->id('id_inscripcion');
            $table->unsignedBigInteger('id_estudiante');
            $table->unsignedBigInteger('id_plan');
            $table->integer('gestion_inicio');
            $table->integer('anio_actual');
            $table->date('fecha_inscripcion');
            $table->enum('estado', ['activo', 'retirado', 'egresado']);
            $table->timestamps();

            $table->foreign('id_estudiante')->references('id_estudiante')->on('estudiante');
            $table->foreign('id_plan')->references('id_plan')->on('plan');
        });

        Schema::create('tarifas', function (Blueprint $table) {
            $table->id('id_tarifa');
            
            $table->string('codigo', 50); 
            $table->decimal('monto', 10, 2);
            $table->integer('gestion')->nullable(); 
            $table->timestamps();
            
            
            $table->unique(['codigo', 'gestion']); 
        });

        Schema::create('estudiante_derechos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_estudiante');
            
            $table->string('derecho', 20); 
            $table->date('fecha_adquisicion');
            $table->timestamps();

            $table->foreign('id_estudiante')->references('id_estudiante')->on('estudiante');
        });

        Schema::create('categoria_modulo', function (Blueprint $table) {
            $table->id('id_categoria_modulo');
            $table->string('nombre', 50);
            $table->timestamps();
        });

        Schema::create('modulo', function (Blueprint $table) {
            $table->id('id_modulo');
            $table->string('nombre', 50);
            $table->decimal('costo', 8, 2);
            $table->unsignedBigInteger('id_categoria_modulo');
            $table->foreign('id_categoria_modulo')->references('id_categoria_modulo')->on('categoria_modulo');
            $table->timestamps();
        });

        Schema::create('inscripcion_modulo', function (Blueprint $table) {
            $table->id('id_inscripcion_modulo');
            $table->unsignedBigInteger('id_estudiante');
            $table->unsignedBigInteger('id_modulo');
            $table->date('fecha_inscripcion');
            
            $table->enum('estado', ['cursando', 'finalizado']); 
            $table->decimal('costo_al_momento', 10, 2); 
            $table->timestamps();

            $table->foreign('id_estudiante')->references('id_estudiante')->on('estudiante');
            $table->foreign('id_modulo')->references('id_modulo')->on('modulo');
        });

        Schema::create('metodos_pago', function (Blueprint $table) {
            $table->id('id_metodo_pago');
            $table->string('nombre', 50); // Ej: "Efectivo", "QR Simple", "Transferencia"
            
            // ESTE CAMPO ES LA CLAVE DEL ÉXITO:
            // Si es true, el dinero entra al cajón físico.
            // Si es false, el dinero va al banco (no se cuenta en el arqueo físico).
            $table->boolean('es_efectivo')->default(false); 
            
            $table->boolean('activo')->default(true); // Para ocultar métodos viejos en el futuro
            $table->timestamps();
        });

        Schema::create('pagos', function (Blueprint $table) {
            $table->id('id_pago');
            
            $table->unsignedBigInteger('origen_id');
            $table->string('origen_type');

            $table->unsignedBigInteger('id_estudiante')->nullable(); 

            
            $table->date('fecha_vencimiento'); 
            $table->date('fecha_pago')->nullable();

            
            
            $table->string('descripcion'); 
            
            
            $table->decimal('monto_total', 10, 2);   
            $table->decimal('monto_abonado', 10, 2)->default(0); 
            
            $table->enum('estado', ['pendiente', 'parcial', 'pagado', 'vencido', 'anulado'])->default('pendiente');

            $table->timestamps();

            $table->index(['origen_id', 'origen_type']);
            $table->foreign('id_estudiante')->references('id_estudiante')->on('estudiante');
        });

        Schema::create('transacciones', function (Blueprint $table) {
            $table->id('id_transaccion');
            
            // Relación con el pago (Deuda)
            $table->unsignedBigInteger('id_pago');
            $table->foreign('id_pago')->references('id_pago')->on('pagos')->onDelete('cascade');
            
            // Relación con el método (Forma)
            $table->unsignedBigInteger('id_metodo_pago');
            $table->foreign('id_metodo_pago')->references('id_metodo_pago')->on('metodos_pago');
            
            // Cuánto dinero entró en ESTA forma específica
            $table->decimal('monto', 10, 2);
            $table->dateTime('fecha_transaccion'); // Hora exacta importante para el arqueo

            // En transacciones y egresos:
            $table->unsignedBigInteger('id_caja')->nullable();
            $table->foreign('id_caja')->references('id_caja')->on('caja');
            
            // Usuario que cobró (Opcional pero recomendado)
            // $table->foreignId('id_usuario')...

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
