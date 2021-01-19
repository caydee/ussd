<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Songofthehour extends Model
{
    public $timestamps = false;
    protected $fillable = [
        'menunumber',
        'name',
        'url',
        'date'
    ];
}
