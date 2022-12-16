<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Chat extends Model
{
    use HasFactory;

    protected $table = 'chats';
    protected $primary_key = 'id';

    public function users()
    {
        return $this->belongsToMany('App\Models\User');
    }

    public function status()
    {
        return $this->hasOne('App\Models\Status');
    }
}
