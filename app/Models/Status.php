<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Status extends Model
{
    use HasFactory;

    protected $table = 'statuses';
    protected $primary_key = 'id';

    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }

    public function subscription()
    {
        return $this->belongsTo('App\Models\Subscription');
    }

    public function notification()
    {
        return $this->belongsTo('App\Models\Notification');
    }

    public function transaction()
    {
        return $this->belongsTo('App\Models\Transaction');
    }

    public function verification()
    {
        return $this->belongsTo('App\Models\Verification');
    }

    public function demand()
    {
        return $this->belongsTo('App\Models\Demand');
    }

    public function chat()
    {
        return $this->belongsTo('App\Models\Chat');
    }

}
