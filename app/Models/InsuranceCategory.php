<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InsuranceCategory extends Model
{
    use HasFactory;
    protected $table = 'insurance_categories';
    protected $fillable = ['insurance_provider_id', 'name', 'description'];

    public function provider()
{
    return $this->belongsTo(InsuranceProvider::class, 'insurance_provider_id');
}

public function packages()
{
    return $this->hasMany(InsurancePackage::class, 'insurance_category_id');
}

    


}
