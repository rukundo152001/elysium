<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InsuranceProvider extends Model
{
    use HasFactory;
    protected $table = 'insurance_providers';

    protected $fillable = ['name', 'description', 'is_active'];

    

    protected $casts = [
        'is_active' => 'boolean',
    ];


    

 public function categories()
{
    return $this->hasMany(InsuranceCategory::class, 'insurance_provider_id');
}


    


    
}
