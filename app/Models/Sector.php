<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Sector extends Model
{
    use HasFactory;
    protected $table = 'sectors' ;
    protected $fillable = ['district_id','name'];

    public function district() { return $this->belongsTo(District::class); }
    
}
