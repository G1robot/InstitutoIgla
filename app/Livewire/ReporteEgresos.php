<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\EgresoModel;
use Carbon\Carbon;

class ReporteEgresos extends Component
{
    public $fecha_inicio;
    public $fecha_fin;

    // Totales
    public $totalEfectivo = 0;
    public $totalBanco = 0;
    public $totalGeneral = 0;

    // Lista de resultados
    public $listaEgresos = [];

    public function mount()
    {
        // Por defecto: Del 1ro del mes actual hasta hoy
        $this->fecha_inicio = Carbon::now()->startOfMonth()->format('Y-m-d');
        $this->fecha_fin = Carbon::now()->format('Y-m-d');
        
        $this->generarReporte();
    }

    // Se ejecuta cada vez que el usuario cambia una de las fechas
    public function updatedFechaInicio() { $this->generarReporte(); }
    public function updatedFechaFin() { $this->generarReporte(); }

    public function generarReporte()
    {
        // 1. Validar que la fecha de inicio no sea mayor a la final
        if ($this->fecha_inicio > $this->fecha_fin) {
            $temp = $this->fecha_inicio;
            $this->fecha_inicio = $this->fecha_fin;
            $this->fecha_fin = $temp;
        }

        // 2. Traer TODOS los egresos en ese rango de fechas a una VARIABLE LOCAL
        $egresos = EgresoModel::with(['metodoPago', 'proveedor'])
            ->whereDate('fecha_egreso', '>=', $this->fecha_inicio)
            ->whereDate('fecha_egreso', '<=', $this->fecha_fin)
            ->orderBy('fecha_egreso', 'asc') // Orden cronológico
            ->get();

        // 3. Calcular los totales
        $this->totalEfectivo = $egresos->where('metodoPago.es_efectivo', true)->sum('monto');
        $this->totalBanco = $egresos->where('metodoPago.es_efectivo', false)->sum('monto');
        $this->totalGeneral = $this->totalEfectivo + $this->totalBanco;

        // 4. Asignar a la variable pública
        $this->listaEgresos = $egresos;
    }
    
    public function render()
    {
        return view('livewire.reporte-egresos');
    }
}
