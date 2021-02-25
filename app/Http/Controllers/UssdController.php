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
        $ussdString = str_replace('*', '', $ussdString);
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
                        $this->update_session($session, $ussdString, $menu_items, 1, $min, $max, 'Betting Tips');
                        break;
                    case 11:
                        $response = $this->get_menus($session, 2, 2);
                        $menu_items = $response[0];
                        $min = $response[1];
                        $max = $response[2];
                        $this->update_session($session, $ussdString, $menu_items, 1, $min, $max, 'Wrong Number');
                        break;
                    case 12:
                        $response = $this->get_menus($session, 2, 3);
                        $menu_items = $response[0];
                        $min = $response[1];
                        $max = $response[2];
                        $this->update_session($session, $ussdString, $menu_items, 1, $min, $max, 'Adult in the Room');
                        break;
                    case 13:
                        $response = $this->get_menus($session, 2, 4);
                        $menu_items = $response[0];
                        $min = $response[1];
                        $max = $response[2];
                        $this->update_session($session, $ussdString, $menu_items, 1, $min, $max, 'Kesi Mashinani');
                        break;
                    case 14:
                        $response = $this->get_menus($session, 2, 5);
                        $menu_items = $response[0];
                        $min = $response[1];
                        $max = $response[2];
                        $this->update_session($session, $ussdString, $menu_items, 1, $min, $max, 'Situation Room');
                        break;
                    case 20:
                        $response = $this->get_menus($session, 2, 6);
                        $menu_items = $response[0];
                        $min = $response[1];
                        $max = $response[2];
                        $this->update_session($session, $ussdString, $menu_items, 1, $min, $max, 'Euro News');
                        break;
                }
            } else {
                $len = strlen($session->USSD_STRING);
                $selection = substr($ussdString,  $len);
                $response = $this->get_menus($session, $session->LEVEL + 1, $selection);
                $menu_items = $response[0];
                $min = $response[1];
                $max = $response[2];
                $title = $response[3];
                $this->update_session($session, $ussdString, $menu_items,  $selection, $min, $max, $title);
            }
        } else {
            //new
            $response = $this->get_menus('', 1, '');
            $menu_items = $response[0];
            $min = $response[1];
            $max = $response[2];
            $selection = $ussdString;

            Session::insert([
                'SESSION_ID' => $sessionId,
                'SERVICE_CODE' => $serviceCode,
                'MSISDN' => $msisdn,
                'USSD_STRING' => $ussdString,
                'TITLE' => 'Main Menu',
                'LEVEL' => 1,
                'SELECTION' =>  (int)$selection,
                'MENU' => $menu_items,
                'MIN_VAL' => $min,
                'MAX_VAL' => $max,
                'SESSION_DATE' => Carbon::now()
            ]);
        }
        return response($menu_items, 200)
            ->header('Content-Type', 'text/plain');
    }
    function update_session($session, $ussdstring, $menus, $selection, $min, $max, $header)
    {
        if ($header == '') {
            $header = $session->TITLE;
        }

        $session->update([
            'USSD_STRING' => $ussdstring,
            'SELECTION' =>  (int)$selection,
            'LEVEL' => $session->LEVEL + 1,
            'TITLE' => $header,
            'MENU' => $menus,
            'MIN_VAL' => (int)$min,
            'MAX_VAL' => (int) $max,
            'SESSION_DATE' => Carbon::now()
        ]);
    }
    function get_menus($session, $level, $selection)
    {
        $menu = '';
        $title = '';
        $min = 0;
        $max = 0;
        if ($session != null) {
            if ((int)$selection < (int)$session->MIN_VAL || (int)$selection > (int)$session->MIN_VAL) {
                //wrong selection, return previous Menu
                if ((int)$selection == 0) {
                    $menu = 'END Thank you for checking out Standard VAS. Dial *207# for more options.';
                    return [$menu, 0, 0];
                }
                if ((int)$selection == 99) {
                    $session->update([
                        'LEVEL' => $session->LEVEL - 1,
                    ]);
                    return [$session->MENU, $session->MIN_VAL, $session->MAX_VAL];
                }
            }
        }

        switch ($level) {
            case 1:
                $menu = 'CON Welcome to The Standard VAS. Select' . PHP_EOL;
                $menu .= '1. Betting Tips' . PHP_EOL;
                $menu .= '2. Wrong Number' . PHP_EOL;
                $menu .= '3. Adult in the Room' . PHP_EOL;
                $menu .= '4. Kesi Mashinani' . PHP_EOL;
                $menu .= '5. Situation Room' . PHP_EOL;
                $menu .= '6. Euro News' . PHP_EOL;
                $menu .= '0. Exit';
                $min = 1;
                $max = 6;
                $title = 'Main Menu';
                break;
            case 2:
                switch ($selection) {
                    case 1:
                        $menu = 'END Thank you for subscribing to Betting Tips.';
                        $title = 'Betting Tips';
                        $this->subscribe($session->MSISDN, '001006928145');
                        break;
                    case 2:
                        $menu = 'CON Wrong Number. Select' . PHP_EOL;
                        $menu .= '1. Content 1' . PHP_EOL;
                        $menu .= '2. Content 2' . PHP_EOL;
                        $menu .= '99. Back' . PHP_EOL;
                        $min = 1;
                        $max = 2;
                        $title = 'Wrong number';
                        break;
                    case 3:
                        //list available content
                        $menu = 'CON Adult in the Room. Select' . PHP_EOL;
                        $menu .= '1. Content 1' . PHP_EOL;
                        $menu .= '2. Content 2' . PHP_EOL;
                        $menu .= '99. Back' . PHP_EOL;
                        $min = 1;
                        $max = 2;
                        $title = 'Adult in the Room';
                        break;
                    case 4:
                        //list available content
                        $menu = 'CON Kesi Mashinani. Select' . PHP_EOL;
                        $menu .= '1. Kesi 1' . PHP_EOL;
                        $menu .= '2. Kesi 2' . PHP_EOL;
                        $menu .= '99. Back' . PHP_EOL;
                        $min = 1;
                        $max = 2;
                        $title = 'Kesi Mashinani';
                        break;
                    case 5:
                        //list available content
                        $menu = 'CON Situation Room. Select' . PHP_EOL;
                        $menu .= '1. Content 1' . PHP_EOL;
                        $menu .= '2. Content 2' . PHP_EOL;
                        $menu .= '99. Back' . PHP_EOL;
                        $min = 1;
                        $max = 2;
                        $title = 'Situation Room';
                        break;
                    case 6:
                        $menu = 'END Thank you for subscribing to Euro News.';
                        //subscribe user and terminate session
                        $title = 'Euro News';
                        $this->subscribe($session->MSISDN, '001006928145');
                        break;
                }
                break;
            case 3:

                switch ($session->TITLE) {
                    case 'Wrong number':
                        $this->subscribe($session->MSISDN, '001006928145');
                        break;
                    case 'Kesi Mashinani':
                        $this->subscribe($session->MSISDN, '001006928147');
                        break;
                    case 'Situation Room':
                        $this->subscribe($session->MSISDN, '001006928145');
                        break;
                    case 'Adult in the Room':
                        $this->subscribe($session->MSISDN, '001006928145');
                        break;
                }
                $menu = 'END Thank you for subscribing to ' . $session->TITLE . ' Content.';
                break;
            case 4:
                break;
            default:
        }

        return [$menu, $min, $max, $title];
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
