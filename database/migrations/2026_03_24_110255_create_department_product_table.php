<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('department_product', function (Blueprint $table) {
            $table->id();
            $table->foreignId('department_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->string('category')->nullable(); // The category within that department
            $table->string('notes')->nullable();
            $table->timestamps();

            $table->unique(['department_id', 'product_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('department_product');
    }
};
