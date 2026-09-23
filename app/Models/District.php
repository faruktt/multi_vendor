<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class District extends Model
{

    protected $table = 'districts';
    public $timestamps = false;

    protected $fillable = ['division_id','name','bn_name'];

    public function thanas()
    {
        return $this->hasMany(Thana::class, 'district_id', 'id');
    }
}
