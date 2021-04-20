<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Airtimerequest extends Model
{
    public $timestamps = true;
    protected $fillable = [
        'session_id', 'msisdn', 'creditphone', 'amount', 'timein', 'status','mpesa_account','mpesa_confirmed'
    ];
}
