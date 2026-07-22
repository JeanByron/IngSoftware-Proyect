<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Response;

/**
 * RNF-07: comanda (ticket de cocina) de un pedido, en texto plano.
 *
 * Se entrega como text/plain con ancho fijo (~32 caracteres, el de una
 * impresora térmica de tickets). La acción de impresión física se programa
 * aparte; aquí sólo se genera el contenido. Módulo activable por su flag
 * MODULE_COMANDA (ver routes/web.php).
 */
class ComandaController extends Controller
{
    /** Ancho del ticket en caracteres (impresora térmica típica). */
    private const WIDTH = 32;

    public function __invoke(Order $order): Response
    {
        $order->load('items');

        $texto = $this->render($order);

        return response($texto, 200)
            ->header('Content-Type', 'text/plain; charset=utf-8')
            ->header('Content-Disposition', 'inline; filename="comanda-pedido-'.$order->id.'.txt"');
    }

    /** Construye el ticket en texto plano. */
    private function render(Order $order): string
    {
        $sep    = str_repeat('=', self::WIDTH);
        $subsep = str_repeat('-', self::WIDTH);
        $nombre = (string) config('comercio.nombre');

        $lineas = [];
        $lineas[] = $sep;
        $lineas[] = $this->centrar(mb_strtoupper($nombre));
        $lineas[] = $this->centrar('COMANDA DE COCINA');
        $lineas[] = $sep;
        $lineas[] = 'Pedido #'.$order->id;

        if ($order->isPresencial()) {
            $lineas[] = 'MESA: '.$order->table_number;
        } else {
            $lineas[] = 'DOMICILIO';
            $lineas[] = 'Dir: '.$order->address;
        }

        $lineas[] = 'Fecha: '.$order->created_at->format('d/m/Y H:i');
        $lineas[] = $subsep;

        foreach ($order->items as $item) {
            // "2x Bandeja Paisa .......... $24.000" (importe alineado a la derecha).
            $lineas[] = $this->row($item->quantity.'x '.$item->dish_name, $this->money($item->subtotal));
        }

        $lineas[] = $subsep;
        // Total a pagar. La devuelta se anota a mano (depende del efectivo recibido).
        $lineas[] = $this->row('TOTAL', $this->money($order->total));
        $lineas[] = $this->pagoLinea($order);
        $lineas[] = $subsep;
        $lineas[] = 'Estado: '.$order->statusLabel();
        $lineas[] = $sep;
        $lineas[] = '';

        return implode("\n", $lineas);
    }

    /** Línea del estado de pago (método + si está pagado o pendiente). */
    private function pagoLinea(Order $order): string
    {
        $metodo = match ($order->payment_method) {
            'tarjeta'       => 'Tarjeta',
            'efectivo'      => 'Efectivo',
            'transferencia' => 'Transferencia',
            default         => null,
        };

        if ($order->isPaid()) {
            return 'Pago: '.($metodo ?? '-').' (PAGADO)';
        }

        if ($order->payment_method === 'efectivo') {
            return 'Pago: Efectivo (COBRAR AL ENTREGAR)';
        }

        return 'Pago: '.($metodo ?? '-').' (pendiente)';
    }

    /** Da formato de dinero (pesos, miles con punto). */
    private function money($amount): string
    {
        return '$'.number_format((float) $amount, 0, ',', '.');
    }

    /**
     * Fila de dos columnas: texto a la izquierda, importe alineado a la derecha
     * en el ancho del ticket. Si no caben juntos, el importe baja a la línea
     * siguiente alineado a la derecha.
     */
    private function row(string $left, string $right): string
    {
        $gap = self::WIDTH - mb_strlen($left) - mb_strlen($right);

        if ($gap < 1) {
            return $left."\n".str_pad($right, self::WIDTH, ' ', STR_PAD_LEFT);
        }

        return $left.str_repeat(' ', $gap).$right;
    }

    /** Centra un texto dentro del ancho del ticket. */
    private function centrar(string $texto): string
    {
        $texto = mb_substr($texto, 0, self::WIDTH);
        $pad   = (int) max(0, floor((self::WIDTH - mb_strlen($texto)) / 2));

        return str_repeat(' ', $pad).$texto;
    }
}
