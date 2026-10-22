<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Patient extends Model
{
    use HasFactory;

    protected $table = 'patients';
    
    protected $fillable = [
        'ref_number',
        'first_name',
        'last_name',
        'dob',
        'gender',
        'phone',
        'country_id',
        'province_id',
        'district_id',
        'sector_id',
        'registered_by',
        'referred_from',
        'insurance_provider_id',
        'insurance_category_id',
        'insurance_package_id',
        'insurance_number',
        'affiliate_name',
        'affiliate_insurance_number',
        'affiliate_relationship',
        'assigned_location',
        'technician_id',
        'treatment_date',
    ];
            

    protected $casts = [
        'dob' => 'date',
        'treatment_date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    //
    // RELATIONSHIPS
    //

    // If you store provinces/districts/etc in the addresses table, link them to Address model
    public function country()
    {
        return $this->belongsTo(Country::class, 'country_id');
    }
    public function province()
    {
        return $this->belongsTo(Province::class, 'province_id');
    }

    public function district()
    {
        return $this->belongsTo(District::class, 'district_id');
    }

    public function sector()
    {
        return $this->belongsTo(Sector::class, 'sector_id');
    }

   
    public function scopeWithInsurance($query)
    {
        return $query->whereHas('insuranceProvider');
    }

    

    // Insurance relationships (if you have these models/tables)
    public function insuranceProvider()
    {
        return $this->belongsTo(InsuranceProvider::class, 'insurance_provider_id');
    }

    public function insuranceCategory()
    {
        return $this->belongsTo(InsuranceCategory::class, 'insurance_category_id');
    }
    public function insurancePackage()
    {
        return $this->belongsTo(InsurancePackage::class, 'insurance_package_id');
    }


    // Relationships
    


    public function registeredBy()
    {
        return $this->belongsTo(User::class, 'registered_by');
    }


    
public function createdBy()
{
    return $this->belongsTo(User::class, 'registered_by');

}



public function patientActivities()
{
    return $this->hasMany(PatientActivity::class, 'patient_id');
}

public function paymentSummary()
{
    return $this->hasMany(PaymentSummary::class, 'patient_id');
}

public function services()
{
    return $this->belongsToMany(Service::class, 'patient_activities', 'patient_id', 'service_id')
                ->withPivot(['unit_price', ])
                ->withTimestamps();
}





public function technician() {
    return $this->belongsTo(User::class, 'technician_id');
}





}
