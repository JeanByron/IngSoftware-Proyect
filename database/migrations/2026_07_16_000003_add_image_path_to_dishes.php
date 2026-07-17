<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * RNF-01: imagen del plato. Se guarda sólo la RUTA relativa del archivo en el
 * disco 'public' (no el binario en BD). Nullable: un plato puede no tener foto
 * (la vista muestra un marcador de posición).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('dishes', function (Blueprint $table) {
            $table->string('image_path')->nullable()->after('description');
        });
    }

    public function down(): void
    {
        Schema::table('dishes', function (Blueprint $table) {
            $table->dropColumn('image_path');
        });
    }
};
