<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DishAuditLog;
use Illuminate\View\View;

/**
 * RNF-20: vista de la bitácora de auditoría del catálogo/precios. Sólo lectura
 * (el registro es inalterable, append-only): lista quién cambió qué y cuándo.
 */
class DishAuditController extends Controller
{
    public function index(): View
    {
        $logs = DishAuditLog::with('user')->latest()->paginate(20);

        return view('admin.audit.dishes', compact('logs'));
    }
}
