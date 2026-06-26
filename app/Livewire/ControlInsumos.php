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
use App\Models\CategoriaArticuloModel;
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
    public $articulosInsumo = [];
    public $articulo_seleccionado;
    
    // Modal Historial
    public $showModalHistorial = false;
    public $estudianteHistorial = null;
    public $historialInsumos = [];

    public $showModalExito = false;
    public $datosRecibo = null;
    public $ultimoIdVenta = null;

    // Modal Múltiple (Pagos)
    public $showModalMultiple = false;
    public $estudianteMultiple = null;
    public $fechasMultiple = [];
    public $montosPago = [];

    // NUEVO: Modal Estados Múltiples (Faltas / Licencias)
    public $showModalEstado = false;
    public $estudianteEstado = null;
    public $estadoSeleccionado = '';
    public $fechasEstado = [];

    // Modal Abono
    public $showModalAbono = false;
    public $estudianteAbono = null;
    public $controlInsumoActivo = null;
    public $monto_a_abonar = 0;
    public $deuda_actual = 0;
    public $fecha_abono; // NUEVO: Fecha específica para el abono

    public function mount()
    {
        $this->fecha_semana = Carbon::now()->format('Y-m-d');
        $this->metodosPago = MetodoPagoModel::where('activo', true)->get();

        $efectivo = $this->metodosPago->where('nombre', 'Efectivo')->first();
        $this->metodo_pago_seleccionado = $efectivo ? $efectivo->id_metodo_pago : ($this->metodosPago->first()->id_metodo_pago ?? null);

        $categoriaInsumo = CategoriaArticuloModel::where('nombre', 'INSUMOS SEMANAL')->first();
        
        if ($categoriaInsumo) {
            $this->articulosInsumo = ArticuloModel::where('id_categoria_articulo', $categoriaInsumo->id_categoria_articulo)->get();
        } else {
            $this->articulosInsumo = ArticuloModel::where('nombre', 'like', '%INSUMO%')->get();
        }

        $this->articulo_seleccionado = $this->articulosInsumo->first()->id_articulo ?? null;
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    // ========================================================
    // NUEVA LÓGICA: Faltas y Licencias Múltiples
    // ========================================================
    public function abrirModalEstado($id_estudiante, $estado)
    {
        $this->estudianteEstado = EstudianteModel::find($id_estudiante);
        $this->estadoSeleccionado = $estado;
        $this->fechasEstado = [Carbon::now()->format('Y-m-d')];
        $this->showModalEstado = true;
    }

    public function agregarFechaEstado()
    {
        $ultimaFecha = end($this->fechasEstado);
        $nuevaFecha = $ultimaFecha ? Carbon::parse($ultimaFecha)->addDays(7)->format('Y-m-d') : Carbon::now()->format('Y-m-d');
        $this->fechasEstado[] = $nuevaFecha;
    }

    public function quitarFechaEstado($indice)
    {
        unset($this->fechasEstado[$indice]);
        $this->fechasEstado = array_values($this->fechasEstado);
    }

    public function cerrarModalEstado()
    {
        $this->showModalEstado = false;
        $this->estudianteEstado = null;
        $this->estadoSeleccionado = '';
        $this->fechasEstado = [];
    }

    public function procesarEstadoMultiple()
    {
        if(empty($this->fechasEstado)) {
            $this->addError('estado', 'Debe agregar al menos una fecha.'); return;
        }

        $semanasSeleccionadas = [];
        foreach($this->fechasEstado as $fecha) {
            $inicioSemana = Carbon::parse($fecha)->startOfWeek()->format('Y-m-d');
            $finSemana = Carbon::parse($fecha)->endOfWeek()->format('Y-m-d');

            if (in_array($inicioSemana, $semanasSeleccionadas)) {
                $this->addError('estado', 'Has añadido dos fechas que pertenecen a la misma semana.'); return;
            }
            $semanasSeleccionadas[] = $inicioSemana;

            $existe = ControlInsumoModel::where('id_estudiante', $this->estudianteEstado->id_estudiante)
                        ->whereBetween('fecha_semana', [$inicioSemana, $finSemana])
                        ->where('estado', '!=', 'anulado')->first();
                        
            if($existe) {
                $this->addError('estado', "El alumno ya tiene un registro en la semana del " . Carbon::parse($inicioSemana)->format('d/m/Y')); return;
            }
        }

        foreach($this->fechasEstado as $fecha) {
            ControlInsumoModel::create([
                'id_estudiante' => $this->estudianteEstado->id_estudiante,
                'fecha_semana' => $fecha,
                'estado' => $this->estadoSeleccionado,
                'id_venta' => null
            ]);
        }

        $this->cerrarModalEstado();
        $this->dispatch('toast', ['icon' => 'success', 'title' => ucfirst($this->estadoSeleccionado) . ' registrada exitosamente.']);
    }

    // ========================================================
    // LÓGICAS RESTANTES
    // ========================================================
    public function cerrarModalExito() 
    {
        $this->showModalExito = false;
        $this->datosRecibo = null;
    }

    public function descargarReciboPdf()
    {
        if (!$this->datosRecibo) return;

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

    public function abrirCobroMultiple($id_estudiante)
    {
        $this->estudianteMultiple = EstudianteModel::find($id_estudiante);
        $this->fechasMultiple = [Carbon::now()->format('Y-m-d')]; 
        $this->montosPago = [];
        $this->showModalMultiple = true;
    }

    public function agregarFechaMultiple()
    {
        $ultimaFecha = end($this->fechasMultiple);
        $nuevaFecha = $ultimaFecha 
            ? Carbon::parse($ultimaFecha)->addDays(7)->format('Y-m-d') 
            : Carbon::now()->format('Y-m-d');
            
        $this->fechasMultiple[] = $nuevaFecha;
    }

    public function quitarFechaMultiple($indice)
    {
        unset($this->fechasMultiple[$indice]);
        $this->fechasMultiple = array_values($this->fechasMultiple);
    }

    public function cerrarModalMultiple()
    {
        $this->showModalMultiple = false;
        $this->estudianteMultiple = null;
        $this->fechasMultiple = [];
        $this->montosPago = [];
    }

    public function procesarCobroMultiple()
    {
        if(empty($this->fechasMultiple)) {
            $this->addError('multiple', 'Debe agregar al menos una fecha.'); return;
        }
        if (!$this->articulo_seleccionado) {
            $this->addError('multiple', 'Seleccione un tipo de Insumo en la barra superior.'); return;
        }

        $articulo = ArticuloModel::find($this->articulo_seleccionado);
        $cantidadSemanas = count($this->fechasMultiple);
        $totalCobrar = $articulo->precio * $cantidadSemanas;

        $totalIngresado = collect($this->montosPago)->map(fn($v) => (float)$v)->sum();
        
        if ($totalIngresado < ($totalCobrar - 0.05)) {
            $this->addError('multiple', "El monto ingresado ($totalIngresado Bs) no cubre el total de $totalCobrar Bs."); 
            return;
        }

        $cajaAbierta = CajaModel::where('id_usuario', Auth::id())->where('estado', 'abierta')->first();
        if (!$cajaAbierta) {
            $this->addError('multiple', 'No hay caja abierta.'); return;
        }

        $semanasSeleccionadas = [];
        foreach($this->fechasMultiple as $fecha) {
            $inicioSemana = Carbon::parse($fecha)->startOfWeek()->format('Y-m-d');
            $finSemana = Carbon::parse($fecha)->endOfWeek()->format('Y-m-d');

            if (in_array($inicioSemana, $semanasSeleccionadas)) {
                $this->addError('multiple', 'Has añadido dos fechas que pertenecen a la misma semana.'); return;
            }
            $semanasSeleccionadas[] = $inicioSemana;

            $existe = ControlInsumoModel::where('id_estudiante', $this->estudianteMultiple->id_estudiante)
                        ->whereBetween('fecha_semana', [$inicioSemana, $finSemana])
                        ->where('estado', '!=', 'anulado')->first();
                        
            if($existe) {
                $this->addError('multiple', "El alumno ya pagó la semana del " . Carbon::parse($inicioSemana)->format('d/m/Y')); return;
            }
        }

        DB::transaction(function () use ($articulo, $cajaAbierta, $totalCobrar, $cantidadSemanas, $totalIngresado) {
            
            $venta = VentaModel::create([
                'id_estudiante' => $this->estudianteMultiple->id_estudiante,
                'fecha_venta' => Carbon::now(),
                'monto_total' => $totalCobrar,
                'estado' => 'finalizada'
            ]);

            $this->ultimoIdVenta = $venta->id_venta;
            $itemsRecibo = [];

            foreach($this->fechasMultiple as $fecha) {
                DetalleVentaModel::create([
                    'id_venta' => $venta->id_venta,
                    'id_articulo' => $articulo->id_articulo,
                    'cantidad' => 1,
                    'precio_unitario' => $articulo->precio,
                    'subtotal' => $articulo->precio
                ]);

                ControlInsumoModel::create([
                    'id_estudiante' => $this->estudianteMultiple->id_estudiante,
                    'fecha_semana' => $fecha,
                    'estado' => 'pagado',
                    'id_venta' => $venta->id_venta
                ]);

                $itemsRecibo[] = [
                    'shadow_inner' => 1,
                    'cantidad' => 1,
                    'nombre' => $articulo->nombre . ' (Sem: ' . Carbon::parse($fecha)->format('d/m/y') . ')',
                    'precio' => $articulo->precio,
                    'subtotal' => $articulo->precio
                ];
            }

            $pago = PagoModel::create([
                'origen_id' => $venta->id_venta,
                'origen_type' => VentaModel::class,
                'id_estudiante' => $this->estudianteMultiple->id_estudiante,
                'fecha_vencimiento' => Carbon::now(),
                'fecha_pago' => Carbon::now(),
                'descripcion' => "Pago Múltiple Insumos ($cantidadSemanas semanas)",
                'monto_total' => $totalCobrar,
                'monto_abonado' => $totalCobrar,
                'estado' => 'pagado'
            ]);

            $cambio = max(0, $totalIngresado - $totalCobrar);
            $montosFinales = $this->montosPago;

            if ($cambio > 0) {
                $efectivo = MetodoPagoModel::where('nombre', 'like', '%Efectivo%')->first();
                if ($efectivo && isset($montosFinales[$efectivo->id_metodo_pago])) {
                    $montosFinales[$efectivo->id_metodo_pago] -= $cambio;
                }
            }

            $metodosUsados = [];
            foreach ($montosFinales as $id_metodo => $monto) {
                if ((float)$monto > 0) {
                    TransaccionModel::create([
                        'id_pago' => $pago->id_pago,
                        'id_metodo_pago' => $id_metodo,
                        'id_caja' => $cajaAbierta->id_caja,
                        'monto' => (float)$monto,
                        'fecha_transaccion' => Carbon::now()
                    ]);
                    
                    $metodoModel = MetodoPagoModel::find($id_metodo);
                    if($metodoModel) {
                        $metodosUsados[] = $metodoModel->nombre . ': ' . number_format((float)$monto, 2) . ' Bs';
                    }
                }
            }

            $this->datosRecibo = [
                'nro_recibo' => str_pad($venta->id_venta, 6, '0', STR_PAD_LEFT),
                'estudiante' => $this->estudianteMultiple->nombre . ' ' . $this->estudianteMultiple->apellido,
                'ci' => $this->estudianteMultiple->ci,
                'fecha' => Carbon::now()->format('d/m/Y H:i'),
                'cajero' => Auth::user()->nombre ?? 'Administrador',
                'items' => $itemsRecibo,
                'total' => $totalCobrar,
                'ingresado' => $totalIngresado,
                'cambio' => $cambio,
                'metodos_pago' => implode(' | ', $metodosUsados)
            ];
        });

        $this->cerrarModalMultiple();
        $this->showModalExito = true;
    }

    public function render()
    {
        $search = mb_strtolower(trim($this->search));
        
        // El render siempre evalúa la semana actual (hoy) para mostrar la lista principal
        $inicioSemana = Carbon::now()->startOfWeek()->format('Y-m-d');
        $finSemana = Carbon::now()->endOfWeek()->format('Y-m-d');

        $estudiantes = EstudianteModel::with(['controlInsumos' => function($query) use ($inicioSemana, $finSemana) {
                $query->whereBetween('fecha_semana', [$inicioSemana, $finSemana])
                    ->where('estado', '!=', 'anulado');
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

    public function forzarSincronizacionAnulados()
    {
        $ventasAnuladas = VentaModel::where('estado', 'anulada')->pluck('id_venta');
        
        $cantidad = ControlInsumoModel::whereIn('id_venta', $ventasAnuladas)
            ->where('estado', '!=', 'anulado')
            ->update(['estado' => 'anulado']);
            
        $this->dispatch('toast', ['icon' => 'success', 'title' => "Se corrigieron $cantidad registros antiguos."]);
        
        if ($this->estudianteHistorial) {
            $this->abrirHistorial($this->estudianteHistorial->id_estudiante);
        }
    }

    public function abrirModalAbono($id_estudiante, $id_control_insumo = null)
    {
        $this->estudianteAbono = EstudianteModel::find($id_estudiante);
        $this->fecha_abono = Carbon::now()->format('Y-m-d'); // Default hoy

        if ($id_control_insumo) {
            $this->controlInsumoActivo = ControlInsumoModel::with('venta.pago')->find($id_control_insumo);
            $pago = $this->controlInsumoActivo->venta->pago;
            $this->deuda_actual = $pago->monto_total - $pago->monto_abonado;
        } else {
            if (!$this->articulo_seleccionado) {
                $this->addError('general', 'Seleccione un tipo de Insumo arriba primero.');
                return;
            }
            $articulo = ArticuloModel::find($this->articulo_seleccionado);
            $this->controlInsumoActivo = null;
            $this->deuda_actual = $articulo->precio;
        }

        $this->monto_a_abonar = '';
        $this->showModalAbono = true;
    }

    public function cerrarModalAbono()
    {
        $this->showModalAbono = false;
        $this->estudianteAbono = null;
        $this->controlInsumoActivo = null;
        $this->monto_a_abonar = 0;
    }

    public function procesarAbono()
    {
        $monto = (float) $this->monto_a_abonar;
        if ($monto <= 0 || $monto > $this->deuda_actual) {
            $this->addError('abono', 'El monto debe ser mayor a 0 y no puede superar la deuda ('.$this->deuda_actual.' Bs).');
            return;
        }

        if (!$this->metodo_pago_seleccionado) {
            $this->addError('abono', 'Debe seleccionar un método de pago.');
            return;
        }

        $cajaAbierta = CajaModel::where('id_usuario', Auth::id())->where('estado', 'abierta')->first();
        if (!$cajaAbierta) {
            $this->addError('abono', 'No tienes una caja abierta.');
            return;
        }

        DB::transaction(function () use ($monto, $cajaAbierta) {
            
            if ($this->controlInsumoActivo) {
                $pago = $this->controlInsumoActivo->venta->pago;
                $nuevo_abonado = $pago->monto_abonado + $monto;

                TransaccionModel::create([
                    'id_pago' => $pago->id_pago,
                    'id_metodo_pago' => $this->metodo_pago_seleccionado,
                    'id_caja' => $cajaAbierta->id_caja,
                    'monto' => $monto,
                    'fecha_transaccion' => Carbon::now()
                ]);

                if ($nuevo_abonado >= $pago->monto_total) {
                    $pago->update(['monto_abonado' => $nuevo_abonado, 'estado' => 'pagado']);
                    $this->controlInsumoActivo->update(['estado' => 'pagado']);
                } else {
                    $pago->update(['monto_abonado' => $nuevo_abonado]);
                }

                $this->ultimoIdVenta = $this->controlInsumoActivo->id_venta;

            } else {
                $articulo = ArticuloModel::find($this->articulo_seleccionado);

                $venta = VentaModel::create([
                    'id_estudiante' => $this->estudianteAbono->id_estudiante,
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
                    'id_estudiante' => $this->estudianteAbono->id_estudiante,
                    'fecha_vencimiento' => Carbon::now(),
                    'fecha_pago' => Carbon::now(),
                    'descripcion' => 'Insumo Semanal (' . Carbon::parse($this->fecha_abono)->format('d/m') . ')',
                    'monto_total' => $articulo->precio,
                    'monto_abonado' => $monto,
                    'estado' => 'parcial'
                ]);

                TransaccionModel::create([
                    'id_pago' => $pago->id_pago,
                    'id_metodo_pago' => $this->metodo_pago_seleccionado,
                    'id_caja' => $cajaAbierta->id_caja,
                    'monto' => $monto,
                    'fecha_transaccion' => Carbon::now()
                ]);

                // Guardamos el insumo con la fecha seleccionada en el input
                ControlInsumoModel::create([
                    'id_estudiante' => $this->estudianteAbono->id_estudiante,
                    'fecha_semana' => $this->fecha_abono,
                    'estado' => 'pendiente',
                    'id_venta' => $venta->id_venta
                ]);

                $this->ultimoIdVenta = $venta->id_venta;
            }

            $this->datosRecibo = [
                'nro_recibo' => str_pad($this->ultimoIdVenta, 6, '0', STR_PAD_LEFT),
                'estudiante' => $this->estudianteAbono->nombre . ' ' . $this->estudianteAbono->apellido,
                'ci' => $this->estudianteAbono->ci,
                'fecha' => Carbon::now()->format('d/m/Y H:i'),
                'cajero' => Auth::user()->nombre ?? 'Administrador',
                'items' => [
                    [
                        'cantidad' => 1,
                        'nombre' => 'ABONO - Insumo Semanal',
                        'precio' => $monto,
                        'subtotal' => $monto
                    ]
                ],
                'total' => $monto,
                'ingresado' => $monto,
                'cambio' => 0,
            ];
        });

        $this->cerrarModalAbono();
        $this->showModalExito = true;
    }

    public function llenarSaldo($id_metodo_pago)
    {
        $art = ArticuloModel::find($this->articulo_seleccionado);
        $total = $art ? $art->precio * count($this->fechasMultiple) : 0;
        $ingresadoOtros = 0;

        foreach ($this->montosPago as $key => $monto) {
            if ($key != $id_metodo_pago) {
                $ingresadoOtros += (float)$monto;
            }
        }

        $restante = max(0, $total - $ingresadoOtros);
        $this->montosPago[$id_metodo_pago] = $restante > 0 ? round($restante, 2) : '';
    }
    
    public function anularEstadoInsumo($id_control_insumo)
    {
        $insumo = ControlInsumoModel::find($id_control_insumo);
        
        if ($insumo && in_array($insumo->estado, ['falta', 'licencia'])) {
            $insumo->update(['estado' => 'anulado']);
            $this->dispatch('toast', ['icon' => 'success', 'title' => 'Acción deshecha. El alumno vuelve a estar sin registrar.']);
        }
    }
}