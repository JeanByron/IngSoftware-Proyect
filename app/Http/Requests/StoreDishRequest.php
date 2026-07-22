<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validación declarativa del plato (RF-01), compartida por crear y editar.
 * Equivale a un "Schema" de marshmallow: saca las reglas del controlador.
 */
class StoreDishRequest extends FormRequest
{
    /** La autorización real la da el middleware 'auth' de la ruta. */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'name'         => ['required', 'string', 'max:255'],
            'description'  => ['nullable', 'string', 'max:1000'],
            // RNF-01: imagen opcional (jpg/png/webp, máx 2 MB) y con resolución
            // máxima de 1280x720 (720p) para optimizar el tiempo de carga.
            'image'        => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:2048', 'dimensions:max_width=1280,max_height=720'],
            'price'        => ['required', 'numeric', 'min:0'],
            'is_available' => ['nullable', 'boolean'],
        ];
    }

    /**
     * Datos ya normalizados para persistir: el checkbox desmarcado no envía
     * valor, así que se resuelve is_available a booleano explícito.
     *
     * @return array<string, mixed>
     */
    public function validatedData(): array
    {
        $data = $this->validated();
        $data['is_available'] = $this->boolean('is_available');

        // La imagen (archivo) se persiste aparte en el controlador; no es una
        // columna con este nombre.
        unset($data['image']);

        return $data;
    }
}
