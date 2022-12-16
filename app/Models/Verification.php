<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Verification extends Model
{
    use HasFactory;

    protected $table = 'verifications';
    protected $primary_key = 'id';

    public function status()
    {
        return $this->hasOne('App\Models\Status');
    }

    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }
}
