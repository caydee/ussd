<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Session extends Model
{
    public $timestamps = false;
    protected $fillable = [
        'SESSION_ID',
        'SERVICE_CODE',
        'MSISDN',
        'USSD_STRING',
        'LEVEL',
        'MENU',
        'SELECTION',
        'MIN_VAL',
        'MAX_VAL',
        'SESSION_DATE'
    ];
}
