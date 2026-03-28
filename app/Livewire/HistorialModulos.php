<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\EstudianteModel;
use App\Models\InscripcionModuloModel;
use App\Models\PagoModel;
use App\Models\MetodoPagoModel;
use App\Models\TransaccionModel;
use App\Models\CajaModel;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;

class HistorialModulos extends Component
{
    public $search = '';
    public $estudianteSeleccionado = null;
    public $modulos = [];
    public $pagoPUP = null;
    public $estudiantesEncontrados = [];

    public $transaccionesPago = [];
    public $showModalPago = false;
    public $pagoIdSeleccionado = null;
    public $montoAPagar = '';
    public $idMetodoPago = '';
    public $saldoPendiente = 0;
    public $descripcionPago = '';
    public $metodosPago = [];

    public $montosPago = [];
    public $totalIngresado = 0;
    public $efectivoRecibido = null;
    public $cambio = 0;
    public $fechaPagoManual;

    // --- VARIABLES PARA EL RECIBO ---
    public $showModalExito = false;
    public $datosRecibo = null;
    

    public function mount()
    {
        $this->metodosPago = MetodoPagoModel::where('activo', true)->get();
        $this->resetMontosPago();
    }

    public function resetMontosPago()
    {
        $this->montosPago = [];
        foreach ($this->metodosPago as $m) {
            $this->montosPago[$m->id_metodo_pago] = '';
        }
        $this->totalIngresado = 0;
        $this->efectivoRecibido = null;
        $this->cambio = 0;
    }

    public function updatedMontosPago()
    {
        $this->totalIngresado = 0;
        foreach($this->montosPago as $m) {
            $this->totalIngresado += (float) ($m === '' ? 0 : $m);
        }
        $this->calcularCambio();
    }

    public function updatedEfectivoRecibido()
    {
        $this->calcularCambio();
    }

    public function calcularCambio()
    {
        $montoEfectivoRegistrado = 0;
        foreach ($this->metodosPago as $metodo) {
            if ($metodo->es_efectivo) {
                $monto = $this->montosPago[$metodo->id_metodo_pago] ?? 0;
                $montoEfectivoRegistrado += (float) ($monto === '' ? 0 : $monto);
            }
        }

        $efectivoReal = (float) ($this->efectivoRecibido === '' || is_null($this->efectivoRecibido) ? 0 : $this->efectivoRecibido);

        if ($efectivoReal > 0 && $efectivoReal >= $montoEfectivoRegistrado) {
            $this->cambio = $efectivoReal - $montoEfectivoRegistrado;
        } else {
            $this->cambio = 0;
        }
    }

    public function llenarSaldo($idMetodo)
    {
        $this->resetMontosPago();
        $this->montosPago[$idMetodo] = $this->saldoPendiente;
        $this->updatedMontosPago();
    }

    public function updatedSearch()
    {
        if (strlen($this->search) > 2) {
            $this->estudiantesEncontrados = EstudianteModel::where('ci', 'like', '%' . $this->search . '%')
                ->orWhereRaw('LOWER(nombre) LIKE ?', ['%' . strtolower($this->search) . '%'])
                ->orWhereRaw('LOWER(apellido) LIKE ?', ['%' . strtolower($this->search) . '%'])
                ->take(5)
                ->get();
        } else {
            $this->estudiantesEncontrados = [];
        }
    }

    public function seleccionarEstudiante($id)
    {
        $this->estudianteSeleccionado = EstudianteModel::find($id);
        $this->search = '';
        $this->estudiantesEncontrados = [];
        $this->cargarModulos();
    }

    public function cargarModulos()
    {
        if ($this->estudianteSeleccionado) {
            // 1. Traemos los módulos
            $this->modulos = InscripcionModuloModel::with(['modulo', 'pagos'])
                ->where('id_estudiante', $this->estudianteSeleccionado->id_estudiante)
                ->orderBy('fecha_inscripcion', 'desc')
                ->get();

            // 2. Traemos el pago del PUP (Si existe)
            $this->pagoPUP = PagoModel::where('id_estudiante', $this->estudianteSeleccionado->id_estudiante)
                ->where('origen_type', 'App\Models\TarifaModel')
                ->first();
        }
    }

    public function abrirModalPago($idPago)
    {
        $pago = PagoModel::find($idPago);
        if ($pago) {
            // QUitar esto luego para Fecha automática
            $this->resetMontosPago();
            $this->fechaPagoManual = Carbon::now()->format('Y-m-d');

            $this->pagoIdSeleccionado = $pago->id_pago;
            $this->descripcionPago = $pago->descripcion;
            $this->saldoPendiente = $pago->monto_total - $pago->monto_abonado;
            
            //Transacciones relacionadas a este pago
            $this->transaccionesPago = TransaccionModel::with('metodo')
                ->where('id_pago', $idPago)
                ->orderBy('fecha_transaccion', 'desc')
                ->get();
            
            $this->showModalPago = true;
            $this->resetValidation();
        }
    }

    public function cerrarModalPago()
    {
        $this->showModalPago = false;
        $this->pagoIdSeleccionado = null;
        $this->transaccionesPago = [];
    }

    public function registrarAbono()
    {
        if ($this->totalIngresado <= 0) {
            $this->addError('pago', 'Debe ingresar al menos un monto a pagar.');
            return;
        }

        if ($this->totalIngresado > $this->saldoPendiente + 100) {
            $this->addError('pago', 'El monto ingresado es excesivamente mayor al saldo pendiente.');
            return;
        }

        $cajaAbierta = CajaModel::where('id_usuario', Auth::id())->where('estado', 'abierta')->first();
        
        if (!$cajaAbierta) {
            $this->addError('general', 'No hay una caja abierta para recibir este dinero.');
            return;
        }

        $pago = PagoModel::find($this->pagoIdSeleccionado);

        //quitar esto luego para Fecha automática
        $fechaCobro = $this->fechaPagoManual 
            ? Carbon::parse($this->fechaPagoManual)->setTimeFrom(Carbon::now()) 
            : Carbon::now();

        if ($pago) {
            DB::transaction(function () use ($cajaAbierta, $pago, $fechaCobro) {
                
                $bolsaDinero = [];
                foreach ($this->montosPago as $idMetodo => $monto) {
                    if (!empty($idMetodo) && is_numeric($idMetodo) && (float)$monto > 0) {
                        $bolsaDinero[$idMetodo] = (float)$monto;
                    }
                }

                $montoAbonadoTotalHoy = 0;
                $deudaActual = $pago->monto_total - $pago->monto_abonado;

                foreach ($bolsaDinero as $idMetodo => $monto) {
                    if ($deudaActual <= 0) break;
                    
                    $montoUsar = min($deudaActual, $monto);
                    TransaccionModel::create([
                        'id_pago' => $pago->id_pago,
                        'id_metodo_pago' => $idMetodo,
                        'id_caja' => $cajaAbierta->id_caja,
                        'monto' => $montoUsar,
                        'fecha_transaccion' => $fechaCobro
                        // 'fecha_transaccion' => Carbon::now()
                    ]);
                    $montoAbonadoTotalHoy += $montoUsar;
                    $deudaActual -= $montoUsar;
                }
                // Actualizar la deuda
                if($montoAbonadoTotalHoy > 0){
                    $pago->monto_abonado += $montoAbonadoTotalHoy;
                    $pago->fecha_pago = $fechaCobro;

                    if ($pago->monto_abonado >= $pago->monto_total - 0.1) {
                        $pago->estado = 'pagado';
                    } else {
                        $pago->estado = 'parcial';
                    }
                    $pago->save();
                }
            });

            // Preparar recibo térmico
            $pagoActualizado = PagoModel::find($this->pagoIdSeleccionado);
            $nuevoSaldo = max(0, $pagoActualizado->monto_total - $pagoActualizado->monto_abonado);
            
            // Para el recibo, armamos un string con los métodos usados
            $nombresMetodos = [];
            foreach ($this->montosPago as $idMetodo => $monto) {
                if ((float)$monto > 0) {
                    $metodoObj = MetodoPagoModel::find($idMetodo);
                    if($metodoObj) $nombresMetodos[] = $metodoObj->nombre;
                }
            }
            $stringMetodos = implode(' + ', $nombresMetodos);

            $this->datosRecibo = [
                'nro_recibo' => str_pad($pagoActualizado->id_pago, 6, '0', STR_PAD_LEFT),
                'estudiante' => $this->estudianteSeleccionado->nombre . ' ' . $this->estudianteSeleccionado->apellido,
                'ci' => $this->estudianteSeleccionado->ci,
                'fecha' => Carbon::parse($fechaCobro)->format('d/m/Y H:i'),
                'cajero' => Auth::user()->nombre ?? 'Administrador',
                'concepto' => 'Abono a ' . $pagoActualizado->descripcion,
                'metodo_pago' => $stringMetodos,
                'costo_total' => $pagoActualizado->monto_total,
                'monto_abonado_hoy' => min($this->totalIngresado, $this->saldoPendiente),
                'saldo_pendiente' => $nuevoSaldo,
                'cambio' => $this->cambio
            ];

            $this->cerrarModalPago();
            $this->cargarModulos();
            $this->showModalExito = true;
        }
    }

    public function cerrarModalExito()
    {
        $this->showModalExito = false;
        $this->datosRecibo = null;
    }

    public function descargarReciboPdf()
    {
        if (!$this->datosRecibo) return;

        $pdf = Pdf::loadView('livewire.pdf.abono-recibo-pdf', [
            'datosRecibo' => $this->datosRecibo
        ]);
        $pdf->setPaper('letter', 'portrait');

        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->output();
        }, 'Recibo_Abono_' . $this->datosRecibo['nro_recibo'] . '.pdf');
    }

    public function marcarFinalizado($idInscripcionModulo)
    {
        $inscripcion = InscripcionModuloModel::find($idInscripcionModulo);
        if ($inscripcion) {
            $inscripcion->estado = 'finalizado';
            $inscripcion->save();
            
            // Recargamos la lista
            $this->cargarModulos(); 
            session()->flash('success', 'El módulo se ha marcado como FINALIZADO.');
        }
    }
    
    public function reactivarModulo($idInscripcionModulo)
    {
        $inscripcion = InscripcionModuloModel::find($idInscripcionModulo);
        if ($inscripcion) {
            $inscripcion->estado = 'cursando';
            $inscripcion->save();
            $this->cargarModulos();
        }
    }
    
    public function render()
    {
        return view('livewire.historial-modulos');
    }
}
