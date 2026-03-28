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
        Schema::create('store_announcements', function (Blueprint $table) {
            $table->id();
            $table->text('message');
            $table->string('target_role'); // head_chef, bar_keeper, both, all
            $table->boolean('is_active')->default(true);
            $table->unsignedBigInteger('created_by');
            $table->dateTime('expires_at')->nullable();
            $table->timestamps();

            $table->foreign('created_by')->references('id')->on('staffs')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('store_announcements');
    }
};
