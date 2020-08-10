<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Log;

class UssdController extends Controller
{
    public function Menus(Request $request){


        log::info($request->input('USSD_STRING'));

        $sessionId   = $request->get('SESSION_ID');
        $serviceCode = $request->get('SERVICE_CODE');
        $phoneNumber = $request->get('MSISDN');
        $ussdString = $request->get('USSD_STRING');
        $text        = $request->get('text');

       // Log::info( $sessionId . ' - ' .$serviceCode . ' - ' .$phoneNumber . ' - ' .  $ussdString );


        if ($ussdString == "") {
            // first response when a user dials our ussd code
            $response  = "CON Welcome to  SG SMS \n";
            $response .= "1. Register \n";
            $response .= "2. More";
        }
        elseif ($ussdString == "1") {
            // when user respond with option one to register
            $response = "CON Choose Service \n";
            $response .= "1. SMS \n";
            $response .= "2. USSD";
        }
        elseif ($ussdString == "11") {
            // when use response with option
            $response = "CON Please enter your name";
        }
        elseif ($ussdString == "12") {
            // when use response with option
            $response = "CON Enter your query";
        }
        elseif (strlen($ussdString)>2 && substr($ussdString,0,2)=='11') {
            $response = "END Thank you";
        }
        elseif (strlen($ussdString)>2 && substr($ussdString,0,2)=='12') {
            $response = "END Thank you.Dont call us, we will call you";
        }       
        else {
            // save data in the database
            $response = "END Thank you for contacting SG VAS Platform.";
        }
        // send your response back to the API
        //header('Content-type: text/plain');
        $return=Response::make($response,200);
        $return->header('Content-Type', 'text/plain');
        echo $return;
    }
}
