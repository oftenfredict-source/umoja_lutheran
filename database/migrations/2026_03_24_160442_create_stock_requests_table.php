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
        Schema::create('stock_requests', function (Illuminate\Database\Schema\Blueprint $table) {
            $table->id();
            $table->foreignId('requested_by')->constrained('staffs');
            $table->foreignId('product_variant_id')->constrained('product_variants');
            $table->decimal('quantity', 10, 2);
            $table->string('unit'); // 'packages' (crates) or 'bottles' (pics)
            $table->string('status')->default('pending_accountant'); // pending_accountant, pending_manager, approved, rejected, completed

            $table->foreignId('accountant_id')->nullable()->constrained('staffs');
            $table->timestamp('accountant_approved_at')->nullable();

            $table->foreignId('manager_id')->nullable()->constrained('staffs');
            $table->timestamp('manager_approved_at')->nullable();

            $table->foreignId('storekeeper_id')->nullable()->constrained('staffs');
            $table->timestamp('distributed_at')->nullable();

            $table->foreignId('stock_transfer_id')->nullable()->constrained('stock_transfers');

            $table->text('notes')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_requests');
    }
};
