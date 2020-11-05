<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Feedback extends Model
{
    public $timestamps = true;
    protected $fillable = [
        'sessionid', 'msisdn', 'name', 'message', 'status','subject', 'updated_at'
    ];
}
