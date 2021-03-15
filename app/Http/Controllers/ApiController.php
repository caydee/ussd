<?php

namespace App\Http\Controllers;

use App\Confirmedsubscription;
use App\Content;
use App\Feedback;
use App\Mpesatransaction;
use App\Mpesatransactions;
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
use Illuminate\Http\Request;
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
            'title' => 'required'
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
            'title' => $request->title
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
            'id' => 'required|integer|gt0',
            'ussdmenu' => 'required',
            'ussdlistnumber' => 'required',
            'title' => 'required'
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
                'title' => $request->title
            ]);
            return response()->json($content, 201);
        }
        return response()->json('Content with ID ' . $request->id . ' NOT FOUND', 404);
    }
}
