<?php

namespace App\Livewire\SubCategorias;

use App\Models\Categorias\Subcategoria;  // Corregido el namespace
use App\Models\Categorias\Categoria;
use Livewire\Component;

class SubCategorias extends Component
{
    public $subcategorias, $categoria_id, $nombre, $descripcion, $activo = true, $subcategoria_id;
    public $isEdit = false;

    public function render()
    {
        $this->subcategorias = Subcategoria::with('categoria')->get();  // Corregido la referencia a Subcategoria
        return view('livewire.sub-categorias.sub-categorias', [
            'categorias' => Categoria::all()
        ]);
    }

    public function store()
    {
        $this->validate([
            'categoria_id' => 'required',
            'nombre' => 'required'
        ]);

        Subcategoria::create($this->only(['categoria_id', 'nombre', 'descripcion', 'activo']));  // Corregido la referencia a Subcategoria

        $this->resetInput();
    }

    public function edit($id)
    {
        $sub = Subcategoria::findOrFail($id);  // Corregido la referencia a Subcategoria
        $this->fill($sub->toArray());
        $this->subcategoria_id = $id;
        $this->isEdit = true;
    }

    public function update()
    {
        $this->validate([
            'categoria_id' => 'required',
            'nombre' => 'required'
        ]);

        Subcategoria::find($this->subcategoria_id)->update($this->only(['categoria_id', 'nombre', 'descripcion', 'activo']));  // Corregido la referencia a Subcategoria

        $this->resetInput();
    }

    public function delete($id)
    {
        Subcategoria::destroy($id);  // Corregido la referencia a Subcategoria
    }

    private function resetInput()
    {
        $this->reset(['categoria_id', 'nombre', 'descripcion', 'activo', 'subcategoria_id', 'isEdit']);
    }
}
