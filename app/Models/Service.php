<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'category',
        'price',
        'description',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    // Relationships
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    // Helper methods
    



 

public function payments()
{
    return $this->hasMany(PatientActivity::class, 'service_id');
}

public function patients()
{
    return $this->belongsToMany(Patient::class, 'payments', 'service_id', 'patient_id')
                ->withPivot(['unit_price', 'total_price', 'amount_paid', 'balance', 'is_paid', 'payment_method'])
                ->withTimestamps();
}






}
