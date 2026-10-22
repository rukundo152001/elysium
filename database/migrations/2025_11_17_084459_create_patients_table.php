<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {

    if (!Schema::hasTable('patients')) {
        Schema::create('patients', function (Blueprint $table) {
            $table->id();
            $table->string('ref_number')->unique(); // Inserted manually


            // Personal Information
            $table->string('first_name');
            $table->string('last_name');
            $table->date('dob');
            $table->enum('gender', ['male', 'female']);
            $table->string('phone');

            // Identification
            $table->string('referred_from')->nullable();

            // Address (database-based hierarchical)
            $table->foreignId('country_id')->nullable()->constrained('countries')->onDelete('set null');
            $table->foreignId('province_id')->nullable()->constrained('provinces')->onDelete('set null');
            $table->foreignId('district_id')->nullable()->constrained('districts')->onDelete('set null');
            $table->foreignId('sector_id')->nullable()->constrained('sectors')->onDelete('set null');
            $table->foreignId('registered_by')->nullable()->constrained('users')->onDelete('set null');

        
            // Insurance Details
            $table->foreignId('insurance_provider_id')->nullable()->constrained('insurance_providers')->onDelete('set null');
            $table->foreignId('insurance_category_id')->nullable()->constrained('insurance_categories')->onDelete('set null');
            $table->foreignId('insurance_package_id')->nullable()->constrained('insurance_packages')->onDelete('set null');
            $table->string('insurance_number')->nullable();

            // Additional affiliate info
            $table->string('affiliate_name')->nullable();
            $table->text('affiliate_insurance_number')->nullable();
            $table->text('affiliate_relationship')->nullable();
            $table->string('assigned_location')->nullable();

            // Technician treated
            $table->foreignId('technician_id')->nullable()->constrained('users')->onDelete('set null');
            $table->date('treatment_date')->nullable();

            $table->timestamps();
        });

    }
    }

    public function down(): void
    {
        Schema::dropIfExists('patients');
    }
};
