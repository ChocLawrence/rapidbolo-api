<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransactionType extends Model
{
    use HasFactory;


    protected $table = 'transaction_types';
    protected $primary_key = 'id';

    public function transactions()
    {
        return $this->belongsTo('App\Models\Transaction');
    }
}
