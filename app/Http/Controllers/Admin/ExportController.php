<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Dish;
use App\Models\Order;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * RNF-16: descarga del catálogo y de las ventas en CSV.
 *
 * Usa respuestas en streaming (streamDownload + fputcsv): las filas se escriben
 * directo a la salida sin cargar toda la colección en memoria (relevante para
 * RNF-03: no dispara el consumo de RAM con muchos registros).
 */
class ExportController extends Controller
{
    /** Catálogo de platos en CSV. */
    public function catalogo(): StreamedResponse
    {
        return response()->streamDownload(function () {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['id', 'nombre', 'descripcion', 'precio', 'disponible']);

            // chunk(): procesa de a 200 filas para no traer todo a memoria.
            Dish::orderBy('id')->chunk(200, function ($dishes) use ($out) {
                foreach ($dishes as $d) {
                    fputcsv($out, [
                        $d->id, $d->name, $d->description, $d->price,
                        $d->is_available ? 'si' : 'no',
                    ]);
                }
            });

            fclose($out);
        }, 'catalogo.csv', ['Content-Type' => 'text/csv']);
    }

    /** Ventas (pedidos) en CSV. */
    public function ventas(): StreamedResponse
    {
        return response()->streamDownload(function () {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['id', 'tipo', 'mesa', 'direccion', 'total', 'estado', 'fecha']);

            Order::orderBy('id')->chunk(200, function ($orders) use ($out) {
                foreach ($orders as $o) {
                    fputcsv($out, [
                        $o->id, $o->type, $o->table_number, $o->address,
                        $o->total, $o->status, $o->created_at,
                    ]);
                }
            });

            fclose($out);
        }, 'ventas.csv', ['Content-Type' => 'text/csv']);
    }
}
