<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('payment_summaries')) {

        Schema::create('payment_summaries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained()->onDelete('cascade');
            $table->string('ref_number'); // link to patient and payment ref
            $table->decimal('total_amount', 10, 2)->default(0);
            $table->decimal('total_insurance_amount', 10, 2)->default(0);
            $table->decimal('total_patient_amount', 10, 2)->default(0);


            $table->dateTime('paid_at')->nullable();
            $table->enum('payment_method', ['cash', 'card', 'mobile', 'none'])->default('cash');
            
            $table->boolean('is_private')->default(0);
            $table->timestamps();


            
           $table->unique(['patient_id', 'ref_number']);
        });

        }
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_summaries');
    }
};
