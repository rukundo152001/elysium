<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PaymentSummary extends Model
{
    use HasFactory;
    protected $table = 'payment_summaries';
    protected $fillable = [
        'ref_number',
        'patient_id',
        'total_amount',
        'total_patient_amount',
        'total_insurance_amount',
        'paid_at',
        'payment_method' ,    

        'is_private',
    ];

    protected $casts = [
        'total_insurance_amount' => 'decimal:2',
        'total_patient_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'paid_at' => 'datetime',
        'is_private' => 'boolean',
    ];

    // 🔹 Relationships
    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function patientActivities()
    {
        return $this->hasMany(patientActivity::class, 'ref_number', 'ref_number');
    }

    // 🔹 Helper to auto-recalculate totals
    public function refreshTotals()
    {
        $payments = $this->payments;
        $this->update([
            'total_amount' => $payments->sum('unit_price'),
            'patient_amount' => $payments->sum('patient_cover_amount'),
            'insurance_amount' => $payments->sum('insurance_cover_amount'),
        ]);
    }
    


    
}
