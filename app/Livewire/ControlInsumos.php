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
        // Iniciamos el array con la fecha que esté seleccionada en el filtro
        $this->fechasMultiple = [$this->fecha_semana]; 
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
    }

    public function procesarCobroMultiple()
    {
        // 1. Validaciones
        if(empty($this->fechasMultiple)) {
            $this->addError('multiple', 'Debe agregar al menos una fecha.'); return;
        }
        if (!$this->articulo_seleccionado || !$this->metodo_pago_seleccionado) {
            $this->addError('multiple', 'Seleccione un tipo de Insumo y Método de pago en la barra superior.'); return;
        }

        $articulo = ArticuloModel::find($this->articulo_seleccionado);
        $cajaAbierta = CajaModel::where('id_usuario', Auth::id())->where('estado', 'abierta')->first();

        if (!$cajaAbierta) {
            $this->addError('multiple', 'No hay caja abierta.'); return;
        }

        // 2. Verificar que ninguna de las fechas elegidas ya esté pagada/registrada
        $semanasSeleccionadas = []; // Para evitar duplicados en el mismo modal

        foreach($this->fechasMultiple as $fecha) {
            $inicioSemana = Carbon::parse($fecha)->startOfWeek()->format('Y-m-d');
            $finSemana = Carbon::parse($fecha)->endOfWeek()->format('Y-m-d');

            // A. Evitar que pongan dos días de la misma semana en el modal
            if (in_array($inicioSemana, $semanasSeleccionadas)) {
                $this->addError('multiple', 'Has añadido dos fechas que pertenecen a la misma semana. Solo se cobra 1 insumo por semana.');
                return;
            }
            $semanasSeleccionadas[] = $inicioSemana;

            // B. Revisar si la base de datos ya tiene un pago en esa semana
            $existe = ControlInsumoModel::where('id_estudiante', $this->estudianteMultiple->id_estudiante)
                        ->whereBetween('fecha_semana', [$inicioSemana, $finSemana])->first();
                        
            if($existe) {
                $this->addError('multiple', "El alumno ya pagó la semana del " . Carbon::parse($inicioSemana)->format('d/m/Y'));
                return;
            }
        }

        // 3. Ejecutar la transacción maestra
        DB::transaction(function () use ($articulo, $cajaAbierta) {
            $cantidadSemanas = count($this->fechasMultiple);
            $totalCobrar = $articulo->precio * $cantidadSemanas;

            // A. Venta global
            $venta = VentaModel::create([
                'id_estudiante' => $this->estudianteMultiple->id_estudiante,
                'fecha_venta' => Carbon::now(),
                'monto_total' => $totalCobrar,
                'estado' => 'finalizada'
            ]);

            $this->ultimoIdVenta = $venta->id_venta;
            $itemsRecibo = [];

            // B. Recorrer cada fecha y registrar su detalle y su control de insumo
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

                // Armamos la línea para el PDF, asegurando que diga "Insumo Semanal (Fecha)"
                $itemsRecibo[] = [
                    'cantidad' => 1,
                    'nombre' => $articulo->nombre . ' (Sem: ' . Carbon::parse($fecha)->format('d/m/y') . ')',
                    'precio' => $articulo->precio,
                    'subtotal' => $articulo->precio
                ];
            }

            // C. El Pago y la Transacción para la contadora
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

            TransaccionModel::create([
                'id_pago' => $pago->id_pago,
                'id_metodo_pago' => $this->metodo_pago_seleccionado,
                'id_caja' => $cajaAbierta->id_caja,
                'monto' => $totalCobrar,
                'fecha_transaccion' => Carbon::now()
            ]);

            // D. Mandar datos al recibo
            $this->datosRecibo = [
                'nro_recibo' => str_pad($venta->id_venta, 6, '0', STR_PAD_LEFT),
                'estudiante' => $this->estudianteMultiple->nombre . ' ' . $this->estudianteMultiple->apellido,
                'ci' => $this->estudianteMultiple->ci,
                'fecha' => Carbon::now()->format('d/m/Y H:i'),
                'cajero' => Auth::user()->nombre ?? 'Administrador',
                'items' => $itemsRecibo, // Aquí va el array con todas las fechas
                'total' => $totalCobrar,
                'ingresado' => $totalCobrar,
                'cambio' => 0,
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
                // 2. Buscamos registros en TODA esa semana, no solo en ese día
                $query->whereBetween('fecha_semana', [$inicioSemana, $finSemana]);
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
