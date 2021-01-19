<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Session extends Model
{
    public $timestamps = false;
    protected $fillable = [
        'menus',
        'ussd_string',
        'ussd_level',
        'current_selection',
        'expected_input',
        'min_selection',
        'max_selection'
    ];
}
