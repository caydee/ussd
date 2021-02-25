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

        $menu_items = '';
        $msisdn = $_GET['MSISDN'];
        $serviceCode = $_GET['SERVICE_CODE'];
        $ussdString = $_GET['USSD_STRING'];
        $sessionId = $_GET['SESSION_ID'];
        $selection = '';

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

        $session = Session::where('SESSION_ID', $sessionId)->first();
        if ($session) {
            //ongoing
            //get the selection
            if (strlen($ussdString) == 2 &&  $ussdString == $session->SELECTION &&  $session->LEVEL == 1) {
                //shortcuts
                switch ((int) $ussdString) {
                    case 10:
                        $response = $this->get_menus($session, 2, 1);
                        $menu_items = $response[0];
                        $min = $response[1];
                        $max = $response[2];
                        $this->update_session($session, $ussdString, $menu_items, 1, $min, $max);
                        break;
                    case 11:
                        $response = $this->get_menus($session, 2, 2);
                        $menu_items = $response[0];
                        $min = $response[1];
                        $max = $response[2];
                        $this->update_session($session, $ussdString, $menu_items, 1, $min, $max);
                        break;
                    case 12:
                        $response = $this->get_menus($session, 2, 3);
                        $menu_items = $response[0];
                        $min = $response[1];
                        $max = $response[2];
                        $this->update_session($session, $ussdString, $menu_items, 1, $min, $max);
                        break;
                    case 13:
                        $response = $this->get_menus($session, 2, 4);
                        $menu_items = $response[0];
                        $min = $response[1];
                        $max = $response[2];
                        $this->update_session($session, $ussdString, $menu_items, 1, $min, $max);
                        break;
                    case 14:
                        $response = $this->get_menus($session, 2, 5);
                        $menu_items = $response[0];
                        $min = $response[1];
                        $max = $response[2];
                        $this->update_session($session, $ussdString, $menu_items, 1, $min, $max);
                        break;
                    case 20:
                        $response = $this->get_menus($session, 2, 6);
                        $menu_items = $response[0];
                        $min = $response[1];
                        $max = $response[2];
                        $this->update_session($session, $ussdString, $menu_items, 1, $min, $max);
                        break;
                }
            }
        } else {
            //new
            $menu_items = $this->get_menus(null, 1, null);
            $selection = $ussdString;
            Session::insert([
                'SESSION_ID' => $sessionId,
                'SERVICE_CODE' => $serviceCode,
                'MSISDN' => $msisdn,
                'USSD_STRING' => $ussdString,
                'LEVEL' => 1,
                'SELECTION' =>  $selection,
                'MENU' => $menu_items,
                'MIN_VAL' => 1,
                'MAX_VAL' => 6,
                'SESSION_DATE' => Carbon::now()
            ]);
        }
        return response($menu_items, 200)
            ->header('Content-Type', 'text/plain');
    }
    function update_session($session, $ussdstring, $menus, $selection, $min, $max)
    {
        $session->update([
            'USSD_STRING' => $ussdstring,
            'SELECTION' =>  $selection,
            'LEVEL' => $session + 1,
            'MENU' => $menus,
            'MIN_VAL' => $min,
            'MAX_VAL' => $max,
            'SESSION_DATE' => Carbon::now()
        ]);
    }
    function get_menus($session, $level, $selection)
    {
        $menu = '';
        $min = 0;
        $max = 0;

        switch ($level) {
            case 1:
                $menu = 'Welcome to The Standard VAS. Select' . PHP_EOL;
                $menu .= '1. For Betting Tips' . PHP_EOL;
                $menu .= '2. For Wrong Number' . PHP_EOL;
                $menu .= '3. For Adult in the Room' . PHP_EOL;
                $menu .= '4. For Kesi Mashinani' . PHP_EOL;
                $menu .= '5. For Situation Room' . PHP_EOL;
                $menu .= '6. For Euro News' . PHP_EOL;
                $menu .= '0. To Exit';
                $min = 1;
                $max = 6;
                break;
            case 2:
                if ((int)$selection < 1 || (int)$selection > 6) {
                    //wrong selection, return previous Menu

                }
                switch ($selection) {
                    case 1:
                        $menu = 'END Thank you for subscribing to Betting Tips.';
                        $this->subscribe($session->MSISDN, '001006928145');
                        break;
                    case 2:
                        //list available content
                        break;
                    case 3:
                        //list available content
                        break;
                    case 4:
                        //list available content
                        break;
                    case 5:
                        //list available content
                        break;
                    case 6:
                        $menu = 'END Thank you for subscribing to Eur News.';
                        //subscribe user and terminate session
                        $this->subscribe($session->MSISDN, '001006928145');
                        break;
                }
                break;
            case 3:
                break;
            case 4:
                break;
            default:
        }
        return [$menu, $min, $max];
    }


    function subscribe($telephone, $offercode)
    {
        $apiurl = 'https://ktnkenya.com/vas/public/api/SubscribeUser';
        $payload = [
            'msisdn' => $telephone,
            'offercode' => $offercode
        ];
        $client = new Client();
        $response = $client->request('POST', $apiurl, [
            'body' => json_encode($payload),
            'headers' => ['Content-Type' => 'application/json', 'app_key' => '12345'],
        ]);
        return 0;
    }
}
