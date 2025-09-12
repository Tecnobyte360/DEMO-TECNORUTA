<?php

namespace App\Livewire\ConfiguracionEmpresas;

use App\Models\ConfiguracionEmpresas\Empresa;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class Empresas extends Component
{
    use WithFileUploads, WithPagination;

    protected $paginationTheme = 'tailwind';

    public ?Empresa $empresa = null;

    // Formulario
    public string $nombre = '';
    public ?string $nit = null;
    public ?string $email = null;
    public ?string $telefono = null;
    public ?string $sitio_web = null;
    public ?string $direccion = null;

    public ?string $color_primario = null;
    public ?string $color_secundario = null;
    public bool $is_activa = true;

    // Archivos (subidas temporales)
    public $logo;
    public $logo_dark;
    public $favicon;

    // Listado
    public string $q = '';
    public int $perPage = 10;

    protected function rules(): array
    {
        return [
            'nombre' => ['required','string','max:255'],
            'nit' => ['nullable','string','max:50'],
            'email' => ['nullable','email','max:255'],
            'telefono' => ['nullable','string','max:50'],
            'sitio_web' => ['nullable','url','max:255'],
            'direccion' => ['nullable','string','max:255'],
            'color_primario' => ['nullable','regex:/^#?[0-9A-Fa-f]{6}$/'],
            'color_secundario' => ['nullable','regex:/^#?[0-9A-Fa-f]{6}$/'],
            'is_activa' => ['boolean'],

            'logo' => ['nullable','image','mimes:png,jpg,jpeg,webp','max:2048'],
            'logo_dark' => ['nullable','image','mimes:png,jpg,jpeg,webp','max:2048'],
            'favicon' => ['nullable','mimes:png,jpg,jpeg,webp,ico','max:1024'],
        ];
    }

    public function updatingQ() { $this->resetPage(); } // al buscar, regresa a página 1
    public function updatingPerPage() { $this->resetPage(); }

    public function mount(): void
    {
        // Si quieres editar el primer registro al abrir, descomenta:
        // $this->empresa = Empresa::first();
        // if ($this->empresa) $this->fillFromModel($this->empresa);
    }

    public function createNew(): void
    {
        $this->resetForm();
        $this->empresa = null;
    }

    public function edit(int $id): void
    {
        $model = Empresa::findOrFail($id);
        $this->empresa = $model;
        $this->fillFromModel($model);
    }

    public function save(): void
    {
        $this->validate();

        $empresa = $this->empresa ?? new Empresa();

        $empresa->fill([
            'nombre'           => $this->nombre,
            'nit'              => $this->nit,
            'email'            => $this->email,
            'telefono'         => $this->telefono,
            'sitio_web'        => $this->sitio_web,
            'direccion'        => $this->direccion,
            'color_primario'   => $this->normalizeHex($this->color_primario),
            'color_secundario' => $this->normalizeHex($this->color_secundario),
            'is_activa'        => $this->is_activa,
        ]);

        if ($this->logo) {
            $this->replaceFile($empresa, 'logo_path', $this->logo, 'empresas/logos');
        }
        if ($this->logo_dark) {
            $this->replaceFile($empresa, 'logo_dark_path', $this->logo_dark, 'empresas/logos');
        }
        if ($this->favicon) {
            $this->replaceFile($empresa, 'favicon_path', $this->favicon, 'empresas/favicons');
        }

        $empresa->save();
        $this->empresa = $empresa;

        session()->flash('ok', 'Configuración de empresa guardada correctamente.');
        $this->resetUploads(); // limpia inputs file
    }

    public function delete(int $id): void
    {
        $empresa = Empresa::findOrFail($id);

        // Elimina archivos asociados
        foreach (['logo_path','logo_dark_path','favicon_path'] as $attr) {
            if ($empresa->$attr && Storage::disk('public')->exists($empresa->$attr)) {
                Storage::disk('public')->delete($empresa->$attr);
            }
        }
        $empresa->delete();

        // Si estabas editando esta misma, limpia el formulario
        if ($this->empresa?->id === $id) {
            $this->createNew();
        }

        session()->flash('ok', 'Empresa eliminada.');
        $this->resetPage();
    }

    private function replaceFile(Empresa $empresa, string $attr, $uploadedFile, string $dir): void
    {
        if ($empresa->$attr && Storage::disk('public')->exists($empresa->$attr)) {
            Storage::disk('public')->delete($empresa->$attr);
        }
        $path = $uploadedFile->store($dir, 'public');
        $empresa->$attr = $path;
    }

    private function normalizeHex(?string $hex): ?string
    {
        if (!$hex) return null;
        $hex = ltrim($hex, '#');
        return '#'.strtolower($hex);
    }

    private function fillFromModel(Empresa $m): void
    {
        $this->fill($m->only([
            'nombre','nit','email','telefono','sitio_web','direccion',
            'color_primario','color_secundario','is_activa'
        ]));
        $this->resetUploads();
    }

    private function resetForm(): void
    {
        $this->reset([
            'nombre','nit','email','telefono','sitio_web','direccion',
            'color_primario','color_secundario','is_activa',
        ]);
        $this->is_activa = true;
        $this->resetUploads();
    }

    private function resetUploads(): void
    {
        $this->reset(['logo','logo_dark','favicon']);
    }

    public function render()
    {
         $rows = Empresa::query()
            ->when($this->q !== '', function($q){
                $q->where(function($sub){
                    $sub->where('nombre','like',"%{$this->q}%")
                        ->orWhere('nit','like',"%{$this->q}%")
                        ->orWhere('email','like',"%{$this->q}%")
                        ->orWhere('telefono','like',"%{$this->q}%");
                });
            })
            ->latest('id')
            ->paginate($this->perPage);

        return view('livewire.configuracion-empresas.empresas',compact('rows'));
    }
    
}
