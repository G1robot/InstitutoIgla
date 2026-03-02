<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\EstudianteModel;
use App\Models\CategoriaModuloModel;
use App\Models\ModuloModel;
use App\Models\InscripcionModuloModel;
use App\Models\PagoModel;
use App\Models\TarifaModel;
use App\Models\EstudianteDerechoModel;
use App\Models\MetodoPagoModel;
use App\Models\TransaccionModel;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\CajaModel;
use Illuminate\Support\Facades\Auth;

class InscripcionModulo extends Component
{
    public $searchEstudiante = '';
    public $estudianteSeleccionado = null;
    public $estudiantesEncontrados = [];
    public $datosRecibo = null;

    public $id_categoria = null;
    public $modulos = [];
    public $searchModulo = '';
    public $carrito = [];
    public $total = 0;

    public $tienePUP = false;
    public $precioPUP = 0;

    public $metodosPago = []; 
    public $montosPago = [];
    public $totalIngresado = 0;

    public $showModalExito = false;

    public function mount()
    {
        $tarifa = TarifaModel::where('codigo', 'PUP')->latest('created_at')->first();
        $this->precioPUP = $tarifa ? $tarifa->monto : 0;

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
        
        $this->verificarPUP();
        
        $this->carrito = [];
        $this->total = 0;
    }

    public function verificarPUP()
    {
        if ($this->estudianteSeleccionado) {
            $this->tienePUP = EstudianteDerechoModel::where('id_estudiante', $this->estudianteSeleccionado->id_estudiante)
                ->where('derecho', 'PUP_PAGADO')
                ->exists();
        }
    }

    public function render()
    {
        $categorias = CategoriaModuloModel::all();
        
        $query = ModuloModel::query();
        
        if ($this->id_categoria) {
            $query->where('id_categoria_modulo', $this->id_categoria);
        }
        
        if ($this->searchModulo) {
            $query->where('nombre', 'like', '%' . $this->searchModulo . '%');
        }

        $this->modulos = $query->get();

        return view('livewire.inscripcion-modulo', compact('categorias'));
    }

    public function filtrarCategoria($id)
    {
        $this->id_categoria = $id;
        $this->searchModulo = '';
    }

    public function agregarAlCarrito($idModulo)
    {
        if (!$this->estudianteSeleccionado) {
            $this->addError('general', 'Seleccione un estudiante primero.');
            return;
        }

        if (!$this->tienePUP) {
             $this->agregarPUPAlCarrito();
        }

        $modulo = ModuloModel::find($idModulo);

        $yaInscrito = InscripcionModuloModel::where('id_estudiante', $this->estudianteSeleccionado->id_estudiante)
            ->where('id_modulo', $idModulo)
            ->where('estado', 'cursando')
            ->exists();

        if ($yaInscrito) {
             session()->flash('error', 'El estudiante ya está cursando este módulo.');
             return;
        }

        foreach ($this->carrito as $item) {
            if ($item['tipo'] == 'modulo' && $item['id'] == $idModulo) {
                return;
            }
        }

        $this->carrito[] = [
            'tipo' => 'modulo',
            'id' => $modulo->id_modulo,
            'nombre' => $modulo->nombre,
            'precio' => $modulo->costo,
            'objeto' => $modulo
        ];

        $this->calcularTotal();
    }

    public function agregarPUPAlCarrito()
    {
        foreach ($this->carrito as $item) {
            if ($item['tipo'] == 'pup') return;
        }

        $this->carrito[] = [
            'tipo' => 'pup',
            'id' => 'PUP',
            'nombre' => 'PAGO ÚNICO PERMANENTE (PUP)',
            'precio' => $this->precioPUP,
        ];
        
        session()->flash('warning', 'Se ha agregado el PUP al carrito automáticamente porque el estudiante no lo tiene.');
        $this->calcularTotal();
    }

    public function quitarDelCarrito($index)
    {
        unset($this->carrito[$index]);
        $this->carrito = array_values($this->carrito);
        $this->calcularTotal();
    }

    public function calcularTotal()
    {
        $this->total = 0;
        foreach ($this->carrito as $item) {
            $this->total += $item['precio'];
        }
    }

    public function finalizarInscripcion()
    {
        if (empty($this->carrito)) return;

        if ($this->totalIngresado < $this->total - 0.1) {
            $this->addError('pago', 'El monto ingresado no cubre el total a pagar.');
            return;
        }

        $cajaAbierta = CajaModel::where('id_usuario', Auth::id())
                            ->where('estado', 'abierta')
                            ->first();

        // 1. Creamos la variable afuera para atrapar el ID real
        $ultimoPagoId = null;

        // 2. Le pasamos &$ultimoPagoId a la transacción para poder modificarla desde adentro
        DB::transaction(function () use ($cajaAbierta, &$ultimoPagoId) {
            
            $bolsaDinero = [];
            foreach ($this->montosPago as $id => $monto) {
                if ((float)$monto > 0) $bolsaDinero[$id] = (float)$monto;
            }

            foreach ($this->carrito as $item) {
                $pagoCreado = null;

                if ($item['tipo'] == 'pup') {
                    $pagoCreado = PagoModel::create([
                        'origen_id' => 0, 
                        'origen_type' => 'App\Models\TarifaModel',
                        'id_estudiante' => $this->estudianteSeleccionado->id_estudiante,
                        'fecha_vencimiento' => Carbon::now(),
                        'fecha_pago' => Carbon::now(),
                        'descripcion' => 'Pago Único Permanente (PUP)',
                        'monto_total' => $item['precio'],
                        'monto_abonado' => $item['precio'],
                        'estado' => 'pagado'
                    ]);

                    EstudianteDerechoModel::create([
                        'id_estudiante' => $this->estudianteSeleccionado->id_estudiante,
                        'derecho' => 'PUP_PAGADO',
                        'fecha_adquisicion' => Carbon::now()
                    ]);
                    $this->tienePUP = true;
                }

                if ($item['tipo'] == 'modulo') {
                    $inscripcion = InscripcionModuloModel::create([
                        'id_estudiante' => $this->estudianteSeleccionado->id_estudiante,
                        'id_modulo' => $item['id'],
                        'fecha_inscripcion' => Carbon::now(),
                        'estado' => 'cursando',
                        'costo_al_momento' => $item['precio']
                    ]);

                    $pagoCreado = PagoModel::create([
                        'origen_id' => $inscripcion->id_inscripcion_modulo,
                        'origen_type' => InscripcionModuloModel::class,
                        'id_estudiante' => $this->estudianteSeleccionado->id_estudiante,
                        'fecha_vencimiento' => Carbon::now(),
                        'fecha_pago' => Carbon::now(),
                        'descripcion' => 'Módulo: ' . $item['nombre'],
                        'monto_total' => $item['precio'],
                        'monto_abonado' => $item['precio'],
                        'estado' => 'pagado'
                    ]);
                }

                // Si se generó un pago en la base de datos...
                if ($pagoCreado) {
                    
                    // 3. ¡ATRAPAMOS EL ID AQUÍ!
                    $ultimoPagoId = $pagoCreado->id_pago; 

                    $costoItem = $item['precio'];
                    
                    foreach ($bolsaDinero as $idMetodo => &$saldoDisponible) {
                        if ($costoItem <= 0) break; 
                        if ($saldoDisponible <= 0) continue; 

                        $montoUsar = min($costoItem, $saldoDisponible);

                        TransaccionModel::create([
                            'id_pago' => $pagoCreado->id_pago,
                            'id_metodo_pago' => $idMetodo,
                            'id_caja' => $cajaAbierta->id_caja,
                            'monto' => $montoUsar,
                            'fecha_transaccion' => Carbon::now()
                        ]);

                        $saldoDisponible -= $montoUsar;
                        $costoItem -= $montoUsar;
                    }
                }
            }
        });

        // 4. USAMOS EL ID REAL PARA EL RECIBO ($ultimoPagoId)
        $this->datosRecibo = [
            'nro_recibo' => str_pad($ultimoPagoId ?? 0, 6, '0', STR_PAD_LEFT),
            'estudiante' => $this->estudianteSeleccionado->nombre . ' ' . $this->estudianteSeleccionado->apellido,
            'ci' => $this->estudianteSeleccionado->ci,
            'fecha' => \Carbon\Carbon::now()->format('d/m/Y H:i'),
            'cajero' => Auth::user()->nombre ?? 'Administrador',
            'items' => $this->carrito,
            'total' => $this->total,
            'ingresado' => $this->totalIngresado,
            'cambio' => max(0, $this->totalIngresado - $this->total),
        ];
        
        $this->carrito = [];
        $this->total = 0;
        $this->resetMontosPago();
        $this->showModalExito = true;
        $this->verificarPUP();
    }
    
    public function cerrarModalExito() {
        $this->showModalExito = false;
    }
}
