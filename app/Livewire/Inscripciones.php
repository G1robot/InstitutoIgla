<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\EstudianteModel;
use App\Models\PlanModel;
use App\Models\InscripcionModel;
use App\Models\PagoModel;
use App\Models\TarifaModel;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\WithPagination;

class Inscripciones extends Component
{
    use WithPagination;
    public $search = '';
    public $showModal = false;

    public $estudiantes_encontrados = [];
    public $planes = [];

    public $id_estudiante;
    public $id_plan;
    public $gestion_inicio;
    public $anio_actual=1;
    public $estado = 'activo';

    public $searchEstudiante= '';

    public function mount()
    {
        $this->gestion_inicio = (int) date('Y');
        $this->planes = PlanModel::all();
    }

    public function updatedSearchEstudiante()
    {
        if(strlen($this->searchEstudiante) > 2){
            $this->estudiantes_encontrados = EstudianteModel::where('ci', 'like', '%'.$this->searchEstudiante.'%')
                ->orWhereRaw('LOWER(nombre) LIKE ?', ['%'.strtolower($this->searchEstudiante).'%'])
                ->orWhereRaw('LOWER(apellido) LIKE ?', ['%'.strtolower($this->searchEstudiante).'%'])
                ->take(5)
                ->get();
        } else {
            $this->estudiantes_encontrados = [];
        }
    }

    public function seleccionarEstudiante($id)
    {
        $estudiante = EstudianteModel::find($id);
        
        if ($estudiante) {
            // 2. Guardamos el ID para el registro
            $this->id_estudiante = $estudiante->id_estudiante;
            
            // 3. ¡LA MAGIA! Sobrescribimos lo que el usuario escribió con el nombre oficial
            $this->searchEstudiante = $estudiante->nombre . ' ' . $estudiante->apellido;
            
            // 4. Ocultamos la lista desplegable vaciando el array
            $this->estudiantes_encontrados = [];
        }
    }

    public function limpiarEstudiante()
    {
        $this->id_estudiante = '';
        $this->searchEstudiante = '';
        $this->estudiantes_encontrados = [];
    }

    public function openModal()
    {
        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->resetInput();
    }

    private function resetInput()
    {
        $this->id_estudiante = '';
        $this->id_plan = '';
        $this->gestion_inicio = (int) date('Y');
        $this->anio_actual = 1;
        $this->estado = 'activo';
        $this->searchEstudiante = '';
        $this->estudiantes_encontrados = [];
    }

    public function rules()
    {
        return [
            'id_estudiante' => 'required',
            'id_plan' => 'required',
            'gestion_inicio' => 'required|integer|min:2000',
            'anio_actual' => 'required|integer|min:1',
            'estado' => 'required|in:activo,retirado,egresado',
        ];
    }

    public function guardar()
    {
        $this->validate();

        DB::transaction(function () {
            // 1. Crear Inscripción
            $ins = InscripcionModel::create([
                'id_estudiante' => $this->id_estudiante,
                'id_plan' => $this->id_plan,
                'gestion_inicio' => $this->gestion_inicio,
                'anio_actual' => $this->anio_actual,
                'fecha_inscripcion' => Carbon::now(),
                'estado' => $this->estado,
            ]);

            // 2. Generar Pagos (Deuda)
            $this->generarPagos($ins);
        });

        $this->closeModal();
        
        $this->dispatch('toast', [
            'icon' => 'success', 
            'title' => 'Inscripción creada y pagos generados exitosamente'
        ]);
    }

    private function generarPagos(InscripcionModel $ins)
    {
        $plan = PlanModel::find($ins->id_plan);
        $totalMonths = (int) ($plan->duracion_meses ?? 0); // Ej: 36 meses
        
        // Calcular años completos para el PUA
        // Si dura 36 meses -> 3 años. Si dura 10 meses -> 1 año.
        $yearsCount = ceil($totalMonths / 12); 
        if($yearsCount < 1) $yearsCount = 1;

        // --- A. Generar Cobros de PUA (Anual) ---
        // Buscamos precio PUA actual en DB. Si no existe, error o default.
        // Ojo: Podrías buscar el PUA específico de cada año en el futuro loop, 
        // pero por ahora tomaremos el PUA de la gestión de inicio.
        $tarifaPUA = TarifaModel::where('codigo', 'PUA')
                        ->where('gestion', $ins->gestion_inicio)
                        ->first();
        
        // Si no hay PUA específico para ese año, buscamos el "permanente" (gestion null)
        if(!$tarifaPUA){
            $tarifaPUA = TarifaModel::where('codigo', 'PUA')->whereNull('gestion')->first();
        }

        $montoPUA = $tarifaPUA ? $tarifaPUA->monto : 0; // Si es 0, ojo, revisar tarifas

        for ($y = 0; $y < $yearsCount; $y++) {
            $yearCalculado = $ins->gestion_inicio + $y;
            
            // Creamos el pago tipo PUA
            // Nota: El PUA lo asociamos a la inscripción para saber que pertenece a este "contrato"
            // O podríamos asociarlo a la Tarifa, pero tu lógica es "pagos de la inscripción".
            // Vamos a usar la relación polimórfica apuntando a la INCRIPCION, 
            // pero en descripción aclaramos que es PUA.
            
            PagoModel::create([
                'origen_id' => $ins->id_inscripcion,
                'origen_type' => InscripcionModel::class, // 'App\Models\InscripcionModel'
                'id_estudiante' => $ins->id_estudiante,
                
                'fecha_vencimiento' => Carbon::create($yearCalculado, 2, 10), // Ej: Vence el 10 de Febrero
                'fecha_pago' => null,
                
                'descripcion' => "PUA Gestión $yearCalculado",
                'monto_total' => $montoPUA,
                'monto_abonado' => 0,
                'estado' => 'pendiente',
            ]);
        }

        // --- B. Generar Mensualidades o Anualidades del Plan ---
        
        if ($plan->tipo_pago === 'mensual') {
            // Generar N cuotas mensuales
            for ($i = 0; $i < $totalMonths; $i++) {
                $yearOffset = intdiv($i, 12);
                $mes = ($i % 12) + 1;
                $anio = $ins->gestion_inicio + $yearOffset;
                
                // Nombre del mes en español (opcional)
                $nombreMes = $this->getNombreMes($mes);

                PagoModel::create([
                    'origen_id' => $ins->id_inscripcion,
                    'origen_type' => InscripcionModel::class,
                    'id_estudiante' => $ins->id_estudiante,
                    
                    // Vence el 10 de cada mes
                    'fecha_vencimiento' => Carbon::create($anio, $mes, 10), 
                    'fecha_pago' => null,
                    
                    'descripcion' => "Cuota $nombreMes $anio",
                    'monto_total' => $plan->costo_mensual,
                    'monto_abonado' => 0,
                    'estado' => 'pendiente',
                ]);
            }

        } elseif ($plan->tipo_pago === 'anual') {
            // Generar N cuotas anuales (1 por año)
            for ($y = 0; $y < $yearsCount; $y++) {
                $anio = $ins->gestion_inicio + $y;

                PagoModel::create([
                    'origen_id' => $ins->id_inscripcion,
                    'origen_type' => InscripcionModel::class,
                    'id_estudiante' => $ins->id_estudiante,
                    
                    // Vence a inicio de gestión (ej: Marzo)
                    'fecha_vencimiento' => Carbon::create($anio, 3, 10), 
                    'fecha_pago' => null,
                    
                    'descripcion' => "Colegiatura Anual Gestión $anio",
                    'monto_total' => $plan->costo_anual,
                    'monto_abonado' => 0,
                    'estado' => 'pendiente',
                ]);
            }
        }
    }

    private function getNombreMes($numero) {
        $meses = [1=>'Enero',2=>'Febrero',3=>'Marzo',4=>'Abril',5=>'Mayo',6=>'Junio',
                  7=>'Julio',8=>'Agosto',9=>'Septiembre',10=>'Octubre',11=>'Noviembre',12=>'Diciembre'];
        return $meses[$numero] ?? 'Mes '.$numero;
    }

    public function render()
    {
        $search = mb_strtolower(trim($this->search));

        $inscripciones = InscripcionModel::with(['plan', 'estudiante'])
            ->whereHas('estudiante', function ($q) use ($search) {
                $q->whereRaw('LOWER(nombre) LIKE ?', ["%{$search}%"])
                  ->orWhereRaw('LOWER(apellido) LIKE ?', ["%{$search}%"])
                  ->orWhere('ci', 'like', "%{$search}%");
            })
            ->orWhereHas('plan', function ($q) use ($search) {
                $q->whereRaw('LOWER(nombre) LIKE ?', ["%{$search}%"]);
            })
            ->orWhereRaw('CAST(gestion_inicio AS TEXT) LIKE ?', ["%{$search}%"])
            ->orderBy('id_inscripcion', 'desc')
            ->paginate(10);

        return view('livewire.inscripciones', compact('inscripciones'));
    }

    public function clickBuscar() {
        $this->render();
    }

    public function cambiarEstado($id, $nuevoEstado)
    {
        $ins = InscripcionModel::find($id);
        if ($ins) {
            $ins->estado = $nuevoEstado;
            $ins->save();
            // Opcional: Podrías cancelar las deudas pendientes si se retira
        }
    }


    public function retirar($id)
    {
        $ins = InscripcionModel::find($id);
        if (! $ins) {
            session()->flash('error', 'Inscripción no encontrada.');
            return;
        }

        $ins->estado = 'retirado';
        $ins->save();

        session()->flash('success', 'Estado de la inscripción actualizado a retirado.');
    }



    public function activar($id)
    {
        $ins = InscripcionModel::find($id);
        if (! $ins) {
            session()->flash('error', 'Inscripción no encontrada.');
            return;
        }

        $ins->estado = 'activo';
        $ins->save();

        session()->flash('success', 'Estado de la inscripción actualizado a activo.');
    }

    public function egresado($id)
    {
        $ins = InscripcionModel::find($id);
        if (! $ins) {
            session()->flash('error', 'Inscripción no encontrada.');
            return;
        }

        $ins->estado = 'egresado';
        $ins->save();

        session()->flash('success', 'Estado de la inscripción actualizado a egresado.');
    }
    
}
