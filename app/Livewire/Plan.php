<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\PlanModel;

class Plan extends Component
{
    public $search = '';
    public $showModal = false;

    public $nombre = '';
    public $duracion_anios = 0;
    public $duracion_meses = null;
    public $costo_anual = null;
    public $costo_mensual = null;
    public $tipo_pago = '';
    public $plan_id = '';

    public function rules(){
        $rules = [
            'nombre' => 'required|string|max:255',
            'duracion_anios' => 'nullable|integer|min:0',
            'duracion_meses' => 'nullable|integer|min:0',
            'costo_anual' => 'nullable|numeric|min:0',
            'costo_mensual' => 'nullable|numeric|min:0',
            'tipo_pago' => 'required|in:mensual,anual',
        ];
        return $rules;
    }

    public function render()
    {
        $planes = PlanModel::where('nombre', 'like', '%'.$this->search.'%')
        ->orderBy('id_plan', 'asc')
        ->get();
        return view('livewire.plan',compact('planes'));
    }

    public function clickBuscar(){

    }

    public function openModal()
    {
        $this->showModal = true;
    }
    public function closeModal()
    {
        $this->showModal = false;
       $this->limpiarDatos();
    }
    public function limpiarDatos(){
        $this->nombre='';
        $this->duracion_anios=0;
        $this->duracion_meses=null;
        $this->costo_anual=null;
        $this->costo_mensual=null;
        $this->tipo_pago='';
        $this->plan_id='';
    }
    public function enviarClick()
    {
        $this->validate();

        if ($this->plan_id) {
            $plan = PlanModel::find($this->plan_id);
            $plan->nombre = $this->nombre;
            $plan->duracion_anios = $this->duracion_anios;
            $plan->duracion_meses = $this->duracion_meses !== null ? $this->duracion_meses : ($this->duracion_anios !== null ? $this->duracion_anios * 12 : null);
            $plan->costo_anual = $this->costo_anual;
            $plan->costo_mensual = $this->costo_mensual;
            $plan->tipo_pago = $this->tipo_pago;
            $plan->save();
        } else {
            PlanModel::create([
                'nombre' => $this->nombre,
                'duracion_anios' => $this->duracion_anios,
                'duracion_meses' => $this->duracion_meses !== null ? $this->duracion_meses : ($this->duracion_anios !== null ? $this->duracion_anios * 12 : null),
                'costo_anual' => $this->costo_anual,
                'costo_mensual' => $this->costo_mensual,
                'tipo_pago' => $this->tipo_pago,
            ]);
        }
        $this->limpiarDatos();
        $this->closeModal();

        $this->dispatch('toast', [
            'icon' => 'success', 
            'title' => 'Plan guardado exitosamente'
        ]);
    }
    
    public function editar($id){
        $plan = PlanModel::find($id);
        $this->plan_id = $plan->id_plan;
        $this->nombre = $plan->nombre;
        $this->duracion_anios = $plan->duracion_anios;
        $this->duracion_meses = $plan->duracion_meses;
        $this->costo_anual = $plan->costo_anual;
        $this->costo_mensual = $plan->costo_mensual;
        $this->tipo_pago = $plan->tipo_pago;
        $this->openModal();
    }
}
