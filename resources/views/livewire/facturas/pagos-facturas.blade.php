<div x-data="{ open: @entangle('show') }" x-cloak>
  <div x-show="open" class="fixed inset-0 z-40 bg-black/40"></div>

  <div x-show="open" class="fixed inset-0 z-50 grid place-items-center p-4">
    <div class="w-full max-w-xl rounded-2xl bg-white dark:bg-gray-900 shadow-2xl border dark:border-gray-700">
      <div class="px-5 py-4 border-b dark:border-gray-700 flex items-center justify-between">
        <h3 class="text-lg font-semibold"><i class="fa-solid fa-cash-register mr-2"></i> Registrar pago</h3>
        <button class="text-slate-500 hover:text-slate-800" @click="open=false" wire:click="cerrar">
          <i class="fa-solid fa-xmark"></i>
        </button>
      </div>

      <div class="p-5 space-y-4">
        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="text-sm">Fecha</label>
            <input type="date" class="w-full rounded-lg border dark:bg-gray-800 dark:text-white" wire:model="fecha">
            @error('fecha') <div class="text-rose-600 text-xs mt-1">{{ $message }}</div> @enderror
          </div>
          <div>
            <label class="text-sm">Monto</label>
            <input type="number" step="0.01" min="0.01" class="w-full rounded-lg border text-right dark:bg-gray-800 dark:text-white" wire:model="monto">
            @error('monto') <div class="text-rose-600 text-xs mt-1">{{ $message }}</div> @enderror
          </div>
        </div>

        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="text-sm">Método</label>
            <input type="text" class="w-full rounded-lg border dark:bg-gray-800 dark:text-white" wire:model="metodo" placeholder="efectivo, transferencia…">
          </div>
          <div>
            <label class="text-sm">Referencia</label>
            <input type="text" class="w-full rounded-lg border dark:bg-gray-800 dark:text-white" wire:model="referencia">
          </div>
        </div>

        <div>
          <label class="text-sm">Notas</label>
          <textarea rows="3" class="w-full rounded-lg border dark:bg-gray-800 dark:text-white" wire:model="notas"></textarea>
        </div>

        @if(isset($factura))
          <div class="rounded-lg border dark:border-gray-700 p-3 text-sm">
            <div class="flex justify-between"><span>Total factura</span><strong>${{ number_format($factura->total,2) }}</strong></div>
            <div class="flex justify-between"><span>Pagado</span><strong>${{ number_format($factura->pagado,2) }}</strong></div>
            <div class="flex justify-between"><span>Saldo</span><strong>${{ number_format($factura->saldo,2) }}</strong></div>
          </div>
        @endif
      </div>

      <div class="px-5 py-4 border-t dark:border-gray-700 flex items-center justify-end gap-2">
        <button class="px-4 py-2 rounded-xl bg-white border dark:bg-gray-900 dark:border-gray-700" @click="open=false" wire:click="cerrar">Cancelar</button>
        <button class="px-4 py-2 rounded-xl bg-emerald-600 text-white hover:bg-emerald-700" wire:click="guardarPago">
          <i class="fa-solid fa-check mr-2"></i> Guardar pago
        </button>
      </div>
    </div>
  </div>
</div>
