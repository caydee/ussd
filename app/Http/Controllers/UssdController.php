<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class UssdController extends Controller
{
    public function Menus(Request $request){
        $response_string='CON Hello. Welcome to Standard Group PLC:'.PHP_EOL;
        $response_string.='Please enter your Name';
        return trim(strip_tags( $response_string));
    }
}
