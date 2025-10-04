<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->json('land_area_coordinates')->nullable()->comment('Coordenadas del área de terreno seleccionada');
            $table->decimal('land_area_size', 10, 2)->nullable()->comment('Tamaño del área en hectáreas');
            $table->string('land_area_name')->nullable()->comment('Nombre del área de terreno');
            $table->text('land_area_description')->nullable()->comment('Descripción del área de terreno');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'land_area_coordinates',
                'land_area_size',
                'land_area_name',
                'land_area_description'
            ]);
        });
    }
};
