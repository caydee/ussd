<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class UssdController extends Controller
{
    public function Menus(Request $request){

        $sessionId   = $request->get('sessionId');
        $serviceCode = $request->get('serviceCode');
        $phoneNumber = $request->get('phoneNumber');
        $text        = $request->get('text');
        $ussd_string_exploded = explode("*", $text);

        $level = count($ussd_string_exploded);

        if ($text == "") {
            // first response when a user dials our ussd code
            $response  = "CON Welcome to  SG SMS \n";
            $response .= "1. Register \n";
            $response .= "2. More";
        }
        elseif ($text == "1") {
            // when user respond with option one to register
            $response = "CON Choose Service \n";
            $response .= "1. SMS \n";
            $response .= "2. USSD";
        }
        elseif ($text == "1*1") {
            // when use response with option django
            $response = "CON Please enter your first name";
        }
        elseif ($ussd_string_exploded[0] == 1 && $ussd_string_exploded[1] == 1 && $level == 3) {
            $response = "CON Please enter your last name";
        }
        elseif ($ussd_string_exploded[0] == 1 && $ussd_string_exploded[1] == 1 && $level == 4) {
            $response = "CON Please enter your email";
        }
        elseif ($ussd_string_exploded[0] == 1 && $ussd_string_exploded[1] == 1 && $level == 5) {
            // save data in the database
            $response = "END Your data has been captured successfully! Thank you for registering forSG VAS platform.";
        }
        elseif ($text == "1*2") {
            // when use response with option Laravel
            $response = "CON Please enter your first name. ";
        }
        elseif ($ussd_string_exploded[0] == 1 && $ussd_string_exploded[1] == 2 && $level == 3) {
            $response = "CON Please enter your last name";
        }
        elseif ($ussd_string_exploded[0] == 1 && $ussd_string_exploded[1] == 2 && $level == 4) {
            $response = "CON Please enter your email";
        }
        elseif ($ussd_string_exploded[0] == 1 && $ussd_string_exploded[1] == 2 && $level == 5) {
            // save data in the database
            $response = "END Your data has been captured successfully! Thank you for registering for SG VAS Platform.";
        }
        elseif ($text == "2") {
            // Our response a user respond with input 2 from our first level
            $response = "END Thank you for your response.";
        }
        // send your response back to the API
        header('Content-type: text/plain');
        echo $response;
    }
}
