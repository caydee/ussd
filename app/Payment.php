<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    public $timestamps = true;
    protected $fillable = [
        'msisdn', 'account', 'amount', 'reference', 'origin', 'mode','created_at','updated_at'
    ];
}
