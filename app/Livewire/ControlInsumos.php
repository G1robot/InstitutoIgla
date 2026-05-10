<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\EstudianteModel;
use App\Models\ControlInsumoModel;
use App\Models\ArticuloModel;
use App\Models\VentaModel;
use App\Models\DetalleVentaModel;
use App\Models\MetodoPagoModel;
use App\Models\PagoModel;
use App\Models\TransaccionModel;
use App\Models\CajaModel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

class ControlInsumos extends Component
{
    use WithPagination;

    public $search = '';
    public $fecha_semana;

    public $metodosPago = [];
    public $metodo_pago_seleccionado;
    
    // Modal Historial
    public $showModalHistorial = false;
    public $estudianteHistorial = null;
    public $historialInsumos = [];

    public $showModalExito = false;
    public $datosRecibo = null;
    public $ultimoIdVenta = null;

    public function mount()
    {
        $this->fecha_semana = Carbon::now()->format('Y-m-d');
        $this->metodosPago = MetodoPagoModel::where('activo', true)->get();

        $efectivo = $this->metodosPago->where('nombre', 'Efectivo')->first();
        $this->metodo_pago_seleccionado = $efectivo ? $efectivo->id_metodo_pago : ($this->metodosPago->first()->id_metodo_pago ?? null);
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingFechaSemana()
    {
        $this->resetPage();
    }

    public function registrarEstado($id_estudiante, $estado)
    {
        // 1. Verificación anti-duplicados
        $existe = ControlInsumoModel::where('id_estudiante', $id_estudiante)
            ->whereDate('fecha_semana', $this->fecha_semana)
            ->first();

        if ($existe) {
            $this->dispatch('toast', ['icon' => 'warning', 'title' => 'Ya existe un registro para esta semana.']);
            return;
        }

        // 2. Lógica para COBRO financiero
        if ($estado === 'pagado') {
            
            // A. Buscar el artículo dinámicamente por nombre
            $articulo = ArticuloModel::where('nombre', 'INSUMOS')->first();
            if (!$articulo) {
                $this->addError('general', 'El artículo "INSUMOS" no existe en el catálogo. Por favor, créalo o verifica el nombre.');
                return;
            }

            // B. Validar método de pago
            if (!$this->metodo_pago_seleccionado) {
                $this->addError('general', 'Debe seleccionar un método de pago en la parte superior.');
                return;
            }

            // C. Validar Caja Abierta
            $cajaAbierta = CajaModel::where('id_usuario', Auth::id())
                ->where('estado', 'abierta')
                ->first();

            if (!$cajaAbierta) {
                $this->addError('general', 'No tienes una caja abierta. Ve a Operación Diaria para abrir tu caja antes de cobrar.');
                return;
            }

            // D. Transacción Segura
            DB::transaction(function () use ($id_estudiante, $estado, $cajaAbierta, $articulo) {
                
                $venta = VentaModel::create([
                    'id_estudiante' => $id_estudiante,
                    'fecha_venta' => Carbon::now(),
                    'monto_total' => $articulo->precio,
                    'estado' => 'finalizada'
                ]);

                DetalleVentaModel::create([
                    'id_venta' => $venta->id_venta,
                    'id_articulo' => $articulo->id_articulo,
                    'cantidad' => 1,
                    'precio_unitario' => $articulo->precio,
                    'subtotal' => $articulo->precio
                ]);

                $pago = PagoModel::create([
                    'origen_id' => $venta->id_venta,
                    'origen_type' => VentaModel::class,
                    'id_estudiante' => $id_estudiante,
                    'fecha_vencimiento' => Carbon::now(),
                    'fecha_pago' => Carbon::now(),
                    'descripcion' => 'Insumo Semanal (' . $this->fecha_semana . ')',
                    'monto_total' => $articulo->precio,
                    'monto_abonado' => $articulo->precio,
                    'estado' => 'pagado'
                ]);

                // Usamos el método de pago que la cajera seleccionó en la vista
                TransaccionModel::create([
                    'id_pago' => $pago->id_pago,
                    'id_metodo_pago' => $this->metodo_pago_seleccionado,
                    'id_caja' => $cajaAbierta->id_caja,
                    'monto' => $articulo->precio,
                    'fecha_transaccion' => Carbon::now()
                ]);

                ControlInsumoModel::create([
                    'id_estudiante' => $id_estudiante,
                    'fecha_semana' => $this->fecha_semana,
                    'estado' => $estado,
                    'id_venta' => $venta->id_venta
                ]);

                $estudiante = EstudianteModel::find($id_estudiante);
                $this->datosRecibo = [
                    'nro_recibo' => str_pad($venta->id_venta, 6, '0', STR_PAD_LEFT),
                    'estudiante' => $estudiante->nombre . ' ' . $estudiante->apellido,
                    'ci' => $estudiante->ci,
                    'fecha' => Carbon::now()->format('d/m/Y H:i'),
                    'cajero' => Auth::user()->nombre ?? 'Administrador',
                    'items' => [
                        [
                            'cantidad' => 1,
                            'nombre' => $articulo->nombre . ' (Semana ' . Carbon::parse($this->fecha_semana)->format('d/m') . ')',
                            'precio' => $articulo->precio,
                            'subtotal' => $articulo->precio
                        ]
                    ],
                    'total' => $articulo->precio,
                    'ingresado' => $articulo->precio,
                    'cambio' => 0,
                ];

            });
            $this->showModalExito = true;
        } 
        else {
            ControlInsumoModel::create([
                'id_estudiante' => $id_estudiante,
                'fecha_semana' => $this->fecha_semana,
                'estado' => $estado,
                'id_venta' => null
            ]);
            $this->dispatch('toast', ['icon' => 'success', 'title' => 'Estado registrado exitosamente.']);
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

        // Reutilizamos la misma vista que usas en VentaArticulos
        $pdf = Pdf::loadView('livewire.pdf.venta-recibo-pdf', [
            'datosRecibo' => $this->datosRecibo
        ]);

        $pdf->setPaper('letter', 'portrait');
        $nombreArchivo = 'Recibo_IGLA_Insumos_' . $this->datosRecibo['nro_recibo'] . '.pdf';

        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->output();
        }, $nombreArchivo);
    }

    public function abrirHistorial($id_estudiante)
    {
        $this->estudianteHistorial = EstudianteModel::find($id_estudiante);
        $this->historialInsumos = ControlInsumoModel::where('id_estudiante', $id_estudiante)
                                    ->orderBy('fecha_semana', 'desc')
                                    ->get();
        $this->showModalHistorial = true;
    }

    public function cerrarHistorial()
    {
        $this->showModalHistorial = false;
        $this->estudianteHistorial = null;
        $this->historialInsumos = [];
    }

    public function render()
    {
        $search = mb_strtolower(trim($this->search));
        $fecha = $this->fecha_semana;

        $estudiantes = EstudianteModel::with(['controlInsumos' => function($query) use ($fecha) {
                $query->whereDate('fecha_semana', $fecha);
            }])
            ->where(function ($q) use ($search) {
                $q->whereRaw('LOWER(nombre) LIKE ?', ["%{$search}%"])
                  ->orWhereRaw('LOWER(apellido) LIKE ?', ["%{$search}%"])
                  ->orWhere('ci', 'like', "%{$search}%");
            })
            ->orderBy('apellido', 'asc')
            ->paginate(15);

        return view('livewire.control-insumos', compact('estudiantes'));
    }
}
