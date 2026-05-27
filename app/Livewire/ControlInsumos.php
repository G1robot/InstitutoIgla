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

    public $showModalMultiple = false;
    public $estudianteMultiple = null;
    public $fechasMultiple = [];
    public $montosPago = [];

    public $showModalAbono = false;
    public $estudianteAbono = null;
    public $controlInsumoActivo = null;
    public $monto_a_abonar = 0;
    public $deuda_actual = 0;

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
            // Plan B por si borran la categoría: buscamos por nombre
            $this->articulosInsumo = ArticuloModel::where('nombre', 'like', '%INSUMO%')->get();
        }

        // Seleccionar el primero por defecto
        $this->articulo_seleccionado = $this->articulosInsumo->first()->id_articulo ?? null;
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
        $inicioSemana = Carbon::parse($this->fecha_semana)->startOfWeek()->format('Y-m-d');
        $finSemana = Carbon::parse($this->fecha_semana)->endOfWeek()->format('Y-m-d');

        $existe = ControlInsumoModel::where('id_estudiante', $id_estudiante)
            ->whereBetween('fecha_semana', [$inicioSemana, $finSemana])
            ->where('estado', '!=', 'anulado')
            ->first();

        if ($existe) {
            $this->dispatch('toast', ['icon' => 'warning', 'title' => 'Ya existe un registro para esta semana.']);
            return;
        }

        // 2. Lógica para COBRO financiero
        if ($estado === 'pagado') {

            // A. Usar el artículo seleccionado en el Combo Box
            if (!$this->articulo_seleccionado) {
                $this->addError('general', 'Debe seleccionar un tipo de insumo a cobrar.');
                return;
            }
            
            $articulo = ArticuloModel::find($this->articulo_seleccionado);
            
            if (!$articulo) {
                $this->addError('general', 'El artículo seleccionado no es válido.');
                return;
            }
            
            // ¡ELIMINAMOS LA BÚSQUEDA FORZADA POR NOMBRE!

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

                // Armar datos del recibo
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

    public function abrirCobroMultiple($id_estudiante)
    {
        $this->estudianteMultiple = EstudianteModel::find($id_estudiante);
        $this->fechasMultiple = [$this->fecha_semana]; 
        $this->montosPago = [];
        $this->showModalMultiple = true;
    }

    public function agregarFechaMultiple()
    {
        // Al darle clic al +, añade la semana siguiente automáticamente
        $ultimaFecha = end($this->fechasMultiple);
        $nuevaFecha = $ultimaFecha 
            ? Carbon::parse($ultimaFecha)->addDays(7)->format('Y-m-d') 
            : Carbon::now()->format('Y-m-d');
            
        $this->fechasMultiple[] = $nuevaFecha;
    }

    public function quitarFechaMultiple($indice)
    {
        unset($this->fechasMultiple[$indice]);
        $this->fechasMultiple = array_values($this->fechasMultiple); // Reindexar
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

        // Validar que el dinero ingresado alcance
        $totalIngresado = collect($this->montosPago)->map(fn($v) => (float)$v)->sum();
        
        if ($totalIngresado < ($totalCobrar - 0.05)) { // 0.05 de tolerancia por decimales
            $this->addError('multiple', "El monto ingresado ($totalIngresado Bs) no cubre el total de $totalCobrar Bs."); 
            return;
        }

        $cajaAbierta = CajaModel::where('id_usuario', Auth::id())->where('estado', 'abierta')->first();
        if (!$cajaAbierta) {
            $this->addError('multiple', 'No hay caja abierta.'); return;
        }

        // Verificación de duplicados (Igual a tu código)
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

            // --- MAGIA DEL PAGO DIVIDIDO EN TRANSACCIONES ---
            $metodosNombres = [];
            foreach ($this->montosPago as $id_metodo => $monto) {
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
                        $metodosNombres[] = $metodoModel->nombre;
                    }
                }
            }

            $metodosUsados = []; // <-- Array para guardar los nombres y montos

            foreach ($this->montosPago as $id_metodo => $monto) {
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
                        // Guardamos ej: "Efectivo: 200.00 Bs"
                        $metodosUsados[] = $metodoModel->nombre . ': ' . number_format((float)$monto, 2) . ' Bs';
                    }
                }
            }

            // Armar recibo
            $this->datosRecibo = [
                'nro_recibo' => str_pad($venta->id_venta, 6, '0', STR_PAD_LEFT),
                'estudiante' => $this->estudianteMultiple->nombre . ' ' . $this->estudianteMultiple->apellido,
                'ci' => $this->estudianteMultiple->ci,
                'fecha' => Carbon::now()->format('d/m/Y H:i'),
                'cajero' => Auth::user()->nombre ?? 'Administrador',
                'items' => $itemsRecibo,
                'total' => $totalCobrar,
                'ingresado' => $totalIngresado, 
                'cambio' => max(0, $totalIngresado - $totalCobrar),
                'metodos_pago' => implode(' | ', $metodosUsados) // <-- ¡ENVIAMOS EL TEXTO AL RECIBO!
            ];
        });

        $this->cerrarModalMultiple();
        $this->showModalExito = true;
    }

    public function render()
    {
        $search = mb_strtolower(trim($this->search));
        $fecha = $this->fecha_semana;

        // 1. Calculamos la semana completa en base a la fecha seleccionada
        $inicioSemana = Carbon::parse($fecha)->startOfWeek()->format('Y-m-d');
        $finSemana = Carbon::parse($fecha)->endOfWeek()->format('Y-m-d');

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
        // 1. Buscamos todos los IDs de ventas que están anuladas en el POS
        $ventasAnuladas = VentaModel::where('estado', 'anulada')->pluck('id_venta');
        
        // 2. Actualizamos los insumos vinculados a esas ventas que sigan diciendo "pagado"
        $cantidad = ControlInsumoModel::whereIn('id_venta', $ventasAnuladas)
            ->where('estado', '!=', 'anulado')
            ->update(['estado' => 'anulado']);
            
        // 3. Mostramos el mensaje de éxito
        $this->dispatch('toast', ['icon' => 'success', 'title' => "Se corrigieron $cantidad registros antiguos."]);
        
        // 4. Refrescamos el modal si está abierto
        if ($this->estudianteHistorial) {
            $this->abrirHistorial($this->estudianteHistorial->id_estudiante);
        }
    }

    public function abrirModalAbono($id_estudiante, $id_control_insumo = null)
    {
        $this->estudianteAbono = EstudianteModel::find($id_estudiante);
        
        if ($id_control_insumo) {
            // Ya existe un abono previo, vamos a completarlo
            $this->controlInsumoActivo = ControlInsumoModel::with('venta.pago')->find($id_control_insumo);
            $pago = $this->controlInsumoActivo->venta->pago;
            $this->deuda_actual = $pago->monto_total - $pago->monto_abonado;
        } else {
            // Es el primer abono de esta semana
            if (!$this->articulo_seleccionado) {
                $this->addError('general', 'Seleccione un tipo de Insumo arriba primero.');
                return;
            }
            $articulo = ArticuloModel::find($this->articulo_seleccionado);
            $this->controlInsumoActivo = null;
            $this->deuda_actual = $articulo->precio; // La deuda inicial es el total del artículo
        }

        $this->monto_a_abonar = ''; // Limpiamos el input
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
        // 1. Validaciones
        $monto = (float) $this->monto_a_abonar;
        if ($monto <= 0 || $monto > $this->deuda_actual) {
            $this->addError('abono', 'El monto debe ser mayor a 0 y no puede superar la deuda ('.$this->deuda_actual.' Bs).');
            return;
        }

        if (!$this->metodo_pago_seleccionado) {
            $this->addError('general', 'Debe seleccionar un método de pago.');
            $this->cerrarModalAbono();
            return;
        }

        $cajaAbierta = CajaModel::where('id_usuario', Auth::id())->where('estado', 'abierta')->first();
        if (!$cajaAbierta) {
            $this->addError('general', 'No tienes una caja abierta.');
            $this->cerrarModalAbono();
            return;
        }

        DB::transaction(function () use ($monto, $cajaAbierta) {
            
            // ESCENARIO A: Es el SEGUNDO abono (completando una deuda existente)
            if ($this->controlInsumoActivo) {
                $pago = $this->controlInsumoActivo->venta->pago;
                $nuevo_abonado = $pago->monto_abonado + $monto;

                // Registramos la entrada de dinero
                TransaccionModel::create([
                    'id_pago' => $pago->id_pago,
                    'id_metodo_pago' => $this->metodo_pago_seleccionado,
                    'id_caja' => $cajaAbierta->id_caja,
                    'monto' => $monto,
                    'fecha_transaccion' => Carbon::now()
                ]);

                // Verificamos si con este abono ya canceló todo
                if ($nuevo_abonado >= $pago->monto_total) {
                    $pago->update(['monto_abonado' => $nuevo_abonado, 'estado' => 'pagado']);
                    $this->controlInsumoActivo->update(['estado' => 'pagado']);
                } else {
                    $pago->update(['monto_abonado' => $nuevo_abonado]); // Sigue en parcial
                }

                $this->ultimoIdVenta = $this->controlInsumoActivo->id_venta;

            } 
            // ESCENARIO B: Es el PRIMER abono (Nace la venta y la deuda)
            else {
                $articulo = ArticuloModel::find($this->articulo_seleccionado);

                $venta = VentaModel::create([
                    'id_estudiante' => $this->estudianteAbono->id_estudiante,
                    'fecha_venta' => Carbon::now(),
                    'monto_total' => $articulo->precio,
                    'estado' => 'finalizada' // La venta es finalizada, el PAGO es parcial
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
                    'descripcion' => 'Insumo Semanal (' . $this->fecha_semana . ')',
                    'monto_total' => $articulo->precio,
                    'monto_abonado' => $monto,
                    'estado' => 'parcial' // ¡MAGIA! Nace como deuda
                ]);

                TransaccionModel::create([
                    'id_pago' => $pago->id_pago,
                    'id_metodo_pago' => $this->metodo_pago_seleccionado,
                    'id_caja' => $cajaAbierta->id_caja,
                    'monto' => $monto,
                    'fecha_transaccion' => Carbon::now()
                ]);

                ControlInsumoModel::create([
                    'id_estudiante' => $this->estudianteAbono->id_estudiante,
                    'fecha_semana' => $this->fecha_semana,
                    'estado' => 'pendiente', // Sigue pendiente en el listado
                    'id_venta' => $venta->id_venta
                ]);

                $this->ultimoIdVenta = $venta->id_venta;
            }

            // --- ARMAMOS EL RECIBO DE ABONO ---
            // Reutilizamos tu vista PDF, pero le pasamos el "monto ingresado" como el Abono
            $this->datosRecibo = [
                'nro_recibo' => str_pad($this->ultimoIdVenta, 6, '0', STR_PAD_LEFT),
                'estudiante' => $this->estudianteAbono->nombre . ' ' . $this->estudianteAbono->apellido,
                'ci' => $this->estudianteAbono->ci,
                'fecha' => Carbon::now()->format('d/m/Y H:i'),
                'cajero' => Auth::user()->nombre ?? 'Administrador',
                'items' => [
                    [
                        'cantidad' => 1,
                        'nombre' => 'ABONO - Insumo Semanal (' . Carbon::parse($this->fecha_semana)->format('d/m') . ')',
                        'precio' => $monto, // Reflejamos el abono en el PDF
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

    //Multiples métodos de pago
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
        
        // Verificamos que exista y que SOLO sea falta o licencia (no tocamos pagos aquí)
        if ($insumo && in_array($insumo->estado, ['falta', 'licencia'])) {
            $insumo->update(['estado' => 'anulado']);
            $this->dispatch('toast', ['icon' => 'success', 'title' => 'Acción deshecha. El alumno vuelve a estar sin registrar.']);
        }
    }
}
