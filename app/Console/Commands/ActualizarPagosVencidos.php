<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\PagoModel;
use Carbon\Carbon;

class ActualizarPagosVencidos extends Command
{
    /**
     * El nombre y la firma del comando (así lo llamarás en la terminal).
     */
    protected $signature = 'pagos:actualizar-vencidos';

    /**
     * Descripción del comando.
     */
    protected $description = 'Busca pagos pendientes con fecha pasada y los marca como vencidos';

    /**
     * Ejecutar la lógica.
     */
    public function handle()
    {
        $hoy = Carbon::now()->format('Y-m-d');

        // Buscamos pagos que:
        // 1. Su fecha de vencimiento sea MENOR a hoy
        // 2. Su estado sea 'pendiente' (o parcial si quieres ser estricto)
        // 3. Y los actualizamos a 'vencido'
        
        $afectados = PagoModel::where('fecha_vencimiento', '<', $hoy)
            ->where('estado', 'pendiente') 
            ->update(['estado' => 'vencido']);

        $this->info("Proceso terminado. Se marcaron $afectados pagos como VENCIDOS.");
    }
}
