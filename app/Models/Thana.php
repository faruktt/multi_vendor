<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Thana extends Model
{
    protected $table = 'thanas';
    public $timestamps = false;

    protected $fillable = ['district_id','name','bn_name'];

    public function district()
    {
        return $this->belongsTo(District::class, 'district_id', 'id');
    }


}
