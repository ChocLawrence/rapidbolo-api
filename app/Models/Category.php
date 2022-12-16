<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $table = 'categories';
    protected $primary_key = 'id';

    public function services()
    {
        return $this->hasMany('App\Models\Service');
    }

    public function tags()
    {
        return $this->hasMany('App\Models\Tag');
    }
}
