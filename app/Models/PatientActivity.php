<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class PatientActivity extends Model
{
    use HasFactory;

    use HasFactory;
    protected $table = 'patient_activities';

    protected $fillable = [
        'ref_number',
        'patient_id',
        'service_id',
        'service_name',
        'cashier_id',
        'unit_price',
        'insurance_coverage_amount',
        'patient_coverage_amount',
        
    ];

    protected $casts = [
        'insurance_coverage_amount' => 'decimal:2',
        'patient_coverage_amount' => 'decimal:2',
        'unit_price' => 'decimal:2',
    ];

 
    
    
// 🔹 A patientActivities belongs to one Patient
    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    // 🔹 A patientActivities belongs to one MRI Service (e.g., Brain MRI)
    public function service()
{
    return $this->belongsTo(Service::class, 'service_id');
}


    // 🔹 A patientActivities is handled by one cashier (user)
    public function cashier()
    {
        return $this->belongsTo(User::class, 'cashier_id');
    }

    // 🔹 (Optional) Link to summary by ref_number (not direct foreign key)
    public function paymentSummary()
    {
        return $this->belongsTo(PaymentSummary::class, 'ref_number', 'ref_number');
    }

    // 🔹 Helper to compute coverage %
    public function getCoveragePercentagesAttribute()
    {
        $total = $this->unit_price;
        return [
            'patient_percent' => $total > 0 ? round(($this->patient_coverage_amount / $total) * 100, 2) : 0,
            'insurance_percent' => $total > 0 ? round(($this->insurance_coverage_amount / $total) * 100, 2) : 0,
        ];
    }





}
