<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {

    if (!Schema::hasTable('patient_activities')) {

        Schema::create('patient_activities', function (Blueprint $table) {
            $table->id();

            // Unique reference number per payment record
            $table->string('ref_number')->index();

            // Relationships
            $table->foreignId('patient_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('service_id')->nullable()->constrained('services')->nullOnDelete();

            $table->foreignId('cashier_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('service_name')->nullable();

            /**
             * Service Pricing & Coverage
             */
            $table->decimal('unit_price', 10, 2)->default(0);     // Service base price (from `services.price`)
            $table->decimal('insurance_coverage_amount', 10, 2)->default(0); // Insurance share
            $table->decimal('patient_coverage_amount', 10, 2)->default(0);   // Patient share
            
            /**
             * Status & Payment Details
             */
            

            $table->timestamps();
        });

    }
    }

    public function down(): void
    {
        Schema::dropIfExists('patient_activities');
    }
};
