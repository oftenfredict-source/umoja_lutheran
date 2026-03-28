<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('departments', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Bar, Kitchen, Housekeeping
            $table->string('code')->unique()->nullable(); // bar, kitchen, housekeeping
            $table->string('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Seed default departments
        DB::table('departments')->insert([
            ['name' => 'Bar', 'code' => 'bar', 'description' => 'Bar section - drinks and beverages', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Kitchen', 'code' => 'kitchen', 'description' => 'Kitchen section - food items', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Housekeeping', 'code' => 'housekeeping', 'description' => 'Housekeeping section - cleaning and linens', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('departments');
    }
};
