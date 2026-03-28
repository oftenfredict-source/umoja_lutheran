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
            $table->string('organization')->nullable()->after('guest_email');
            $table->time('end_time')->nullable()->after('service_time');
            $table->string('duration')->nullable()->after('end_time');
            $table->text('purpose')->nullable()->after('duration');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('day_services', function (Blueprint $table) {
            $table->dropColumn(['organization', 'end_time', 'duration', 'purpose']);
        });
    }
};
