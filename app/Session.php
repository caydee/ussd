<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Session extends Model
{
    protected $fillable = [
        'telephone', 'session_id', 'service_code', 'userinput', 'previoususerinput', 'level'
    ];
}
