<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
    public $timestamps = false;
    protected $fillable = [
        'service',
        'offercode',
        'msisdn',
        'status',
        'subscriptiondate'
    ];
}
