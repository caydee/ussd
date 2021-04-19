<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Prefix extends Model
{
    public $timestamps = true;
    protected $fillable = [
        'telco', 'prefix'
    ];
}
