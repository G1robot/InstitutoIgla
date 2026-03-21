<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\TransaccionModel;
use Carbon\Carbon;

class ReporteIngresos extends Component
{
    public $fecha_inicio;
    public $fecha_fin;

    // Totales
    public $totalEfectivo = 0;
    public $totalBanco = 0;
    public $totalGeneral = 0;

    // Lista de resultados
    public $listaIngresos = [];

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
        // 1. Validar fechas
        if ($this->fecha_inicio > $this->fecha_fin) {
            $temp = $this->fecha_inicio;
            $this->fecha_inicio = $this->fecha_fin;
            $this->fecha_fin = $temp;
        }

        // 2. Traer los datos a una VARIABLE LOCAL (Esto soluciona el error)
        $ingresos = TransaccionModel::with(['metodo', 'pago.origen'])
            ->whereHas('pago', function($query) {
                $query->where('estado', '!=', 'anulado'); 
            })
            ->whereDate('fecha_transaccion', '>=', $this->fecha_inicio)
            ->whereDate('fecha_transaccion', '<=', $this->fecha_fin)
            ->orderBy('fecha_transaccion', 'asc') 
            ->get();

        // 3. Calcular los totales usando la variable local
        $this->totalEfectivo = $ingresos->where('metodo.es_efectivo', true)->sum('monto');
        $this->totalBanco = $ingresos->where('metodo.es_efectivo', false)->sum('monto');
        $this->totalGeneral = $this->totalEfectivo + $this->totalBanco;

        // 4. Asignar a la variable pública para que la Vista (Blade) lo dibuje
        $this->listaIngresos = $ingresos;
    }
    
    public function render()
    {
        return view('livewire.reporte-ingresos');
    }
}
