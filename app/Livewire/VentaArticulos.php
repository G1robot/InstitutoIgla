<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\EstudianteModel;
use App\Models\ArticuloModel;
use App\Models\CategoriaArticuloModel;
use App\Models\VentaModel;
use App\Models\DetalleVentaModel;
use App\Models\PagoModel;
use App\Models\MetodoPagoModel;
use App\Models\TransaccionModel;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\CajaModel;
use Illuminate\Support\Facades\Auth;


class VentaArticulos extends Component
{
    public $searchEstudiante = '';
    public $estudianteSeleccionado = null;
    public $estudiantesEncontrados = [];

    public $datosRecibo = null;

    // Catálogo y Carrito
    public $id_categoria = null;
    public $searchArticulo = '';
    public $articulos = [];
    public $carrito = [];
    public $total = 0;

    // Pago (Lógica de Transacciones)
    public $metodosPago = [];
    public $montosPago = []; // [id_metodo => monto]
    public $totalIngresado = 0;

    // Feedback
    public $showModalExito = false;
    public $ultimoIdVenta = null;

    public $showModalHistorial = false; // Control del nuevo modal
    public $ventasDelDia = []; // Lista de ve

    public function mount()
    {
        $this->metodosPago = MetodoPagoModel::where('activo', true)->get();
        $this->resetMontosPago();
    }

    public function resetMontosPago()
    {
        $this->montosPago = [];
        foreach ($this->metodosPago as $m) {
            $this->montosPago[$m->id_metodo_pago] = 0;
        }
        $this->totalIngresado = 0;
    }

    // --- BÚSQUEDA DE ESTUDIANTE ---
    public function updatedSearchEstudiante()
    {
        if (strlen($this->searchEstudiante) > 2) {
            $this->estudiantesEncontrados = EstudianteModel::where('ci', 'like', '%' . $this->searchEstudiante . '%')
                ->orWhereRaw('LOWER(nombre) LIKE ?', ['%' . strtolower($this->searchEstudiante) . '%'])
                ->orWhereRaw('LOWER(apellido) LIKE ?', ['%' . strtolower($this->searchEstudiante) . '%'])
                ->take(5)
                ->get();
        } else {
            $this->estudiantesEncontrados = [];
        }
    }

    public function seleccionarEstudiante($id)
    {
        $this->estudianteSeleccionado = EstudianteModel::find($id);
        $this->searchEstudiante = '';
        $this->estudiantesEncontrados = [];
    }

    public function render()
    {
        $categorias = CategoriaArticuloModel::orderBy('nombre')->get();
        
        $query = ArticuloModel::query();
        
        if ($this->id_categoria) {
            $query->where('id_categoria_articulo', $this->id_categoria);
        }
        if ($this->searchArticulo) {
            $query->where('nombre', 'like', '%' . $this->searchArticulo . '%');
        }

        // Importante: No mostrar items sin stock (excepto servicios)
        // Lógica: (stock > 0 OR stock is null)
        $query->where(function($q) {
            $q->where('stock', '>', 0)->orWhereNull('stock');
        });

        $this->articulos = $query->take(20)->get();

        return view('livewire.venta-articulos', compact('categorias'));
    }

    public function filtrarCategoria($id) {
        $this->id_categoria = $id;
        $this->searchArticulo = '';
    }

    // --- CARRITO ---
    public function agregarAlCarrito($idArticulo)
    {
        $articulo = ArticuloModel::find($idArticulo);

        // Verificar si ya está en carrito
        if (isset($this->carrito[$idArticulo])) {
            // Verificar Stock
            if (!is_null($articulo->stock) && $this->carrito[$idArticulo]['cantidad'] + 1 > $articulo->stock) {
                $this->dispatchBrowserEvent('alert', ['message' => 'Stock insuficiente']);
                return;
            }
            $this->carrito[$idArticulo]['cantidad']++;
            $this->carrito[$idArticulo]['subtotal'] = $this->carrito[$idArticulo]['cantidad'] * $this->carrito[$idArticulo]['precio'];
        } else {
            // Nuevo item
            $this->carrito[$idArticulo] = [
                'id' => $articulo->id_articulo,
                'nombre' => $articulo->nombre,
                'precio' => $articulo->precio,
                'cantidad' => 1,
                'subtotal' => $articulo->precio,
                'es_servicio' => is_null($articulo->stock)
            ];
        }
        $this->calcularTotal();
    }

    public function restarDelCarrito($idArticulo)
    {
        if (isset($this->carrito[$idArticulo])) {
            if ($this->carrito[$idArticulo]['cantidad'] > 1) {
                $this->carrito[$idArticulo]['cantidad']--;
                $this->carrito[$idArticulo]['subtotal'] = $this->carrito[$idArticulo]['cantidad'] * $this->carrito[$idArticulo]['precio'];
            } else {
                unset($this->carrito[$idArticulo]);
            }
        }
        $this->calcularTotal();
    }

    public function quitarItem($idArticulo) {
        unset($this->carrito[$idArticulo]);
        $this->calcularTotal();
    }

    public function calcularTotal()
    {
        $this->total = array_sum(array_column($this->carrito, 'subtotal'));
    }

    // --- PAGO ---
    public function updatedMontosPago()
    {
        $this->totalIngresado = 0;
        foreach($this->montosPago as $m) {
            $this->totalIngresado += (float) ($m === '' ? 0 : $m);
        }
    }

    public function llenarSaldo($idMetodo)
    {
        $this->resetMontosPago();
        $this->montosPago[$idMetodo] = $this->total;
        $this->updatedMontosPago();
    }

    // --- FINALIZAR VENTA (CORE) ---
    public function realizarVenta()
    {
        // 1. Validaciones
        if (!$this->estudianteSeleccionado) {
            $this->addError('general', 'Debe seleccionar un estudiante.');
            return;
        }
        if (empty($this->carrito)) {
            $this->addError('general', 'El carrito está vacío.');
            return;
        }
        
        // Validar montos (margen de error 0.1)
        if ($this->totalIngresado < $this->total - 0.1) {
            $this->addError('pago', 'El monto ingresado no cubre el total de la venta.');
            return;
        }

        $cajaAbierta = CajaModel::where('id_usuario', Auth::id())
                            ->where('estado', 'abierta')
                            ->first();

        if (!$cajaAbierta) {
            $this->addError('general', 'Error de sesión: No hay caja abierta.');
            return;
        }

        DB::transaction(function () use ($cajaAbierta){
            // A. Crear Venta
            $venta = VentaModel::create([
                'id_estudiante' => $this->estudianteSeleccionado->id_estudiante,
                'fecha_venta' => Carbon::now(),
                'monto_total' => $this->total,
                'estado' => 'finalizada'
            ]);

            $this->ultimoIdVenta = $venta->id_venta;

            // B. Detalle de Venta y Descuento de Stock
            foreach ($this->carrito as $item) {
                DetalleVentaModel::create([
                    'id_venta' => $venta->id_venta,
                    'id_articulo' => $item['id'],
                    'cantidad' => $item['cantidad'],
                    'precio_unitario' => $item['precio'],
                    'subtotal' => $item['subtotal']
                ]);

                // Descontar Stock si no es servicio
                if (!$item['es_servicio']) {
                    ArticuloModel::where('id_articulo', $item['id'])->decrement('stock', $item['cantidad']);
                }
            }

            // C. Crear el Pago (Polimórfico)
            // IMPORTANTE: Este pago agrupa todo el dinero de la venta
            $pago = PagoModel::create([
                'origen_id' => $venta->id_venta,
                'origen_type' => VentaModel::class, // App\Models\VentaModel
                'id_estudiante' => $this->estudianteSeleccionado->id_estudiante,
                'fecha_vencimiento' => Carbon::now(),
                'fecha_pago' => Carbon::now(),
                'descripcion' => 'Venta de Artículos #' . $venta->id_venta,
                'monto_total' => $this->total,
                'monto_abonado' => $this->total, // Asumimos pago completo en POS
                'estado' => 'pagado'
            ]);

            // D. Registrar las Transacciones (El dinero real para el Arqueo)
            foreach ($this->montosPago as $idMetodo => $monto) {
                $montoReal = (float) $monto;
                if ($montoReal > 0) {
                    TransaccionModel::create([
                        'id_pago' => $pago->id_pago,
                        'id_metodo_pago' => $idMetodo,
                        'id_caja' => $cajaAbierta->id_caja,
                        'monto' => $montoReal,
                        'fecha_transaccion' => Carbon::now()
                    ]);
                }
            }
        });

        $this->datosRecibo = [
            'nro_recibo' => str_pad($venta->id_venta ?? $this->ultimoIdVenta, 6, '0', STR_PAD_LEFT),
            'estudiante' => $this->estudianteSeleccionado->nombre . ' ' . $this->estudianteSeleccionado->apellido,
            'ci' => $this->estudianteSeleccionado->ci,
            'fecha' => \Carbon\Carbon::now()->format('d/m/Y H:i'),
            'cajero' => Auth::user()->nombre ?? 'Administrador',
            'items' => $this->carrito,
            'total' => $this->total,
            'ingresado' => $this->totalIngresado,
            'cambio' => max(0, $this->totalIngresado - $this->total),
        ];


        // Resetear todo
        $this->carrito = [];
        $this->total = 0;
        $this->resetMontosPago();
        $this->showModalExito = true;
    }

    public function cerrarModalExito() {
        $this->showModalExito = false;
        $this->estudianteSeleccionado = null; // Opcional: limpiar estudiante
    }

    ///ANULAR VEnTa
    public function abrirHistorial()
    {
        $this->cargarVentasDelDia();
        $this->showModalHistorial = true;
    }

    public function cerrarHistorial()
    {
        $this->showModalHistorial = false;
    }

    public function cargarVentasDelDia()
    {
        // Traemos ventas de HOY, con sus relaciones
        $this->ventasDelDia = VentaModel::with(['estudiante', 'pago'])
            ->whereDate('fecha_venta', Carbon::today())
            ->orderBy('id_venta', 'desc') // Las más recientes primero
            ->get();
    }

    public function anularVenta($idVenta)
    {
        DB::transaction(function () use ($idVenta) {
            $venta = VentaModel::with('detalles')->find($idVenta);

            if (!$venta || $venta->estado === 'anulada') {
                return;
            }

            // 1. Restaurar Stock (Solo de lo que no es servicio)
            foreach ($venta->detalles as $detalle) {
                // Verificamos si el artículo maneja stock (no es null)
                $articulo = ArticuloModel::find($detalle->id_articulo);
                if ($articulo && !is_null($articulo->stock)) {
                    $articulo->increment('stock', $detalle->cantidad);
                }
            }

            // 2. Anular la Venta
            $venta->update(['estado' => 'anulada']);

            // 3. Anular el Pago asociado (Esto evitará que sume en el Arqueo)
            if ($venta->pago) {
                $venta->pago->update(['estado' => 'anulado']);
                // Opcional: Podrías borrar las transacciones si prefieres que desaparezcan del todo
                // $venta->pago->transacciones()->delete(); 
                // Pero mejor dejarlas y filtrar por estado 'anulado' en el arqueo para auditoría.
            }
        });

        $this->cargarVentasDelDia(); // Refrescar lista
        session()->flash('mensaje_historial', "Venta #$idVenta anulada y stock restaurado.");
    }
}
