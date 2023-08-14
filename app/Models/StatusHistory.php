<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StatusHistory extends Model
{
    use HasFactory;

    protected $table = 'status_histories';
    protected $primary_key = 'id';

    public function demand()
    {
        return $this->belongsTo('App\Models\Demand');
    }

    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }


}
