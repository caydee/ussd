<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class UssdController extends Controller
{
    public function Menus(Request $request){
        return trim(strip_tags("CON Menus Here"));
    }
}
