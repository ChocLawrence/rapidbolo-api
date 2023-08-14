<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $table = 'transactions';
    protected $primary_key = 'id';

    public function payment_methods()
    {
        return $this->hasOne('App\Models\PaymentMethod');
    }

    public function transaction_type()
    {
        return $this->hasOne('App\Models\TransactionType');
    }

    public function status()
    {
        return $this->hasOne('App\Models\Status');
    }

    public function users()
    {
        return $this->belongsToMany('App\Models\User');
    }
    
}
