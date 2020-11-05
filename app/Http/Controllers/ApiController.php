<?php

namespace App\Http\Controllers;

use App\Confirmedsubscription;
use App\Mpesatransaction;
use App\Mpesatransactions;
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
    public function payment(Request $request)
    {      
        $headers = getallheaders();
        if (!isset($headers['api_key'])) {
            return response()->json("Access to resource Forbidden", 403);
        }
        $apikey = $headers['api_key'];
        if ($apikey != '4e0bf5d2975c44c3b194aac300dae162') {
            return response()->json("Invalid API Key", 403);
        }
        $rules = array(
            'sender_phone'  => 'required',
            'transaction'  => 'required',
            'amount'  => 'required',
            'mpesa_code'  => 'required',
            'type'  => 'required',
            'origin' => 'required'
        );
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            $errors = $validator->getMessagebag()->toarray();
            $array = array_values($errors);
            $msg = '';
            for ($i = 1; $i <= sizeof($array); $i++) {
                $msg .= $array[$i - 1][0] . PHP_EOL;
            }
            return response()->json(['errors' =>  $msg], 403);
        }
        $p = Mpesatransaction::where('reference', $request->reference)->first();
        if (!$p) {
            Mpesatransaction::insert([
                'msisdn'  => $request->sender_phone,
                'account'  => $request->transaction,
                'amount'  => $request->amount,
                'reference'  => $request->mpesa_code,
                'mode'  => $request->type,
                'created_at' > Carbon::now(),
                'updated_at' > Carbon::now()
            ]);            
        } else {
            return response()->json('Duplicate Payment received', 400);
        }
        return response()->json('Payment received');
    }
}
