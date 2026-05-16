<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\OtrosIngresosModel;
use App\Models\MetodoPagoModel;
use App\Models\PagoModel;
use App\Models\TransaccionModel;
use App\Models\CajaModel;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class OtrosIngresos extends Component
{
    use WithPagination;

    public $formKey = 1;
    public $datosRecibo = null;
    public $showModalExito = false;

    public $id_ingreso_editando = null;
    
    public $nombre_origen;
    public $concepto;
    public $descripcion;
    public $monto;
    public $fecha_ingreso;
    public $id_metodo_pago;

    public $metodosPago = [];
    
    public $search = '';
    public $mesFilter;

    public $ultimoIngresoId = null;

    public function mount()
    {
        $this->fecha_ingreso = Carbon::now()->format('Y-m-d\TH:i'); 
        $this->metodosPago = MetodoPagoModel::where('activo', true)->get();
        $this->mesFilter = Carbon::now()->format('Y-m'); 
    }
    
    public function render()
    {
        $ingresos = OtrosIngresosModel::where(function($q) {
                $q->whereRaw("LOWER(concepto) like ?", ['%' . strtolower($this->search) . '%'])
                  ->orWhereRaw("LOWER(nombre_origen) like ?", ['%' . strtolower($this->search) . '%']);
            })
            ->where('fecha_ingreso', 'like', $this->mesFilter . '%')
            ->orderBy('fecha_ingreso', 'desc')
            ->paginate(10);

        return view('livewire.otros-ingresos', compact('ingresos'));
    }

    public function limpiarDatos()
    {
        $this->nombre_origen = '';
        $this->concepto = '';
        $this->descripcion = '';
        $this->monto = '';
        $this->id_metodo_pago = '';
        $this->fecha_ingreso = Carbon::now('America/La_Paz')->format('Y-m-d\TH:i');
        $this->resetValidation();

        $this->formKey++;
    }

    public function editar($id)
    {
        $ingreso = OtrosIngresosModel::find($id);

        $this->id_ingreso_editando = $ingreso->id_ingreso;
        $this->nombre_origen = $ingreso->nombre_origen;
        $this->concepto = $ingreso->concepto;
        $this->descripcion = $ingreso->descripcion;
        $this->monto = $ingreso->monto_total;
        $this->fecha_ingreso = Carbon::parse($ingreso->fecha_ingreso)->format('Y-m-d\TH:i');

        $pago = PagoModel::where('origen_id', $id)
                    ->where('origen_type', OtrosIngresosModel::class)
                    ->first();

        if ($pago) {
            $transaccion = TransaccionModel::where('id_pago', $pago->id_pago)->first();
            $this->id_metodo_pago = $transaccion ? $transaccion->id_metodo_pago : '';
        }

        $this->formKey++;
    }

    public function cancelarEdicion()
    {
        $this->id_ingreso_editando = null;
        $this->limpiarDatos();
    }

    public function guardarIngreso()
    {
        $cajaAbierta = CajaModel::where('id_usuario', Auth::id())
                            ->where('estado', 'abierta')
                            ->first();

        if (!$cajaAbierta) {
            $this->addError('caja', '¡Alerta! No tienes una caja abierta en este momento. Por favor, ve a "Operación Diaria" y abre tu caja antes de registrar o editar ingresos.');
            return;
        }

        $this->validate([
            'nombre_origen' => 'nullable|string|max:150',
            'concepto' => 'required|string|max:100',
            'monto' => 'required|numeric|min:0.1',
            'fecha_ingreso' => 'required',
            'id_metodo_pago' => 'required|exists:metodos_pago,id_metodo_pago',
        ]);

        $ingresoResult = null;

        DB::transaction(function () use ($cajaAbierta, &$ingresoResult) {
            
            if ($this->id_ingreso_editando) {
                // ES UNA EDICIÓN: Actualizamos el Ingreso
                $ingresoResult = OtrosIngresosModel::find($this->id_ingreso_editando);
                $ingresoResult->update([
                    'nombre_origen' => $this->nombre_origen,
                    'concepto' => $this->concepto,
                    'descripcion' => $this->descripcion,
                    'monto_total' => $this->monto,
                    'fecha_ingreso' => $this->fecha_ingreso,
                ]);

                // Actualizamos el Pago vinculado
                $pago = PagoModel::where('origen_id', $this->id_ingreso_editando)
                                 ->where('origen_type', OtrosIngresosModel::class)
                                 ->first();

                if ($pago) {
                    $pago->update([
                        'fecha_vencimiento' => $this->fecha_ingreso,
                        'fecha_pago' => $this->fecha_ingreso,
                        'descripcion' => 'Otros Ingresos: ' . $this->concepto,
                        'monto_total' => $this->monto,
                        'monto_abonado' => $this->monto,
                    ]);

                    // Actualizamos la Transacción vinculada
                    $transaccion = TransaccionModel::where('id_pago', $pago->id_pago)->first();
                    if ($transaccion) {
                        $transaccion->update([
                            'id_metodo_pago' => $this->id_metodo_pago,
                            'monto' => $this->monto,
                            'fecha_transaccion' => $this->fecha_ingreso
                        ]);
                    }
                }
                
                $this->id_ingreso_editando = null; // Apagamos el modo edición

            } else {
                // ES UN REGISTRO NUEVO
                $ingresoResult = OtrosIngresosModel::create([
                    'nombre_origen' => $this->nombre_origen,
                    'concepto' => $this->concepto,
                    'descripcion' => $this->descripcion,
                    'monto_total' => $this->monto,
                    'fecha_ingreso' => $this->fecha_ingreso,
                ]);

                $this->ultimoIngresoId = $ingresoResult->id_ingreso;

                $pago = PagoModel::create([
                    'origen_id' => $ingresoResult->id_ingreso,
                    'origen_type' => OtrosIngresosModel::class,
                    'id_estudiante' => null, 
                    'fecha_vencimiento' => $this->fecha_ingreso,
                    'fecha_pago' => $this->fecha_ingreso,
                    'descripcion' => 'Otros Ingresos: ' . $this->concepto,
                    'monto_total' => $this->monto,
                    'monto_abonado' => $this->monto,
                    'estado' => 'pagado'
                ]);

                TransaccionModel::create([
                    'id_pago' => $pago->id_pago,
                    'id_metodo_pago' => $this->id_metodo_pago,
                    'id_caja' => $cajaAbierta->id_caja,
                    'monto' => $this->monto,
                    'fecha_transaccion' => $this->fecha_ingreso
                ]);
            }
        });

        $metodoSeleccionado = MetodoPagoModel::find($this->id_metodo_pago);

        // Armar el recibo
        $this->datosRecibo = [
            'nro_recibo' => str_pad($ingreso->id_ingreso ?? $this->ultimoIngresoId, 6, '0', STR_PAD_LEFT),
            'fecha' => Carbon::parse($this->fecha_ingreso)->format('d/m/Y H:i'),
            'cajero' => Auth::user()->nombre ?? 'Administrador',
            'origen' => $this->nombre_origen ?: 'Anónimo / Varios',
            'concepto' => $this->concepto,
            'descripcion' => $this->descripcion,
            'monto' => $this->monto,
            'metodo_pago' => $metodoSeleccionado->nombre,
        ];
        
        $this->limpiarDatos();
        $this->showModalExito = true;
    }

    public function cerrarModalExito()
    {
        $this->showModalExito = false;
        $this->datosRecibo = null;
    }

    public function eliminar($id)
    {
        // Eliminación en cascada segura para mantener el arqueo
        DB::transaction(function () use ($id) {
            $ingreso = OtrosIngresosModel::find($id);
            if ($ingreso) {
                $pago = PagoModel::where('origen_id', $id)
                                 ->where('origen_type', OtrosIngresosModel::class)
                                 ->first();
                if ($pago) {
                    TransaccionModel::where('id_pago', $pago->id_pago)->delete();
                    $pago->delete();
                }
                $ingreso->delete();
            }
        });
    }

    public function descargarReciboPdf()
    {
        if (!$this->datosRecibo) return;

        $pdf = Pdf::loadView('livewire.pdf.ingreso-recibo-pdf', [
            'datosRecibo' => $this->datosRecibo
        ]);
        $pdf->setPaper('letter', 'portrait');

        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->output();
        }, 'Recibo_Ingreso_Nro_' . $this->datosRecibo['nro_recibo'] . '.pdf');
    }
}
