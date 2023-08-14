<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserActivity extends Model
{
    use HasFactory;

    protected $table = 'user_activities';
    protected $primary_key = 'id';

    public function users()
    {
        return $this->belongsToMany('App\Models\User');
    }

}
