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
        Schema::table('day_services', function (Blueprint $table) {
            $table->string('vehicle_name')->nullable()->after('guest_email');
            $table->string('plate_number')->nullable()->after('vehicle_name');
            $table->dropColumn('vehicle_details');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('day_services', function (Blueprint $table) {
            $table->dropColumn(['vehicle_name', 'plate_number']);
            $table->string('vehicle_details')->nullable()->after('guest_email');
        });
    }
};
