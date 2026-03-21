<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\TransaccionModel;
use App\Models\EgresoModel;
use App\Models\PagoModel;
use Carbon\Carbon;

class ArqueoCaja extends Component
{
    public $fecha_filtro;

    // Totales
    public $ingresosEfectivo = 0;
    public $ingresosBanco = 0;
    public $egresosEfectivo = 0;
    public $egresosBanco = 0;

    // Listas para el detalle
    public $listaIngresos = [];
    public $listaEgresos = [];

    public function mount()
    {
        // Por defecto, carga el día de hoy
        $this->fecha_filtro = Carbon::today()->format('Y-m-d');
        $this->calcularArqueo();
    }

    public function updatedFechaFiltro()
    {
        $this->calcularArqueo();
    }

    public function calcularArqueo()
    {
        // 1. Guardamos en variables locales (Collections reales)
        $ingresos = TransaccionModel::with(['metodo', 'pago.origen'])
            ->whereDate('fecha_transaccion', $this->fecha_filtro)
            ->whereHas('pago', function($query) {
                $query->where('estado', '!=', 'anulado'); 
            })
            ->orderBy('fecha_transaccion', 'asc') // <-- ORDENAMIENTO DE INGRESOS
            ->get();

        $egresos = EgresoModel::with(['metodoPago', 'proveedor'])
            ->whereDate('fecha_egreso', $this->fecha_filtro)
            ->orderBy('fecha_egreso', 'asc') // <-- ORDENAMIENTO DE EGRESOS
            ->get();

        // 2. Hacemos los cálculos sobre estas variables locales
        $this->ingresosEfectivo = $ingresos->where('metodo.es_efectivo', true)->sum('monto');
        $this->ingresosBanco = $ingresos->where('metodo.es_efectivo', false)->sum('monto');
        
        $this->egresosEfectivo = $egresos->where('metodoPago.es_efectivo', true)->sum('monto');
        $this->egresosBanco = $egresos->where('metodoPago.es_efectivo', false)->sum('monto');

        // 3. Finalmente, pasamos los datos a las variables públicas para la vista
        $this->listaIngresos = $ingresos;
        $this->listaEgresos = $egresos;
    }

    public function render()
    {
        $saldoCajaFisica = $this->ingresosEfectivo - $this->egresosEfectivo;
        $saldoBanco = $this->ingresosBanco - $this->egresosBanco;
        $totalGeneral = $saldoCajaFisica + $saldoBanco;

        return view('livewire.arqueo-caja',[
            'saldoCajaFisica' => $saldoCajaFisica,
            'saldoBanco' => $saldoBanco,
            'totalGeneral' => $totalGeneral
        ]);
    }
}
