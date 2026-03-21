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
    public function guardarIngreso()
    {
        $this->validate([
            'nombre_origen' => 'nullable|string|max:150',
            'concepto' => 'required|string|max:100',
            'monto' => 'required|numeric|min:0.1',
            'fecha_ingreso' => 'required',
            'id_metodo_pago' => 'required|exists:metodos_pago,id_metodo_pago',
        ]);

        $cajaAbierta = CajaModel::where('id_usuario', Auth::id())
                            ->where('estado', 'abierta')
                            ->first();

        if (!$cajaAbierta) {
            $this->addError('general', 'No hay una caja abierta para registrar este ingreso.');
            return;
        }

        DB::transaction(function () use ($cajaAbierta, &$ingreso) {
            // A. Crear el Ingreso
            $ingreso = OtrosIngresosModel::create([
                'nombre_origen' => $this->nombre_origen,
                'concepto' => $this->concepto,
                'descripcion' => $this->descripcion,
                'monto_total' => $this->monto,
                'fecha_ingreso' => $this->fecha_ingreso,
            ]);

            $this->ultimoIngresoId = $ingreso->id_ingreso;

            // B. Crear Pago Polimórfico
            $pago = PagoModel::create([
                'origen_id' => $ingreso->id_ingreso,
                'origen_type' => OtrosIngresosModel::class,
                'id_estudiante' => null, // No es de un estudiante
                'fecha_vencimiento' => $this->fecha_ingreso,
                'fecha_pago' => $this->fecha_ingreso,
                'descripcion' => 'Otros Ingresos: ' . $this->concepto,
                'monto_total' => $this->monto,
                'monto_abonado' => $this->monto,
                'estado' => 'pagado'
            ]);

            // C. Transacción a Caja
            TransaccionModel::create([
                'id_pago' => $pago->id_pago,
                'id_metodo_pago' => $this->id_metodo_pago,
                'id_caja' => $cajaAbierta->id_caja,
                'monto' => $this->monto,
                'fecha_transaccion' => $this->fecha_ingreso
            ]);
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
