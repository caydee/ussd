<?php

namespace App\Http\Controllers;

use App\Airtimerequest;
use App\Confirmedsubscription;
use App\Content;
use App\Feedback;
use App\Mpesatransaction;
use App\Mpesatransactions;
use App\Payment;
use App\Prefix;
use App\Service;
use App\Subscription;
use App\Subscriptionrequest;
use Carbon\Carbon;
use App\Session;
use App\Songofthehour;
use App\Topgospel;
use App\Topmusic;
use CreateSubscriptionsTable;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\RetryMiddleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class ApiController extends Controller
{
    public function content()
    {
        $headers = getallheaders();
        if (!isset($headers['api_key'])) {
            return response()->json("Access to resource Forbidden", 403);
        }
        $apikey = $headers['api_key'];
        if ($apikey != '4e0bf5d2975c44c3b194aac300dae162') {
            return response()->json("Invalid API Key", 403);
        }
        return response()->json(Content::all());
    }
    public function CreateContent(Request $request)
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
            'ussdmenu' => 'required',
            'ussdlistnumber' => 'required',
            'title' => 'required',
            'location' => 'required',
        );
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            $errors = $validator->getMessagebag()->toarray();
            $array = array_values($errors);
            $msg = '';
            for ($i = 1; $i <= sizeof($array); $i++) {
                $msg .= $array[$i - 1][0] . PHP_EOL;
            }
            return response()->json(['errors' =>  $msg], 500);
        }
        $content = Content::create([
            'ussdmenu' => $request->ussdmenu,
            'ussdlistnumber' => $request->ussdlistnumber,
            'title' => $request->title,
            'location' => $request->location
        ]);
        return response()->json($content, 201);
    }
    public function EditContent(Request $request)
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
            'id' => 'required|integer',
            'ussdmenu' => 'required',
            'ussdlistnumber' => 'required',
            'title' => 'required',
            'location' => 'required',
        );
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            $errors = $validator->getMessagebag()->toarray();
            $array = array_values($errors);
            $msg = '';
            for ($i = 1; $i <= sizeof($array); $i++) {
                $msg .= $array[$i - 1][0] . PHP_EOL;
            }
            return response()->json(['errors' =>  $msg], 500);
        }
        $content = Content::where('id', $request->id)->first();
        if ($content) {
            $content->update([
                'ussdmenu' => $request->ussdmenu,
                'ussdlistnumber' => $request->ussdlistnumber,
                'title' => $request->title,
                'location' => $request->location
            ]);
            return response()->json($content, 201);
        }
        return response()->json('Content with ID ' . $request->id . ' NOT FOUND', 404);
    }
    function postairtime()
    {
        $records = Airtimerequest::where([['status', '=', 1], ['mpesa_confirmed', '=', 1]])->get();

        if (sizeof($records) < 1) return;

        $payload = [];
        foreach ($records as $record) {

            $prefix = Prefix::where('prefix', substr($record->creditphone, 0, 6))->first();
            array_push($payload, [
                "msisdn" => $record->creditphone,
                "amount" => (int) $record->amount,
                "network" => $prefix ? $prefix->telco : 'Safaricom'
            ]);
        }
        Airtimerequest::wherein('id', $records->pluck('id'))->update(['status' => 2]);

        //post
        $api_url = 'https://ktnkenya.com/vas/public/api/request_airtime';
        // $api_url = 'http://sms.test/api/request_airtime';
        $client = new Client();
        // $testapikey = 'af3b0666-397b-4523-b093-a4639af063da';
        $liveapikey = '92560ef9-d9bb-46dc-b1b1-c2649f6256b4';
        $req = ['creditrequest' => $payload];
        $response = $client->request(
            'POST',
            $api_url,
            [
                'headers' => [
                    'Content-Type' => ' application/json',
                    'email' => 'ussd@standardmeedia.co.ke',
                    'api_key' =>  $liveapikey,
                ],
                'body' => json_encode($req),
            ]
        );
        Log::alert('Airtime Response: ' . $response->getBody());
        return 0;
    }
    public function prefixes()
    {
        Prefix::query()->truncate();
        $safprefix = [
            '254700', '254701', '254702', '254703', '254704', '254705', '254706', '254707', '254708', '254709',
            '254710', '254711', '254712', '254713', '254714', '254715', '254716', '254717', '254718', '254719',
            '254720', '254721', '254722', '254723', '254724', '254725', '254726', '254727', '254728', '254729',
            '254740', '254741', '254742', '254743', '254745', '254746', '254748',
            '254757', '254758', '254759',
            '254768', '2547699',
            '254790', '254791', '254792', '254793', '254794', '254795', '254796', '254797', '254798', '254799',
            '254112', '254113', '254114', '254115'
        ];
        $equitelprefix = ['254763', '254764', '254765', '254766'];
        $telcomprefix = ['254770', '254771', '254772', '254773', '254774', '254775', '254776', '254777', '254778', '254779'];

        $airtelprefix = [
            '254100', '254101', '254102',
            '254730', '254731', '254732', '254733', '254734', '254735', '254736', '254737', '254738', '254739',
            '254750', '254751', '254752', '254753', '254754', '254755', '254756',
            '254785', '254786', '254787', '254788', '254789'
        ];


        foreach ($safprefix as $p) {
            Prefix::create([
                'telco' => 'Safaricom', 'prefix' => $p
            ]);
        }
        foreach ($airtelprefix as $p) {
            Prefix::create([
                'telco' => 'Airtel', 'prefix' => $p
            ]);
        }
        foreach ($equitelprefix as $p) {
            Prefix::create([
                'telco' => 'Equitel', 'prefix' => $p
            ]);
        }
        foreach ($telcomprefix as $p) {
            Prefix::create([
                'telco' => 'Telkom', 'prefix' => $p
            ]);
        }
        Prefix::create([
            'telco' => 'Faiba', 'prefix' => $p
        ]);

        return 0;
    }

    public function mpesa_callback(Request $payment)
    {

        // '{"msisdn":"254720076063","amount":"1_00","orderid":"4","type":"Pay_Bill","transactioncode":"PDK21RQKVC","timecomplete":"2021-04-20_13:34:03","origin":"mawingu"}' => NULL,request = json_decode((file_get_contents("php://input")), true);
        log::alert($payment);

        $p = Payment::where('reference', $payment['transactioncode'])->first();
        if (!$p) {
            Payment::insert([
                'msisdn'  => $payment['msisdn'],
                'account'  => 'AIR' . $payment['orderid'],
                'amount'  => (float)str_replace('_', '.', $payment['amount']),
                'reference'  => $payment['transactioncode'],
                'origin'  => $payment['origin'],
                'mode'  => $payment['type']
            ]);
            $req = Airtimerequest::where([['mpesa_account', '=', 'AIR' . $payment['orderid']], ['amount', '=', (float)str_replace('_', '.', $payment['amount'])]])->first();
            if ($req) {
                $req->update(['mpesa_confirmed' => 1]);
            }
            $this->postairtime();
        } else {
            return response()->json('Duplicate Payment received', 400);
        }
        return response()->json('Payment received');
    }
}
