<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {

    if (!Schema::hasTable('insurance_providers')) {

        Schema::create('insurance_providers', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            // Indexes for better query performance
            $table->index('is_active');
        });

    }
    }

    public function down(): void
    {
        Schema::dropIfExists('insurance_providers');
    }
};
