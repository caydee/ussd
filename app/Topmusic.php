<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Topmusic extends Model
{
    public $timestamps = false;
    protected $fillable = [
        'menunumber',
        'name',
        'url',
        'date'
    ];
}
