<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;

    protected $table = 'notifications';
    protected $primary_key = 'id';

    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }

    public function status()
    {
        return $this->hasOne('App\Models\Status');
    }
}
