<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {

    if (!Schema::hasTable('insurance_categories')) {

        Schema::create('insurance_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('insurance_provider_id')->nullable()->constrained('insurance_providers')->onDelete('cascade');
            $table->string('name')->nullable()->unique();
            $table->text('description')->nullable();
            $table->timestamps();

            // Indexes for better query performance
            $table->index('insurance_provider_id');
            
        });

    }
    }

    public function down(): void
    {
        Schema::dropIfExists('insurance_categories');
    }
};
