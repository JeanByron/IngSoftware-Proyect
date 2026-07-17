<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;

/**
 * Plato del menú (Módulo de Gestión de Menú).
 */
class Dish extends Model
{
    use HasFactory;

    /** Clave de caché del catálogo de platos disponibles (RNF-04). */
    public const CATALOG_CACHE_KEY = 'catalogo.disponibles';

    protected $fillable = [
        'name',
        'description',
        'price',
        'is_available',
    ];

    protected $casts = [
        'price'        => 'decimal:2',
        'is_available' => 'boolean',
    ];

    /**
     * RNF-04: cualquier cambio en un plato (crear/editar/borrar/toggle) invalida
     * la caché del catálogo automáticamente, vía eventos de modelo de Eloquent.
     * Así no hay que recordar limpiarla en cada método del controlador.
     */
    protected static function booted(): void
    {
        static::saved(fn () => self::forgetCatalogCache());   // create + update
        static::deleted(fn () => self::forgetCatalogCache());
    }

    /**
     * Scope para mostrar únicamente platos disponibles.
     * RF-05: las vistas de cliente sólo deben mostrar platos disponibles.
     */
    public function scopeAvailable(Builder $query): Builder
    {
        return $query->where('is_available', true);
    }

    /**
     * RNF-04: catálogo de platos disponibles con caché.
     * Evita golpear la BD en cada visita del cliente (consulta muy frecuente).
     * Se invalida con forgetCatalogCache() al mutar cualquier plato.
     *
     * Se cachea un ARRAY plano (no modelos Eloquent) y se rehidrata al leer:
     * serializar objetos Eloquent en el store 'database' es frágil (puede volver
     * como __PHP_Incomplete_Class al deserializar); un array siempre round-trip
     * seguro. hydrate() reconstruye la colección de modelos para las vistas.
     *
     * @return Collection<int, Dish>
     */
    public static function availableCached(): Collection
    {
        $rows = Cache::remember(
            self::CATALOG_CACHE_KEY,
            now()->addMinutes(10),
            fn () => self::available()->orderBy('name')->get()->toArray(),
        );

        return self::hydrate($rows);
    }

    /** Invalida la caché del catálogo (llamar tras crear/editar/borrar/togglear). */
    public static function forgetCatalogCache(): void
    {
        Cache::forget(self::CATALOG_CACHE_KEY);
    }
}
