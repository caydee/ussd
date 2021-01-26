<?php

namespace App\Http\Controllers;

use App\Confirmedsubscription;
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
    public function GetSubscribers()
    {
        $headers = getallheaders();
        if (!isset($headers['api_key'])) {
            return response()->json("Access to resource Forbidden", 403);
        }
        $apikey = $headers['api_key'];
        if ($apikey != '4e0bf5d2975c44c3b194aac300dae162') {
            return response()->json("Invalid API Key", 403);
        }
        if (array_key_exists("status", $_GET)) {
            $status = $_GET['status'];
        } else {
            $status = 9;
        }
       $response=$status == 0 ? Subscription::where('status', 0)->get() : ($status == 1 ? Subscription::where('status', 1)->get() : Subscription::all());
        return response()->json($response->toArray());
    }
    public function GetSessions()
    {
        $headers = getallheaders();
        if (!isset($headers['api_key'])) {
            return response()->json("Access to resource Forbidden", 403);
        }
        $apikey = $headers['api_key'];
        if ($apikey != '4e0bf5d2975c44c3b194aac300dae162') {
            return response()->json("Invalid API Key", 403);
        }
        if (!array_key_exists("fromdate", $_GET)) {
            return response()->json('From date required.');
        }
        if (!array_key_exists("todate", $_GET)) {
            return response()->json('To date required.');
        }
       
        $fromdate = $_GET['fromdate'];
        $todate = $_GET['todate'];

        return response()->json(Session::wheredate('session_date', '>=', Carbon::parse($fromdate)->toDateString())
        ->whereDate('session_date', '<=', Carbon::parse($todate)->toDateString())->get()->toArray());
    }
    public function Songs()
    {
        $headers = getallheaders();
        if (!isset($headers['api_key'])) {
            return response()->json("Access to resource Forbidden", 403);
        }
        $apikey = $headers['api_key'];
        if ($apikey != '4e0bf5d2975c44c3b194aac300dae162') {
            return response()->json("Invalid API Key", 403);
        }
        if (!array_key_exists("genre", $_GET)) {
            return response()->json('Music Genre required.');
        }
        $genre = $_GET['genre'];
        return response()->json($this->getmusic($genre));
    }
    function getmusic($genre){
        switch ($genre) {
            case "Songofthehour":
                return Songofthehour::all();
                break;
            case "Topgospel":
                return Topgospel::all();
                break;
            case "Topmusic":
                return Topmusic::all();
                break;
        }
    }
    public function AddSongs(Request $request)
    {
        $headers = getallheaders();
        if (!isset($headers['api_key'])) {
            return response()->json("Access to resource Forbidden", 403);
        }
        $apikey = $headers['api_key'];
        if ($apikey != '4e0bf5d2975c44c3b194aac300dae162') {
            return response()->json("Invalid API Key", 403);
        }
        switch ($request->genre) {
            case "Songofthehour":
                Songofthehour::insert([
                    'menunumber' => $request->menunumber,
                    'name' => $request->name,
                    'genre' => $request->genre,
                    'url' => $request->url,
                    'date' => Carbon::parse($request->date)
                ]);
                break;
            case "Topgospel":
                Topgospel::insert([
                    'menunumber' => $request->menunumber,
                    'name' => $request->name,
                    'genre' => $request->genre,
                    'url' => $request->url,
                    'date' => Carbon::parse($request->date)
                ]);
                break;
            case "Topmusic":
                Topmusic::insert([
                    'menunumber' => $request->menunumber,
                    'name' => $request->name,
                    'genre' => $request->genre,
                    'url' => $request->url,
                    'date' => Carbon::parse($request->date)
                ]);
                break;
        }
        return response()->json($this->getmusic($request->genre));
    }
    public function EditSongs(Request $request)
    {
        $headers = getallheaders();
        if (!isset($headers['api_key'])) {
            return response()->json("Access to resource Forbidden", 403);
        }
        $apikey = $headers['api_key'];
        if ($apikey != '4e0bf5d2975c44c3b194aac300dae162') {
            return response()->json("Invalid API Key", 403);
        }
        switch ($request->genre) {
            case "Songofthehour":
                Songofthehour::where('id', $request->id)->update([
                    'menunumber' => $request->menunumber,
                    'genre' => $request->genre,
                    'name' => $request->name,
                    'url' => $request->url,
                    'date' => Carbon::parse($request->date)
                ]);
                break;
            case "Topgospel":
                Topgospel::where('id', $request->id)->update([
                    'menunumber' => $request->menunumber,
                    'genre' => $request->genre,
                    'name' => $request->name,
                    'url' => $request->url,
                    'date' => Carbon::parse($request->date)
                ]);
                break;
            case "Topmusic":
                Topmusic::where('id', $request->id)->update([
                    'menunumber' => $request->menunumber,
                    'genre' => $request->genre,
                    'name' => $request->name,
                    'url' => $request->url,
                    'date' => Carbon::parse($request->date)
                ]);
                break;
        }
        return response()->json($this->getmusic($request->genre));
    }
    public function GetServices(){
        $headers = getallheaders();
        if (!isset($headers['api_key'])) {
            return response()->json("Access to resource Forbidden", 403);
        }
        $apikey = $headers['api_key'];
        if ($apikey != '4e0bf5d2975c44c3b194aac300dae162') {
            return response()->json("Invalid API Key", 403);
        }
        return response()->json(Service::all()->toArray());
    }
    public function UpdateSubscriber(Request $request){
        $headers = getallheaders();
        if (!isset($headers['api_key'])) {
            return response()->json("Access to resource Forbidden", 403);
        }
        $apikey = $headers['api_key'];
        if ($apikey != '4e0bf5d2975c44c3b194aac300dae162') {
            return response()->json("Invalid API Key", 403);
        }
        Subscription::where([['msisdn','=',$request->msisdn],['offercode','=',$request->offercode]])->update(['status'=>$request->status]);
        return 0;
    }
}
