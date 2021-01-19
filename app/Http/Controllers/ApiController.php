<?php

namespace App\Http\Controllers;

use App\Confirmedsubscription;
use App\Feedback;
use App\Mpesatransaction;
use App\Mpesatransactions;
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
        if (!Request()->has('status')) {
            $status = '';
        } else {
            $status = $_GET['status'];
        }
        return $status == 0 ? Subscription::where('status', 0)->get() : ($status == 1 ? Subscription::where('status', 1)->get() : Subscription::all());
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
        if (!Request()->has('fromdate')) {
            return response()->json('From date required.');
        }
        if (!Request()->has('todate')) {
            return response()->json('To date required.');
        }
        $fromdate = $_GET['fromdate'];
        $todate = $_GET['todate'];

        return Session::wheredate('session_date', '>=', Carbon::parse($fromdate))->whereDate('session_date', '<=', Carbon::parse($todate))->get();
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
        if (!Request()->has('genre')) {
            return response()->json('To date required.');
        }
        $genre = $_GET['genre'];
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
                    'url' => $request->url,
                    'date' => Carbon::parse($request->date)
                ]);
                break;
            case "Topgospel":
                Topgospel::insert([
                    'menunumber' => $request->menunumber,
                    'name' => $request->name,
                    'url' => $request->url,
                    'date' => Carbon::parse($request->date)
                ]);
                break;
            case "Topmusic":
                Topmusic::insert([
                    'menunumber' => $request->menunumber,
                    'name' => $request->name,
                    'url' => $request->url,
                    'date' => Carbon::parse($request->date)
                ]);
                break;
        }
        return response()->json('Song Added to list.');
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
                    'name' => $request->name,
                    'url' => $request->url,
                    'date' => Carbon::parse($request->date)
                ]);
                break;
            case "Topgospel":
                Topgospel::where('id', $request->id)->update([
                    'menunumber' => $request->menunumber,
                    'name' => $request->name,
                    'url' => $request->url,
                    'date' => Carbon::parse($request->date)
                ]);
                break;
            case "Topmusic":
                Topmusic::where('id', $request->id)->update([
                    'menunumber' => $request->menunumber,
                    'name' => $request->name,
                    'url' => $request->url,
                    'date' => Carbon::parse($request->date)
                ]);
                break;
        }
        
        return response()->json('Song Edited.');
    }
}
