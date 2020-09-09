<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
    public $timestamps = true;

    protected $fillable = [
        'msisdn', 'language_id','ussdresult'
    ];}
