<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Province extends Model
{
    use HasFactory;
    protected $table = 'provinces';
    protected $fillable = ['country_id','name'];

    public function country() { return $this->belongsTo(Country::class); }
    public function districts() { return $this->hasMany(District::class); }
}
