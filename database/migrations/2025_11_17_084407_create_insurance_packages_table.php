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
        if (!Schema::hasTable('insurance_packages')) {

        Schema::create('insurance_packages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('insurance_category_id')->nullable()->constrained('insurance_categories')->onDelete('cascade');
            $table->string('package_name')->nullable();
            $table->decimal('patient_cover', 10, 2)->nullable();
            $table->decimal('insurance_cover', 10, 2)->nullable();
            $table->timestamps();
        });

        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('insurance_packages');
    }
};
