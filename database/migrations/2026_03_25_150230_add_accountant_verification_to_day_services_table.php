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
            if (!Schema::hasColumn('day_services', 'accountant_verified_at')) {
                $table->timestamp('accountant_verified_at')->nullable()->after('payment_status');
            }
            if (!Schema::hasColumn('day_services', 'accountant_id')) {
                $table->unsignedBigInteger('accountant_id')->nullable()->after('accountant_verified_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('day_services', function (Blueprint $table) {
            $table->dropForeign(['accountant_id']);
            $table->dropColumn(['accountant_verified_at', 'accountant_id']);
        });
    }
};
