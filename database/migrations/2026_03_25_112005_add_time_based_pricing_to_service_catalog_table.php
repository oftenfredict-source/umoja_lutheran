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
        Schema::table('service_catalog', function (Blueprint $table) {
            $table->decimal('night_price_tanzanian', 10, 2)->nullable()->after('price_tanzanian');
            $table->decimal('night_price_international', 10, 2)->nullable()->after('price_international');
            $table->time('day_start_time')->default('07:00:00')->after('night_price_international');
            $table->time('day_end_time')->default('18:00:00')->after('day_start_time');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('service_catalog', function (Blueprint $table) {
            $table->dropColumn(['night_price_tanzanian', 'night_price_international', 'day_start_time', 'day_end_time']);
        });
    }
};
