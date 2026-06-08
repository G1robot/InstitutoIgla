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
        // 1. EL GUARDIA: Si borraron la fecha, no hacemos consulta a la BD
        if (empty($this->fecha_filtro)) {
            $this->ingresosEfectivo = 0;
            $this->ingresosBanco = 0;
            $this->egresosEfectivo = 0;
            $this->egresosBanco = 0;
            $this->listaIngresos = [];
            $this->listaEgresos = [];
            return; 
        }

        // 2. Traer transacciones en bruto (AQUÍ SE AÑADIÓ 'pago.estudiante')
        $transacciones = TransaccionModel::with(['metodo', 'pago.estudiante', 'pago.origen'])
            ->whereDate('fecha_transaccion', $this->fecha_filtro)
            ->whereHas('pago', function($query) {
                $query->where('estado', '!=', 'anulado'); 
            })
            ->orderBy('fecha_transaccion', 'asc')
            ->get();

        $egresos = EgresoModel::with(['metodoPago', 'proveedor'])
            ->whereDate('fecha_egreso', $this->fecha_filtro)
            ->orderBy('fecha_egreso', 'asc')
            ->get();

        // 3. Cálculos matemáticos generales
        $this->ingresosEfectivo = $transacciones->where('metodo.es_efectivo', true)->sum('monto');
        $this->ingresosBanco = $transacciones->where('metodo.es_efectivo', false)->sum('monto');
        
        $this->egresosEfectivo = $egresos->where('metodoPago.es_efectivo', true)->sum('monto');
        $this->egresosBanco = $egresos->where('metodoPago.es_efectivo', false)->sum('monto');

        // 4. AGRUPACIÓN PARA LA VISTA (Evita filas duplicadas por cobros mixtos)
        $ingresosAgrupados = [];
        
        foreach($transacciones as $t) {
            $pagoId = $t->id_pago;
            
            // Si el pago no existe en nuestro arreglo, lo creamos
            if(!isset($ingresosAgrupados[$pagoId])) {
                
                // AQUÍ SE AÑADIÓ LA CAPTURA DEL NOMBRE
                $estudianteStr = 'Cliente / Varios';
                if ($t->pago && $t->pago->estudiante) {
                    $estudianteStr = $t->pago->estudiante->nombre . ' ' . $t->pago->estudiante->apellido;
                }

                $ingresosAgrupados[$pagoId] = [
                    'hora' => \Carbon\Carbon::parse($t->fecha_transaccion)->format('H:i'),
                    'pago' => $t->pago,
                    'origen_type' => $t->pago->origen_type ?? '',
                    'descripcion' => $t->pago->descripcion ?? 'Ingreso Directo',
                    'estudiante' => $estudianteStr, // <-- AQUÍ SE GUARDA
                    'metodos_usados' => [],
                    'monto_total' => 0
                ];
            }
            
            // Acumulamos los métodos y la plata en ese pago
            $ingresosAgrupados[$pagoId]['metodos_usados'][] = $t->metodo->nombre . ': ' . number_format($t->monto, 2) . ' Bs';
            $ingresosAgrupados[$pagoId]['monto_total'] += $t->monto;
        }

        // Pasamos a la vista la lista ya agrupada y limpia
        $this->listaIngresos = collect($ingresosAgrupados)->values();
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
