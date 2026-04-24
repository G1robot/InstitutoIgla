<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement('ALTER TABLE inscripcion DROP CONSTRAINT IF EXISTS inscripcion_estado_check');
        
        // 2. Creamos la nueva regla incluyendo 'anulado'
        DB::statement("ALTER TABLE inscripcion ADD CONSTRAINT inscripcion_estado_check CHECK (estado::text = ANY (ARRAY['activo'::text, 'retirado'::text, 'egresado'::text, 'anulado'::text]))");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('ALTER TABLE inscripcion DROP CONSTRAINT IF EXISTS inscripcion_estado_check');
        DB::statement("ALTER TABLE inscripcion ADD CONSTRAINT inscripcion_estado_check CHECK (estado::text = ANY (ARRAY['activo'::text, 'retirado'::text, 'egresado'::text]))");
    }
};
