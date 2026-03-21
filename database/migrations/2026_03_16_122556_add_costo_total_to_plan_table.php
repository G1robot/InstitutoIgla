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
        Schema::table('plan', function (Blueprint $table) {
            Schema::table('plan', function (Blueprint $table) {
            $table->decimal('costo_total', 8, 2)->nullable()->after('costo_mensual');
            
            $table->string('tipo_pago', 20)->change();
        });
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('plan', function (Blueprint $table) {
            $table->dropColumn('costo_total');
            
            $table->string('tipo_pago', 20)->change();
        });
    }
};
