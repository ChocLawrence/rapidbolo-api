<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Demand extends Model
{
    use HasFactory;

    protected $table = 'demands';
    protected $primary_key = 'id';

    public function rating()
    {
        return $this->hasMany('App\Models\Rating');
    }

    public function service()
    {
        return $this->belongsTo('App\Models\Service');
    }

    public function status()
    {
        return $this->hasOne('App\Models\Status');
    }
}
