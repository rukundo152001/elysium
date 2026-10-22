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
        /**
         * 1️⃣ COUNTRIES TABLE
         * Each country can have many provinces.
         */

        if (!Schema::hasTable('countries')) {
        Schema::create('countries', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique(); // e.g. Rwanda
            $table->string('code', 10)->nullable(); // e.g. RW
            $table->timestamps();
        });

        }
        /**
         * 2️⃣ PROVINCES TABLE
         * Linked to a country.
         */
        if (!Schema::hasTable('provinces')) {
        Schema::create('provinces', function (Blueprint $table) {
            $table->id();
            $table->foreignId('country_id')->constrained('countries')->cascadeOnDelete();
            $table->string('name');
            $table->timestamps();

            $table->unique(['country_id', 'name']); // Prevent duplicates per country
        });

        }

        /**
         * 3️⃣ DISTRICTS TABLE
         * Linked to a province.
         */

        if (!Schema::hasTable('districts')) {
        Schema::create('districts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('province_id')->constrained('provinces')->cascadeOnDelete();
            $table->string('name');
            $table->timestamps();

            $table->unique(['province_id', 'name']);
        });

        }
        /**
         * 4️⃣ SECTORS TABLE
         * Linked to a district.
         */
        if (!Schema::hasTable('sectors')) {
        Schema::create('sectors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('district_id')->constrained('districts')->cascadeOnDelete();
            $table->string('name');
            $table->timestamps();

            $table->unique(['district_id', 'name']);
        });

        }

        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop in reverse order of creation to avoid foreign key issues
            
        Schema::dropIfExists('sectors');
        Schema::dropIfExists('districts');
        Schema::dropIfExists('provinces');
        Schema::dropIfExists('countries');
    }
};
