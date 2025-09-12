<?php

namespace App\Models\CuentasContables;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PlanCuentas extends Model
{
    protected $table = 'plan_cuentas';

    protected $fillable = [
        'codigo','nombre','nivel','padre_id',
        'naturaleza','cuenta_activa','titulo',
        'moneda','requiere_tercero','confidencial','nivel_confidencial',
        'clase_cuenta','cuenta_monetaria','cuenta_asociada','revalua_indice','bloquear_contab_manual',
        'relevante_flujo_caja','relevante_costos',
        'dimension1','dimension2','dimension3','dimension4',
        'saldo',
    ];

    protected $casts = [
        'nivel' => 'integer',
        'cuenta_activa' => 'boolean',
        'titulo' => 'boolean',
        'requiere_tercero' => 'boolean',
        'confidencial' => 'boolean',
        'nivel_confidencial' => 'integer',
        'cuenta_monetaria' => 'boolean',
        'cuenta_asociada' => 'boolean',
        'revalua_indice' => 'boolean',
        'bloquear_contab_manual' => 'boolean',
        'relevante_flujo_caja' => 'boolean',
        'relevante_costos' => 'boolean',
        'saldo' => 'decimal:2',
    ];

    /* Relaciones */
    public function padre(): BelongsTo { return $this->belongsTo(self::class, 'padre_id'); }
    public function hijos(): HasMany { return $this->hasMany(self::class, 'padre_id')->orderBy('codigo'); }

    /* Scopes */
    public function scopeRaices($q){ return $q->whereNull('padre_id')->orderBy('codigo'); }
    public function scopeActivas($q, bool $solo = true){ return $solo ? $q->where('cuenta_activa', true) : $q; }
    public function scopeNaturaleza($q, ?string $nat){
        if(!$nat || strtoupper($nat)==='TODAS') return $q;
        return $q->where('naturaleza', strtoupper($nat));
    }
    public function scopeBuscar($q, ?string $term){
        if(!$term) return $q;
        $t = trim($term);
        return $q->where(fn($qq)=>$qq
            ->where('codigo','like',"%{$t}%")
            ->orWhere('nombre','like',"%{$t}%"));
    }

    /* Helpers UI */
    public function getTieneHijosAttribute(): bool { return $this->hijos()->exists(); }
    public function getEsImputableAttribute(): bool { return !$this->titulo && $this->cuenta_activa; }
    public function getIndentPxAttribute(): int { return max(0, ($this->nivel - 1) * 18); }
    public function getRutaCodigoAttribute(): string {
        $nodo=$this; $partes=[];
        while($nodo){ array_unshift($partes,$nodo->codigo); $nodo=$nodo->padre; }
        return implode(' > ', $partes);
    }
    public function setNaturalezaAttribute($v){ $this->attributes['naturaleza'] = $v ? strtoupper($v) : null; }
    public function setCodigoAttribute($v){ $this->attributes['codigo'] = trim((string)$v); }

    /* Borrado seguro: huérfanos antes de eliminar (evita cascadas en SQL Server) */
    protected static function booted()
    {
        static::deleting(function (self $cuenta) {
            $cuenta->hijos()->update(['padre_id' => null]); // SET NULL manual
        });
    }
}
