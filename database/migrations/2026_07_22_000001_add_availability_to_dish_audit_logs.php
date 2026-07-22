<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * RNF-20: la bitácora del catálogo también registra el cambio de
 * DISPONIBILIDAD (disponible <-> no disponible), no sólo el precio.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('dish_audit_logs', function (Blueprint $table) {
            $table->boolean('old_available')->nullable()->after('new_price');
            $table->boolean('new_available')->nullable()->after('old_available');
        });
    }

    public function down(): void
    {
        Schema::table('dish_audit_logs', function (Blueprint $table) {
            $table->dropColumn(['old_available', 'new_available']);
        });
    }
};
