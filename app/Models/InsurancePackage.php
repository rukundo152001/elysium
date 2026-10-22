<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class InsurancePackage extends Model
{
    use HasFactory;
    protected $table =  'insurance_packages';
    protected $fillable = [
        'insurance_category_id',
        'package_name',
        'patient_cover',
        'insurance_cover'
    ];

    public function category()
{
    return $this->belongsTo(InsuranceCategory::class, 'insurance_category_id');
}
public function patient()
{
    return $this->hasOne(Patient::class);
}

}
