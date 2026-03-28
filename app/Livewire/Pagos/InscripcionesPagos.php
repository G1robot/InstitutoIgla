<?php

namespace App\Livewire\Pagos;

use Livewire\Component;
use App\Models\InscripcionModel;
use App\Models\PagoModel;
use App\Models\MetodoPagoModel;
use App\Models\TransaccionModel;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Models\CajaModel;
use Illuminate\Support\Facades\Auth;
use Livewire\WithPagination;
use Barryvdh\DomPDF\Facade\Pdf;

class InscripcionesPagos extends Component
{
    use WithPagination;
    public $search = '';
    public $showModal = false;

    public $showModalExito = false;
    public $datosRecibo = null; 
    public $datosExtracto = null;

    public $inscripcionId = null;
    public $pagos = [];
    public $pagoSeleccionado = null;

    public $montoParcial = null;
    public $showReservaInput = false;

    public $montoEfectivo = 0;
    public $montoQR = 0;

    public $metodosPago = [];
    public $montos = [];
    public $totalIngresado = 0;

    public $efectivoRecibido = null; 
    public $cambio = 0;

    ///CAMBIO FECHA
    public $fechaPagoManual = null;

    protected $listeners = ['refreshComponent' => '$refresh'];

    public function mount() {
        // Cargar métodos activos una sola vez
        $this->metodosPago = MetodoPagoModel::where('activo', true)->get();
    }

    public function render()
    {
        $search = mb_strtolower(trim($this->search));

        $inscripciones = InscripcionModel::with(['estudiante','plan'])
            ->where('estado', 'activo')
            ->where(function ($q) use ($search) {
                $q->whereHas('estudiante', function ($q2) use ($search) {
                    $q2->whereRaw('LOWER(nombre) LIKE ?', ["%{$search}%"])
                       ->orWhereRaw('LOWER(apellido) LIKE ?', ["%{$search}%"])
                       ->orWhere('ci', 'like', "%{$search}%");
                })
                ->orWhereHas('plan', function($q2) use ($search) {
                    $q2->whereRaw('LOWER(nombre) LIKE ?', ["%{$search}%"]);
                })
                ->orWhereRaw('CAST(gestion_inicio AS TEXT) LIKE ?', ["%{$search}%"]);
            })
            ->orderBy('id_inscripcion','desc')
            ->paginate(10);
        return view('livewire.pagos.inscripciones-pagos', compact('inscripciones'));
    }

    public function clickBuscar() 
    { 
        $this->render();
    }

    public function verPagos($idInscripcion)
    {
        $this->inscripcionId = (int) $idInscripcion;
        $this->cargarPagos();
        $this->pagoSeleccionado = null;
        $this->showModal = true;

        $this->resetMontos();
    }

    public function resetMontos() {
        $this->montos = [];
        foreach($this->metodosPago as $m) {
            $this->montos[$m->id_metodo_pago] = 0;
        }
        $this->totalIngresado = 0;
        $this->efectivoRecibido = null;
        $this->cambio = 0;
        ///CAMBIO FECHA
        $this->fechaPagoManual = Carbon::now()->format('Y-m-d');
    }

    public function updatedEfectivoRecibido() {
        $recibido = (float) ($this->efectivoRecibido === '' ? 0 : $this->efectivoRecibido);
        $totalAPagar = $this->totalIngresado;

        if ($recibido > $totalAPagar && $totalAPagar > 0) {
            $this->cambio = $recibido - $totalAPagar;
        } else {
            $this->cambio = 0;
        }
    }

    public function updatedMontos() {
        $this->totalIngresado = 0;

        foreach($this->montos as $monto) {
            // Si el campo está vacío (el usuario borró todo), cuenta como 0
            // Si tiene texto, lo forzamos a ser número decimal (float)
            $valor = ($monto === '' || $monto === null) ? 0 : (float) $monto;
            
            $this->totalIngresado += $valor;
        }
    }

    public function seleccionarPago($pagoId)
    {
        // Cargamos el pago con sus transacciones (historial)
        $this->pagoSeleccionado = PagoModel::with(['transacciones.metodo'])->find($pagoId);
        $this->resetMontos();
    }

    // Botón rápido para llenar todo el saldo con un método específico
    public function llenarSaldo($idMetodo) {
        $saldoPendiente = $this->pagoSeleccionado->monto_total - $this->pagoSeleccionado->monto_abonado;
        
        $this->resetMontos(); // Limpiar otros campos
        $this->montos[$idMetodo] = $saldoPendiente;
        $this->updatedMontos();
    }

    public function procesarCobro()
    {
        $this->validate([
            'montos.*' => 'numeric|min:0',
        ]);

        $deuda = $this->pagoSeleccionado->monto_total - $this->pagoSeleccionado->monto_abonado;

        if ($this->totalIngresado <= 0) {
            $this->addError('general', 'Debe ingresar un monto mayor a 0.');
            return;
        }

        // Permitir un pequeño margen de error por decimales (0.1) o validación estricta
        if ($this->totalIngresado > $deuda + 0.1) { 
            $this->addError('general', "El monto excede la deuda pendiente ($deuda).");
            return;
        }

        $cajaAbierta = CajaModel::where('id_usuario', Auth::id())
                            ->where('estado', 'abierta')
                            ->first();

        ///CAMBIO FECHA
        $fechaAUsar = $this->fechaPagoManual 
            ? Carbon::parse($this->fechaPagoManual)->setTimeFrom(Carbon::now()) 
            : Carbon::now();
        
        $metodosUsados = [];

        DB::transaction(function () use ($cajaAbierta, &$metodosUsados, $fechaAUsar) {
            foreach ($this->montos as $idMetodo => $monto) {
                $montoReal = (float) ($monto === '' ? 0 : $monto);

                if ($montoReal > 0) {
                    TransaccionModel::create([
                        'id_pago' => $this->pagoSeleccionado->id_pago,
                        'id_metodo_pago' => $idMetodo,
                        'id_caja' => $cajaAbierta->id_caja,
                        'monto' => $montoReal,
                        //Carbon::now()
                        'fecha_transaccion' => $fechaAUsar,
                    ]);
                    
                    // Guardar nombre del método para el recibo
                    $nombreMetodo = MetodoPagoModel::find($idMetodo)->nombre;
                    $metodosUsados[] = "$nombreMetodo ($montoReal Bs)";
                }
            }

            $nuevoAbonado = $this->pagoSeleccionado->monto_abonado + $this->totalIngresado;
            $nuevoEstado = ($nuevoAbonado >= $this->pagoSeleccionado->monto_total - 0.1) ? 'pagado' : 'parcial';

            $this->pagoSeleccionado->update([
                'monto_abonado' => $nuevoAbonado,
                'estado' => $nuevoEstado,
                //Carbon::now()
                'fecha_pago' => $fechaAUsar 
            ]);
        });

        $this->datosExtracto = null;

        $inscripcion = InscripcionModel::with('estudiante', 'plan')->find($this->inscripcionId);
        
        $this->datosRecibo = [
            'nro_recibo' => str_pad($this->pagoSeleccionado->id_pago, 6, '0', STR_PAD_LEFT),
            //Carbon::now()
            'fecha' => $fechaAUsar->format('d/m/Y H:i'),
            'estudiante' => $inscripcion->estudiante->nombre . ' ' . $inscripcion->estudiante->apellido,
            'ci' => $inscripcion->estudiante->ci,
            'cajero' => Auth::user()->nombre ?? 'Caja',
            'plan' => $inscripcion->plan->nombre,
            'cuota' => $this->pagoSeleccionado->descripcion,
            'monto_pagado' => $this->totalIngresado,

            'ingresado' => $this->efectivoRecibido ?: $this->totalIngresado,
            'cambio' => $this->cambio,

            'metodos' => implode(', ', $metodosUsados),
            'estado_cuota' => ($this->pagoSeleccionado->monto_abonado + $this->totalIngresado) >= $this->pagoSeleccionado->monto_total ? 'CANCELADO TOTAL' : 'ABONO A CUENTA',
            'saldo_restante' => max(0, $this->pagoSeleccionado->monto_total - ($this->pagoSeleccionado->monto_abonado + $this->totalIngresado))
        ];

        // Recargar datos
        $this->seleccionarPago($this->pagoSeleccionado->id_pago);
        $this->cargarPagos(); // Refrescar lista izquierda
        
        $this->showModalExito = true;


        $this->resetMontos();
        
        
    }

    public function cerrarModalExito()
    {
        $this->showModalExito = false;
        $this->datosRecibo = null;
    }

    public function prepararExtracto($idInscripcion)
    {
        $inscripcion = InscripcionModel::with(['estudiante', 'plan'])->find($idInscripcion);
        
        // 1. Obtenemos todos los pagos
        $pagosExtracto = PagoModel::where('origen_id', $idInscripcion)
            ->where('origen_type', InscripcionModel::class)
            ->get();

        // 2. ORDENAMIENTO MÁGICO: Año -> PUA -> Fecha de vencimiento
        $pagosOrdenados = $pagosExtracto->sort(function($a, $b) {
            $yearA = \Carbon\Carbon::parse($a->fecha_vencimiento)->year;
            $yearB = \Carbon\Carbon::parse($b->fecha_vencimiento)->year;
            
            // Si son de años diferentes, ordenamos por año
            if ($yearA !== $yearB) {
                return $yearA <=> $yearB; 
            }
            
            // Si son del mismo año, verificamos si es PUA
            $isPuaA = str_contains($a->descripcion, 'PUA');
            $isPuaB = str_contains($b->descripcion, 'PUA');
            
            if ($isPuaA && !$isPuaB) return -1; // PUA va primero
            if (!$isPuaA && $isPuaB) return 1;  // Cuota normal va después
            
            // Si ambos son PUA o ambos son cuotas, ordenamos por fecha
            return $a->fecha_vencimiento <=> $b->fecha_vencimiento;
            
        })->values(); // .values() resetea los índices de la colección

        $this->datosRecibo = null;
        // 3. Pasamos la colección ya ordenada a los datos del recibo
        $this->datosExtracto = [
            'fecha_emision' => Carbon::now()->format('d/m/Y H:i'),
            'estudiante' => $inscripcion->estudiante->nombre . ' ' . $inscripcion->estudiante->apellido,
            'ci' => $inscripcion->estudiante->ci,
            'plan' => $inscripcion->plan->nombre,
            'gestion' => $inscripcion->gestion_inicio,
            'pagos' => $pagosOrdenados, // <-- Usamos la variable ordenada
            'total_plan' => $pagosExtracto->sum('monto_total'),
            'total_pagado' => $pagosExtracto->sum('monto_abonado'),
            'total_deuda' => $pagosExtracto->sum('monto_total') - $pagosExtracto->sum('monto_abonado'),
        ];

        // Disparamos un evento JS para abrir la ventana de impresión
        $this->dispatch('abrir-impresion-extracto');
    }

    public function limpiarRecibos()
    {
        $this->datosRecibo = null;
        $this->datosExtracto = null;
    }

    public function cargarPagos()
    {
        $this->montoEfectivo = 0;
        $this->montoQR = 0;
        $this->pagos = PagoModel::where('origen_id', $this->inscripcionId)
            ->where('origen_type', InscripcionModel::class)
            ->orderBy('fecha_vencimiento', 'asc')
            ->get();
    }

    public function procesarCobroMixto()
    {
        // 1. Validar que la suma no exceda la deuda
        $totalIngresado = $this->montoEfectivo + $this->montoQR;
        $deuda = $this->pagoSeleccionado->monto_total - $this->pagoSeleccionado->monto_abonado;

        if ($totalIngresado <= 0) {
            $this->addError('general', 'Debe ingresar un monto mayor a 0.');
            return;
        }
        
        if ($totalIngresado > $deuda) {
            $this->addError('general', "El monto ($totalIngresado) excede la deuda pendiente ($deuda).");
            return;
        }

        // 2. Guardar Transacciones
        DB::transaction(function () {
            
            // A. Registrar Efectivo (ID 1 asumiendo seeder)
            if ($this->montoEfectivo > 0) {
                TransaccionModel::create([
                    'id_pago' => $this->pagoSeleccionado->id_pago,
                    'id_metodo_pago' => 1, // ID del Efectivo en BD
                    'monto' => $this->montoEfectivo,
                    'fecha_transaccion' => Carbon::now()
                ]);
            }

            // B. Registrar QR (ID 2 asumiendo seeder)
            if ($this->montoQR > 0) {
                TransaccionModel::create([
                    'id_pago' => $this->pagoSeleccionado->id_pago,
                    'id_metodo_pago' => 2, // ID del QR en BD
                    'monto' => $this->montoQR,
                    'fecha_transaccion' => Carbon::now()
                ]);
            }

            // 3. Actualizar el Pago Padre
            $nuevoAbonado = $this->pagoSeleccionado->monto_abonado + $this->montoEfectivo + $this->montoQR;
            $nuevoEstado = ($nuevoAbonado >= $this->pagoSeleccionado->monto_total) ? 'pagado' : 'parcial';

            $this->pagoSeleccionado->update([
                'monto_abonado' => $nuevoAbonado,
                'estado' => $nuevoEstado,
                'fecha_pago' => Carbon::now()
            ]);
        });

        $this->cerrarModal();
        $this->dispatchBrowserEvent('alert', ['message' => 'Cobro registrado correctamente']);
    }

    public function cerrarModal()
    {
        $this->showModal = false;
        $this->inscripcionId = null;
        $this->pagos = [];
        $this->pagoSeleccionado = null;
        $this->datosRecibo = null;
        $this->showModalExito = false;
        $this->resetMontos();
    }

    public function mostrarInputReserva()
    {
        $this->montoParcial = null;
        $this->showReservaInput = true;
    }

    public function registrarReserva()
    {
        $this->validate([
            'montoParcial' => 'required|numeric|min:1',
        ]);

        // Validar que no pague más de la deuda
        $deudaActual = $this->pagoSeleccionado->monto_total - $this->pagoSeleccionado->monto_abonado;
        
        if ($this->montoParcial > $deudaActual) {
            $this->addError('montoParcial', 'El monto excede la deuda pendiente (' . $deudaActual . ').');
            return;
        }

        // Actualizamos el acumulado
        $nuevoAbonado = $this->pagoSeleccionado->monto_abonado + $this->montoParcial;
        
        $datosUpdate = [
            'monto_abonado' => $nuevoAbonado,
            'fecha_pago' => Carbon::now(), // Registramos fecha del último movimiento
        ];

        // Cambiar estado según el monto
        if ($nuevoAbonado >= $this->pagoSeleccionado->monto_total) {
            $datosUpdate['estado'] = 'pagado';
        } else {
            $datosUpdate['estado'] = 'parcial';
        }

        $this->pagoSeleccionado->update($datosUpdate);

        $this->refrescarSeleccion();
        $this->showReservaInput = false;
    }

    public function pagarCompleto()
    {
        $this->pagoSeleccionado->update([
            'monto_abonado' => $this->pagoSeleccionado->monto_total,
            'estado' => 'pagado',
            'fecha_pago' => Carbon::now(),
        ]);

        $this->refrescarSeleccion();
    }

    public function completarPago()
    {
        $this->pagarCompleto();
    }

    private function refrescarSeleccion()
    {
        // Recargar la lista completa y el ítem seleccionado
        $this->cargarPagos();
        $this->pagoSeleccionado = PagoModel::find($this->pagoSeleccionado->id_pago);
    }

    public function actualizarListaPagos()
    {
        $this->pagos = PagoModel::where('id_inscripcion', $this->inscripcionId)
            ->orderBy('anio')
            ->orderBy('mes')
            ->get();

        $this->pagoSeleccionado = PagoModel::find($this->pagoSeleccionado->id_pago);
    }

    public function descargarReciboPdf()
    {
        if (!$this->datosRecibo) {
            return;
        }

        $pdf = Pdf::loadView('livewire.pagos.pdf.recibo-pdf', [
            'datosRecibo' => $this->datosRecibo
        ]);

        $pdf->setPaper('letter', 'portrait');

        $nombreArchivo = 'Recibo_IGLA_Nro_' . $this->datosRecibo['nro_recibo'] . '.pdf';

        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->output();
        }, $nombreArchivo);
    }
}
