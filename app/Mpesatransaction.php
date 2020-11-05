<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Mpesatransaction extends Model
{
    public $timestamps = true;
    protected $fillable = [
        'msisdn', 'account','amount','reference','status','mode','updated_at'
    ];
}


