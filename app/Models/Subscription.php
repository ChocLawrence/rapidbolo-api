<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
    use HasFactory;

    protected $table = 'subscriptions';
    protected $primary_key = 'id';

    public function status()
    {
        return $this->hasOne('App\Models\Status');
    }

    public function plan()
    {
        return $this->belongsTo('App\Models\Plan');
    }

    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }
}
