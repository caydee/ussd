<?php

namespace App\Http\Controllers;

use App\Feedback;
use App\Menu;
use App\Service;
use App\Session;
use App\Sessionlog;
use App\Songofthehour;
use App\Subscriber;
use App\Subscription;
use App\Topgospel;
use App\Topmusic;
use Carbon\Carbon;
use Exception;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Log;
use PhpParser\Node\Stmt\Return_;

class UssdController extends Controller
{
    public function Request(Request $request)
    {

        $menu_level = 0;
        $menu_items = '';
        $msisdn = $_GET['MSISDN'];
        $serviceCode = $_GET['SERVICE_CODE'];
        $ussdString = $_GET['USSD_STRING'];
        $sessionId = $_GET['SESSION_ID'];
        //Log the session
        $sess = Sessionlog::where('session_id', $sessionId)->first();
        if (!$sess) {
            Sessionlog::insert([
                'session_id' => $sessionId,
                'msisdn' => $msisdn,
                'ussd_string' => $ussdString,
                'service_code' => $serviceCode
            ]);
        }
        if ($serviceCode == '*395#') {
            //get menus from moobifun and return to safaricom
            $apiurl = 'http://standardmedia-ussd.moobifun.com/ubc/ussdgtw/standardmedia';
            $client = new Client();
            $response = $client->request('GET', $apiurl, [
                'headers' => ['Content-Type' => 'application/json'],
                'query' => [
                    'msisdn' => $msisdn, 'servicecode' => 395,
                    'ussdstring' => $ussdString, 'sessionid' => $sessionId
                ]
            ]);
            $body = $response->getBody();
            if (trim($body) == '') {
                return response('END This service is currently under maintenance. Please try again later.', 200)
                    ->header('Content-Type', 'text/plain');
            }
            return response($body, 200)
                ->header('Content-Type', 'text/plain');
        }
        $ussdString = trim(str_replace('*', '', $ussdString));
        $session = Session::where('session_id', $sessionId)->first();
        if (!$session) {
            //new session
            $menu_items = $this->MainMenu();
            Session::insert(
                [
                    'session_id' => $sessionId,
                    'msisdn' => $msisdn,
                    'ussd_string' => $ussdString,
                    'service_code' => $serviceCode,
                    'min_selection' => 1,
                    'max_selection' => 7,
                    'menus' => $menu_items,
                    'ussd_level' => 1,
                    'expected_input' => 0
                ]
            );
        } else {
            //continuing session
            $menu_level = $session->ussd_level + 1;
            $len = strlen($session->ussd_string);
            $userinput = substr($ussdString, $len);
            if ($session->expected_input == 0) {
                if ((int)$userinput < (int)$session->min_selection || (int)$userinput > (int)$session->max_selection) {
                    $menu_items = $this->MainMenu();
                    $session->update(
                        [
                            'min_selection' => 1,
                            'max_selection' => 7,
                            'menus' => $menu_items,
                            'ussd_level' => 1,
                            'expected_input' => 0,
                            'current_selection' => $userinput,
                            'ussd_string' => $ussdString
                        ]
                    );
                    return response('CON Invalid Selection.' . PHP_EOL .  $menu_items, 200)
                        ->header('Content-Type', 'text/plain');
                }
            }
            //expecting a string input - Move to the next
            //load next menus
            switch ($menu_level) {
                case 2:
                    if ((int)$userinput == 1) {
                        //list song of hour
                        $res = $this->getsongs('Songofhour');
                        $menu_items = $res[0];
                        $session->update(
                            [
                                'min_selection' => 1,
                                'max_selection' => $res[1],
                                'menus' => $menu_items,
                                'ussd_level' => $menu_level,
                                'expected_input' => 0,
                                'current_selection' => $userinput,
                                'ussd_string' => $ussdString
                            ]
                        );
                    }
                    if ((int)$userinput == 2) {
                        //ask user to subscribe
                        $sub = $this->checksubscripton('Life Quotes', $msisdn, 'QUOTES') == 0 ? 'Subscribe to Life Quotes' : 'Unsubscribe';
                        $menu_items = 'CON Confirm' . PHP_EOL;
                        $menu_items = '1. ' . $sub . PHP_EOL;
                        $menu_items .= '2. Back' . PHP_EOL;
                        $session->update(
                            [
                                'min_selection' => 1,
                                'max_selection' => 2,
                                'menus' => $menu_items,
                                'ussd_level' => $menu_level,
                                'expected_input' => 0,
                                'current_selection' => $userinput,
                                'ussd_string' => $ussdString
                            ]
                        );
                    }
                    if ((int)$userinput == 3) {
                        //list top gospel songs
                        $res = $this->getsongs('Topgospel');
                        //  return $res;
                        $menu_items = $res[0];
                        $session->update(
                            [
                                'min_selection' => 1,
                                'max_selection' => $res[1],
                                'menus' => $menu_items,
                                'ussd_level' => $menu_level,
                                'expected_input' => 0,
                                'current_selection' => $userinput,
                                'ussd_string' => $ussdString
                            ]
                        );
                    }
                    if ((int)$userinput == 4) {
                        $res = $this->getsongs('Topmusic');
                        $menu_items = $res[0];
                        $session->update(
                            [
                                'min_selection' => 1,
                                'max_selection' => $res[1],
                                'menus' => $menu_items,
                                'ussd_level' => $menu_level,
                                'expected_input' => 0,
                                'current_selection' => $userinput,
                                'ussd_string' => $ussdString
                            ]
                        );
                    }
                    if ((int)$userinput == 5) {
                        //ask user to subscribe to funny jokes
                        //ask user to subscribe
                        $sub = $this->checksubscripton('Funny Jokes', $msisdn, 'JOKES') == 0 ? 'Subscribe to Funny Jokes' : 'Unsubscribe';
                        $menu_items = 'CON Confirm' . PHP_EOL;
                        $menu_items = '1. ' . $sub . PHP_EOL;
                        $menu_items .= '2. Back' . PHP_EOL;
                        $session->update(
                            [
                                'min_selection' => 1,
                                'max_selection' => 2,
                                'menus' => $menu_items,
                                'ussd_level' => $menu_level,
                                'expected_input' => 0,
                                'current_selection' => $userinput,
                                'ussd_string' => $ussdString
                            ]
                        );
                    }
                    if ((int)$userinput == 6) {
                        //ask user to subscribe to News
                        //ask user to subscribe
                        $sub = $this->checksubscripton('Breaking News', $msisdn, 'NEWS') == 0 ? 'Subscribe to Breaking News' : 'Unsubscribe';
                        $menu_items = 'CON Confirm' . PHP_EOL;
                        $menu_items = '1. ' . $sub . PHP_EOL;
                        $menu_items .= '2. Back' . PHP_EOL;
                        $session->update(
                            [
                                'min_selection' => 1,
                                'max_selection' => 2,
                                'menus' => $menu_items,
                                'ussd_level' => $menu_level,
                                'expected_input' => 0,
                                'current_selection' => $userinput,
                                'ussd_string' => $ussdString
                            ]
                        );
                    }
                    if ((int)$userinput == 7) {
                        //ask user to subscribe to Sports
                        //ask user to subscribe
                        $sub = $this->checksubscripton('Sports News', $msisdn, 'SPORTS') == 0 ? 'Subscribe to Sports News' : 'Unsubscribe';
                        $menu_items = 'CON Confirm' . PHP_EOL;
                        $menu_items .= '1. ' . $sub . PHP_EOL;
                        $menu_items .= '2. Back' . PHP_EOL;
                        $session->update(
                            [
                                'min_selection' => 1,
                                'max_selection' => 2,
                                'menus' => $menu_items,
                                'ussd_level' => $menu_level,
                                'expected_input' => 0,
                                'current_selection' => $userinput,
                                'ussd_string' => $ussdString
                            ]
                        );
                    }
                    break;
                case 3:
                    //
                    if ((int)$session->current_selection == 1) {
                        //song of the hour selected    
                        //get top song chosen
                        $song = Songofthehour::where('menunumber', (int)$userinput)->first();
                        $s = $this->checksubscripton('Song of the Hour', $msisdn, $song->name);
                        $this->SubscribeUser($msisdn, 'Song of the Hour', $song->name, $s);
                        $menu_items = 'END Thank you for subscribing to Song of the hour (' . $song->name . '). In case you wanna change your subscription, please dial *207#';
                    }
                    if ((int)$session->current_selection == 2) {
                        //subscription to life quotes
                        if ((int)$userinput == 2) {
                            $menu_items = $this->MainMenu();
                            $session->update(
                                [
                                    'min_selection' => 1,
                                    'max_selection' => 6,
                                    'menus' => $menu_items,
                                    'ussd_level' => 1,
                                    'expected_input' => 0,
                                    'current_selection' => $userinput,
                                    'ussd_string' => $ussdString
                                ]
                            );
                        } else {
                            $s = $this->checksubscripton('Life Quotes', $msisdn, 'QUOTES');
                            $this->SubscribeUser($msisdn, 'Life Quotes', 'QUOTES', $s);
                            if ($s == 0) {
                                $menu_items = 'END Thank you for subscribing to Life Quotes. In case you wanna change your subscription, please dial *207#';
                            } else {
                                $menu_items = 'END You have successfully unsubscribed from Life Quotes. Dial *207# for more options';
                            }
                        }
                    }
                    if ((int)$session->current_selection == 3) {
                        //Top gospel selected
                        $song = Topgospel::where('menunumber', (int)$userinput)->first();
                        $s = $this->checksubscripton('Top Gospel Songs', $msisdn, $song->name);
                        $this->SubscribeUser($msisdn, 'Top Gospel Songs', $song->name, $s);
                        $menu_items = 'END Thank you for subscribing to Top Gospel Songs (' . $song->name . '). In case you wanna change your subscription, please dial *207#';
                    }
                    if ((int)$session->current_selection == 4) {
                        //Top Music selected
                        $song = Topmusic::where('menunumber', (int)$userinput)->first();
                        $s = $this->checksubscripton('Top Music', $msisdn, $song->name);
                        $this->SubscribeUser($msisdn, 'Top Music', $song->name, $s);
                        $menu_items = 'END Thank you for subscribing to Top Music (' . $song->name . '). In case you wanna change your subscription, please dial *207#';
                    }
                    if ((int)$session->current_selection == 5) {
                        //subscription to funny jokes
                        if ((int)$userinput == 2) {
                            $menu_items = $this->MainMenu();
                            $session->update(
                                [
                                    'min_selection' => 1,
                                    'max_selection' => 6,
                                    'menus' => $menu_items,
                                    'ussd_level' => 1,
                                    'expected_input' => 0,
                                    'current_selection' => $userinput,
                                    'ussd_string' => $ussdString
                                ]
                            );
                        } else {
                            $s = $this->checksubscripton('Funny Jokes', $msisdn, 'JOKES');
                            $this->SubscribeUser($msisdn, 'Funy Jokes', 'JOKES', $s);
                            if ($s == 0) {
                                $this->SubscribeUser($msisdn, 'Top Music', 'JOKES', $s);
                                $menu_items = 'END Thank you for subscribing to Funny Jokes. In case you wanna change your subscription, please dial *207#';
                            } else {
                                $menu_items = 'END You have successfully unsubscribed from Funny Jokes. Dial *207# for more options';
                            }
                        }
                    }
                    if ((int)$session->current_selection == 6) {
                        //subscription to breaking news
                        if ((int)$userinput == 2) {
                            $menu_items = $this->MainMenu();
                            $session->update(
                                [
                                    'min_selection' => 1,
                                    'max_selection' => 6,
                                    'menus' => $menu_items,
                                    'ussd_level' => 1,
                                    'expected_input' => 0,
                                    'current_selection' => $userinput,
                                    'ussd_string' => $ussdString
                                ]
                            );
                        } else {
                            $s = $this->checksubscripton('Breaking News', $msisdn, 'NEWS');
                            $this->SubscribeUser($msisdn, 'Breaking News', 'NEWS', $s);
                            if ($s == 0) {
                                $menu_items = 'END Thank you for subscribing to Breaking News. In case you wanna change your subscription, please dial *207#';
                            } else {
                                $menu_items = 'END You have successfully unsubscribed from Breaking News. Dial *207# for more options';
                            }
                        }
                    }
                    if ((int)$session->current_selection == 7) {
                        //subscription to sports
                        if ((int)$userinput == 2) {
                            $menu_items = $this->MainMenu();
                            $session->update(
                                [
                                    'min_selection' => 1,
                                    'max_selection' => 6,
                                    'menus' => $menu_items,
                                    'ussd_level' => 1,
                                    'expected_input' => 0,
                                    'current_selection' => $userinput,
                                    'ussd_string' => $ussdString
                                ]
                            );
                        } else {
                            $s = $this->checksubscripton('Sports News', $msisdn, 'SPORTS');
                            $this->SubscribeUser($msisdn, 'Sports News', 'SPORTS', $s);
                            if ($s == 0) {
                                $menu_items = 'END Thank you for subscribing to Sports News. In case you wanna change your subscription, please dial *207#';
                            } else {
                                $menu_items = 'END You have successfully unsubscribed from Sports News. Dial *207# for more options';
                            }
                        }
                    }
                    break;
                default:
            }
        }
        return response($menu_items, 200)
            ->header('Content-Type', 'text/plain');
    }

    function getsongs($genre)
    {
        $songs = Songofthehour::orderby('menunumber', 'asc')->get();
        $list = "CON Songs of the Hour" . PHP_EOL;
        if ($genre == 'Topgospel') {
            $songs = Topgospel::orderby('menunumber', 'asc')->get();
            $list = "CON Top Gospel Songs Tracks" . PHP_EOL;
        }
        if ($genre == 'Topmusic') {
            $songs = Topmusic::orderby('menunumber', 'asc')->get();
            $list = "CON Top Music Tracks" . PHP_EOL;
        }

        foreach ($songs as $song) {
            $list .= $song->menunumber . '. ' . $song->name . PHP_EOL;
        }

        return [$list, count($songs)];
    }

    function MainMenu()
    {
        $menu = 'Welcome to the Standard VAS' . PHP_EOL;
        $menu .= '1. Song of The hour' . PHP_EOL;
        $menu .= '2. Life Quotes' . PHP_EOL;
        $menu .= '3. Top Gospel Songs Songs' . PHP_EOL;
        $menu .= '4. Top Music' . PHP_EOL;
        $menu .= '5. Funny Jokes' . PHP_EOL;
        $menu .= '6. Breaking News' . PHP_EOL;
        $menu .= '7. Sports News' . PHP_EOL;
        return $menu;
    }
    function SubscribeUser($msisdn, $service, $song, $state)
    {
        $s = Service::where('name', $service)->first();
        if ($s) {
            if ($state == 0) { //not subscribed
                $subs = Subscription::where('offercode', $s->offercode)->where('msisdn', $msisdn)->where('song', $song)->first();
                if ($subs) {
                    $subs->update([
                        'status' => 1,
                        'subscriptiondate' => Carbon::now()
                    ]);
                } else {
                    Subscription::insert([
                        'service' => $s->name,
                        'song' => $song,
                        'offercode' => $s->offercode,
                        'msisdn' => $msisdn,
                        'status' => 1,
                    ]);
                }
            } else {
                Subscription::insert([
                    'service' => $s->name,
                    'song' => $song,
                    'offercode' => $s->offercode,
                    'msisdn' => $msisdn,
                    'status' => 1,
                ]);
            }
            //call subscription API
        }
        return 0;
    }
    function checksubscripton($service, $msisdn, $song)
    {
        $subscribed = Subscription::where([['service', '=', $service], ['msisdn', '=', $msisdn], ['song', '=', $song]])->first();
        if ($subscribed) {
            return $subscribed->status;
        }
        return 0;
    }
}
