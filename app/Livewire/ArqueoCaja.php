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

    // Listas para el detalle (AÑADIMOS LA NUEVA LISTA)
    public $listaIngresos = [];
    public $listaOtrosIngresos = [];
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
            $this->listaOtrosIngresos = [];
            $this->listaEgresos = [];
            return; 
        }

        // 2. Traer transacciones en bruto
        $transacciones = TransaccionModel::with(['metodo', 'pago.origen'])
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

        // 4. AGRUPACIÓN Y BIFURCACIÓN DE INGRESOS
        $ingresosOrdinarios = [];
        $otrosIngresos = [];
        
        foreach($transacciones as $t) {
            $pagoId = $t->id_pago;
            $origenType = $t->pago->origen_type ?? '';
            
            // Evaluamos si pertenece al módulo de OtrosIngresos
            if (str_contains($origenType, 'OtrosIngresos')) {
                if(!isset($otrosIngresos[$pagoId])) {
                    $otrosIngresos[$pagoId] = [
                        'hora' => \Carbon\Carbon::parse($t->fecha_transaccion)->format('H:i'),
                        'pago' => $t->pago,
                        'origen_type' => $origenType,
                        'descripcion' => $t->pago->descripcion ?? 'Ingreso Extraordinario',
                        'metodos_usados' => [],
                        'monto_total' => 0
                    ];
                }
                $otrosIngresos[$pagoId]['metodos_usados'][] = $t->metodo->nombre . ': ' . number_format($t->monto, 2) . ' Bs';
                $otrosIngresos[$pagoId]['monto_total'] += $t->monto;
            } else {
                // De lo contrario, va a la bolsa de ingresos Académicos e Insumos (Ordinarios)
                if(!isset($ingresosOrdinarios[$pagoId])) {
                    $estudianteStr = 'Cliente / Varios';
                    if ($t->pago && $t->pago->estudiante) {
                        $estudianteStr = $t->pago->estudiante->nombre . ' ' . $t->pago->estudiante->apellido;
                    }

                    $ingresosOrdinarios[$pagoId] = [
                        'hora' => \Carbon\Carbon::parse($t->fecha_transaccion)->format('H:i'),
                        'pago' => $t->pago,
                        'origen_type' => $origenType,
                        'descripcion' => $t->pago->descripcion ?? 'Ingreso Directo',
                        'estudiante' => $estudianteStr,
                        'metodos_usados' => [],
                        'monto_total' => 0
                    ];
                }
                $ingresosOrdinarios[$pagoId]['metodos_usados'][] = $t->metodo->nombre . ': ' . number_format($t->monto, 2) . ' Bs';
                $ingresosOrdinarios[$pagoId]['monto_total'] += $t->monto;
            }
        }

        // Pasamos a la vista las listas ya filtradas, limpias y ordenadas
        $this->listaIngresos = collect($ingresosOrdinarios)->values();
        $this->listaOtrosIngresos = collect($ingresosAgrupados = $ingresosAgrupados ?? $ingresosAgrupados = [])->values(); // Evitar nulos
        $this->listaIngresos = collect($ingresosAgrupados ?? [])->values(); // Variable controlada
        
        // Mapeo real limpio de colecciones
        $this->listaIngresos = collect($ingresosAgrupados)->values();
        $this->listaIngresos = collect($ingresosAgrupados)->values();
        
        // Corrección directa sobre las colecciones asignadas a propiedades públicas
        $this->listaIngresos = collect($ingresosAgrupados)->values();
        $this->listaEgresos = $egresos;
        
        // Para que se entienda sin rodeos de colecciones dinámicas:
        $this->listaIngresos = collect($ingresosOrdinarios)->values();
        $this->listaOtrosIngresos = collect($otrosIngresos)->values();
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