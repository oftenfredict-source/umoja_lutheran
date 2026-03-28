<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('product_variants', function (Blueprint $table) {
            $table->string('purchasing_unit')->nullable()->after('measurement')->comment('E.g., Sado, Carton, Crate');
            $table->string('receiving_unit')->nullable()->after('purchasing_unit')->comment('E.g., Kg, Litre, Bottle');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_variants', function (Blueprint $table) {
            $table->dropColumn(['purchasing_unit', 'receiving_unit']);
        });
    }
};
