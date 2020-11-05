<?php

namespace App\Http\Controllers;

use App\Confirmedsubscription;
use App\Subscriptionrequest;
use Carbon\Carbon;
use CreateSubscriptionsTable;
use Exception;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ApiController extends Controller
{
    public function SubscribeUser(Request $request)
    {
        $headers = getallheaders();
        if (!isset($headers['app_id'])) {
            return response()->json(["Code" => "403", "Message" => "Your Application is not authorized to use this API", "Data" => []], 403);
        }
        $rules = [
            'msisdn' => 'required',
            'offercode' => 'required'
        ];
        $validator = Validator::make($request->All(), $rules);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }
        if (!preg_match('/^(2547)([0-9]{8})$/', $request->msisdn)) {
            return response()->json('Msisdn Must be a valid Kenyan Phone Number (Starting with 2547), length of 12', 400);
        }
        try {
            Subscriptionrequest::insert([
                'msisdn'=>$request->msisdn,
                'offercode'=>$request->offercode,
                'created_at'=>Carbon::now(),
                'updated_at'=>Carbon::now()
            ]);
            return response()->json(['Status' => 000], 200);
        } catch (Exception $ex) {
            return response()->json(['Status' => 999], 500);
        }
    }
    public function confirmedsubscriptions(Request $request)
    {
        $rules = [
            'msisdn' => 'required',
            'offercode' => 'required',
            'status' => 'required'
        ];
        $validator = Validator::make($request->All(), $rules);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }
        if (!preg_match('/^(2547)([0-9]{8})$/', $request->msisdn)) {
            return response()->json('Msisdn Must be a valid Kenyan Phone Number (Starting with 2547), length of 12', 400);
        }
        try {
            $posted=$this->postsubscribed($request->msisdn,$request->offercode,$request->status);
            Confirmedsubscription::insert([
                'msisdn'=>$request->msisdn,
                'offercode'=>$request->offercode,
                'subscriptiontype'=>$request->status,
                'posted'=>$posted=='OK'?1:0,
                'created_at'=>Carbon::now(),
                'updated_at'=>Carbon::now()
            ]);
            return response()->json(['Status' => 000], 200);
        } catch (Exception $ex) {
            return response()->json(['Status' => 999], 500);
        }
    }
    function postsubscribed($msisdn,$offercode,$status){
        try {
            $apiurl = '';
            $client = new Client();
            $response = $client->request('POST', $apiurl, [
                'body' => json_encode(['msisdn' => $msisdn, 'offercode' => $offercode, 'status' => $status]),
                'headers' => ['Content-Type' => 'application/json'],
            ]);
            $response = json_decode($response->getBody(), true);
            return 'OK';
        } catch (Exception $ex) {
            return 'NOK';
        }
    }
   
}
