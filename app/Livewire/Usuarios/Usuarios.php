<?php

namespace App\Livewire\Usuarios;

use App\Models\User;
use Livewire\Component;

class Usuarios extends Component
{
    public $usuarios;

    public function mount()
    {
        $this->usuarios = User::all(); 
    }

    public function render()
    {
        return view('livewire.usuarios.usuarios');
    }
}
