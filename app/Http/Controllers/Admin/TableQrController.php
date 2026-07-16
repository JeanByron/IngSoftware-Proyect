<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Illuminate\View\View;

/**
 * RNF-06: genera el código QR (ISO/IEC 18004) de cada mesa.
 *
 * El QR codifica la URL presencial /pedido?mesa=N (el "detalle técnico clave"
 * del proyecto). Se renderiza como SVG en PHP puro (sin depender de ext-gd),
 * en una vista imprimible para pegar en cada mesa.
 */
class TableQrController extends Controller
{
    /** Muestra el QR de una mesa concreta, listo para imprimir. */
    public function show(int $mesa): View
    {
        abort_if($mesa < 1, 404);

        $url = route('orders.create', ['mesa' => $mesa]);
        $svg = $this->renderQr($url);

        return view('admin.qr.show', [
            'mesa' => $mesa,
            'url'  => $url,
            'svg'  => $svg,
        ]);
    }

    /** Devuelve el QR de una URL como cadena SVG (300x300, sin ext-gd). */
    private function renderQr(string $data): string
    {
        $renderer = new ImageRenderer(
            new RendererStyle(300),
            new SvgImageBackEnd(),
        );

        return (new Writer($renderer))->writeString($data);
    }
}
