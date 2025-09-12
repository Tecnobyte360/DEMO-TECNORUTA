<?php

namespace App\Livewire\CuentasContables;

use Livewire\Component;
use Illuminate\Validation\Rule;
use App\Models\CuentasContables\PlanCuentas as Cuenta; // 👈 tu modelo con alias

class PlanCuentas extends Component
{
    /* ====== Filtros/árbol ====== */
    public string $q = '';
    public ?int $nivelMax = 10;
    public string $naturaleza = 'TODAS';
    public array $expandidos = [];
    public ?int $selectedId = null;

    /* ====== Panel ficha (solo display) ====== */
    public ?string $f_codigo = null;
    public ?string $f_nombre = null;
    public ?string $f_moneda = null;
    public bool $f_requiere_tercero = false;
    public int  $f_nivel = 1;
    public bool $f_cuenta_activa = true;
    public bool $f_titulo = false;

    /* ====== Modal crear/editar ====== */
    public bool $showModal = false;
    public ?int $editingId = null;

    // Campos del formulario
    public ?int $padre_id = null;
    public string $codigo = '';
    public string $nombre = '';
    public string $naturaleza_form = 'ACTIVOS';
    public bool $cuenta_activa = true;
    public bool $titulo = false;
    public string $moneda = 'Pesos Colombianos';
    public bool $requiere_tercero = false;
    public bool $confidencial = false;
    public ?int $nivel_confidencial = null;
    public ?string $clase_cuenta = null;
    public bool $cuenta_monetaria = false;
    public bool $cuenta_asociada = false;
    public bool $revalua_indice = false;
    public bool $bloquear_contab_manual = false;
    public bool $relevante_flujo_caja = false;
    public bool $relevante_costos = false;
    public ?string $dimension1 = null;
    public ?string $dimension2 = null;
    public ?string $dimension3 = null;
    public ?string $dimension4 = null;
    public float $saldo = 0;

    public function mount(): void
    {
        // abrir raíces por defecto
        $this->expandidos = Cuenta::whereNull('padre_id')->pluck('id')->all();
    }

    /* ========== Validación ========== */
    protected function rules(): array
    {
        return [
            'padre_id' => ['nullable','integer','exists:plan_cuentas,id'],
            'codigo' => ['required','string','max:30', Rule::unique('plan_cuentas','codigo')->ignore($this->editingId)],
            'nombre' => ['required','string','max:255'],
            'naturaleza_form' => ['required','string','max:40'],
            'cuenta_activa' => ['boolean'],
            'titulo' => ['boolean'],
            'moneda' => ['required','string','max:50'],
            'requiere_tercero' => ['boolean'],
            'confidencial' => ['boolean'],
            'nivel_confidencial' => ['nullable','integer','between:0,10'],
            'clase_cuenta' => ['nullable','string','max:40'],
            'cuenta_monetaria' => ['boolean'],
            'cuenta_asociada' => ['boolean'],
            'revalua_indice' => ['boolean'],
            'bloquear_contab_manual' => ['boolean'],
            'relevante_flujo_caja' => ['boolean'],
            'relevante_costos' => ['boolean'],
            'dimension1' => ['nullable','string','max:255'],
            'dimension2' => ['nullable','string','max:255'],
            'dimension3' => ['nullable','string','max:255'],
            'dimension4' => ['nullable','string','max:255'],
            'saldo' => ['numeric','min:0'],
        ];
    }

    /* ========== Árbol & ficha ========== */
    public function updatedSelectedId(): void
    {
        $this->cargarFicha($this->selectedId);
    }

    protected function cargarFicha(?int $id): void
    {
        if (!$id) { $this->resetFicha(); return; }
        $c = Cuenta::find($id);
        if (!$c) { $this->resetFicha(); return; }

        $this->f_codigo = $c->codigo;
        $this->f_nombre = $c->nombre;
        $this->f_moneda = $c->moneda;
        $this->f_requiere_tercero = (bool) $c->requiere_tercero;
        $this->f_nivel = (int) $c->nivel;
        $this->f_cuenta_activa = (bool) $c->cuenta_activa;
        $this->f_titulo = (bool) $c->titulo;
    }

    protected function resetFicha(): void
    {
        $this->f_codigo = $this->f_nombre = $this->f_moneda = null;
        $this->f_requiere_tercero = false;
        $this->f_nivel = 1;
        $this->f_cuenta_activa = true;
        $this->f_titulo = false;
    }

    public function setNaturaleza(string $nat): void
    {
        $this->naturaleza = $nat;
        $this->expandidos = Cuenta::whereNull('padre_id')
            ->when($nat !== 'TODAS', fn($q) => $q->where('naturaleza', strtoupper($nat)))
            ->pluck('id')->all();
    }

    public function toggle(int $id): void
    {
        if (in_array($id, $this->expandidos)) {
            $this->expandidos = array_values(array_diff($this->expandidos, [$id]));
        } else {
            $this->expandidos[] = $id;
        }
    }

    public function expandAll(): void
    {
        $this->expandidos = Cuenta::when($this->naturaleza !== 'TODAS', fn($q) => $q->where('naturaleza', $this->naturaleza))
            ->pluck('id')->all();
    }

    public function collapseAll(): void
    {
        $this->expandidos = Cuenta::whereNull('padre_id')
            ->when($this->naturaleza !== 'TODAS', fn($q) => $q->where('naturaleza', $this->naturaleza))
            ->pluck('id')->all();
    }

    protected function buildFlatTree()
    {
        $base = Cuenta::query()
            ->when($this->naturaleza !== 'TODAS', fn($q) => $q->where('naturaleza', $this->naturaleza))
            ->when($this->q !== '', function ($q) {
                $t = trim($this->q);
                $q->where(fn($qq) => $qq->where('codigo','like',"%{$t}%")->orWhere('nombre','like',"%{$t}%"));
            })
            ->orderBy('codigo')
            ->get()
            ->groupBy('padre_id');

        $flat = [];
        $walk = function ($padreId, $nivel) use (&$walk, &$flat, $base) {
            foreach (($base[$padreId] ?? collect()) as $nodo) {
                if ($this->nivelMax !== null && $nivel > $this->nivelMax) continue;
                $nodo->nivel_visual = $nivel; // UI
                $flat[] = $nodo;
                if (in_array($nodo->id, $this->expandidos)) $walk($nodo->id, $nivel + 1);
            }
        };
        $walk(null, 1);
        return collect($flat);
    }

    public function select(int $id): void
    {
        $this->selectedId = $id;
        $this->cargarFicha($id);
    }

    /* ========== Crear/Editar ========== */
    public function openCreate(?int $padreId = null): void
    {
        $this->resetForm();
        $this->padre_id = $padreId;
        $this->showModal = true;
    }

    public function openEdit(int $id): void
    {
        $this->resetForm();
        $c = Cuenta::findOrFail($id);

        $this->editingId = $c->id;
        $this->padre_id = $c->padre_id;
        $this->codigo = $c->codigo;
        $this->nombre = $c->nombre;
        $this->naturaleza_form = $c->naturaleza;
        $this->cuenta_activa = (bool)$c->cuenta_activa;
        $this->titulo = (bool)$c->titulo;
        $this->moneda = $c->moneda;
        $this->requiere_tercero = (bool)$c->requiere_tercero;
        $this->confidencial = (bool)$c->confidencial;
        $this->nivel_confidencial = $c->nivel_confidencial;
        $this->clase_cuenta = $c->clase_cuenta;
        $this->cuenta_monetaria = (bool)$c->cuenta_monetaria;
        $this->cuenta_asociada = (bool)$c->cuenta_asociada;
        $this->revalua_indice = (bool)$c->revalua_indice;
        $this->bloquear_contab_manual = (bool)$c->bloquear_contab_manual;
        $this->relevante_flujo_caja = (bool)$c->relevante_flujo_caja;
        $this->relevante_costos = (bool)$c->relevante_costos;
        $this->dimension1 = $c->dimension1;
        $this->dimension2 = $c->dimension2;
        $this->dimension3 = $c->dimension3;
        $this->dimension4 = $c->dimension4;
        $this->saldo = (float)$c->saldo;

        $this->showModal = true;
    }

    /** IDs de todos los descendientes (evitar ciclos) */
    protected function descendantIdsOf(int $id): array
    {
        $ids = [];
        $hijos = Cuenta::where('padre_id', $id)->pluck('id')->all();
        foreach ($hijos as $h) {
            $ids[] = $h;
            $ids = array_merge($ids, $this->descendantIdsOf($h));
        }
        return $ids;
    }

    public function save(): void
    {
        $this->validate();

        // Validación anti-ciclos
        if ($this->editingId) {
            if ($this->padre_id === $this->editingId) {
                $this->addError('padre_id', 'La cuenta no puede ser su propio padre.');
                return;
            }
            if ($this->padre_id) {
                $desc = $this->descendantIdsOf($this->editingId);
                if (in_array($this->padre_id, $desc)) {
                    $this->addError('padre_id', 'No puedes asignar como padre a un descendiente.');
                    return;
                }
            }
        }

        // Nivel calculado
        $nivel = 1;
        if ($this->padre_id) {
            $padre = Cuenta::findOrFail($this->padre_id);
            $nivel = (int)$padre->nivel + 1;
        }

        $data = [
            'padre_id' => $this->padre_id,
            'codigo' => trim($this->codigo),
            'nombre' => $this->nombre,
            'nivel' => $nivel,
            'naturaleza' => strtoupper($this->naturaleza_form),
            'cuenta_activa' => $this->cuenta_activa,
            'titulo' => $this->titulo,
            'moneda' => $this->moneda,
            'requiere_tercero' => $this->requiere_tercero,
            'confidencial' => $this->confidencial,
            'nivel_confidencial' => $this->nivel_confidencial,
            'clase_cuenta' => $this->clase_cuenta,
            'cuenta_monetaria' => $this->cuenta_monetaria,
            'cuenta_asociada' => $this->cuenta_asociada,
            'revalua_indice' => $this->revalua_indice,
            'bloquear_contab_manual' => $this->bloquear_contab_manual,
            'relevante_flujo_caja' => $this->relevante_flujo_caja,
            'relevante_costos' => $this->relevante_costos,
            'dimension1' => $this->dimension1,
            'dimension2' => $this->dimension2,
            'dimension3' => $this->dimension3,
            'dimension4' => $this->dimension4,
            'saldo' => $this->saldo,
        ];

        if ($this->editingId) {
            Cuenta::findOrFail($this->editingId)->update($data);
        } else {
            Cuenta::create($data);
        }

        $this->showModal = false;
        $this->resetForm();
        // recargar ficha si la cuenta editada estaba seleccionada
        if ($this->selectedId) $this->cargarFicha($this->selectedId);
        $this->dispatch('toast', title: 'Guardado', message: 'La cuenta se guardó correctamente.');
    }

    public function resetForm(): void
    {
        $this->reset([
            'editingId','padre_id','codigo','nombre','naturaleza_form','cuenta_activa','titulo','moneda',
            'requiere_tercero','confidencial','nivel_confidencial','clase_cuenta','cuenta_monetaria','cuenta_asociada',
            'revalua_indice','bloquear_contab_manual','relevante_flujo_caja','relevante_costos',
            'dimension1','dimension2','dimension3','dimension4','saldo'
        ]);
        $this->naturaleza_form = 'ACTIVOS';
        $this->cuenta_activa = true;
        $this->titulo = false;
        $this->moneda = 'Pesos Colombianos';
        $this->saldo = 0;
    }

    public function render()
    {
        $items = $this->buildFlatTree();

        // Posibles padres (excluir self y descendientes si edita)
        $posiblesPadres = Cuenta::query()
            ->when($this->editingId, function ($q) {
                $q->where('id', '!=', $this->editingId);
                $desc = $this->descendantIdsOf($this->editingId);
                if (!empty($desc)) $q->whereNotIn('id', $desc);
            })
            ->orderBy('codigo')
            ->get(['id','codigo','nombre']);

        $nivelMax = $this->nivelMax;
        return view('livewire.cuentas-contables.plan-cuentas', compact('items','nivelMax','posiblesPadres'));
    }
}
