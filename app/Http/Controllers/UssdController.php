<?php

namespace App\Http\Controllers;

use App\Airtimerequest;
use App\Allsessions;
use App\Content;
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
        // $url = $request->fullUrl();
        // Log::alert($url);
        $allsess = Allsessions::where('SESSION_ID', $sessionId)->first();
        if (!$allsess) {
            Allsessions::create(
                [
                    'SESSION_ID' => $sessionId,
                    'SERVICE_CODE' => $serviceCode,
                    'MSISDN' => $msisdn,
                    'created_at' => now(),
                    'updated_at' => now()
                ]
            );
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
        if ($serviceCode == '*207#' && substr($ussdString,0,3)=='555') {
            //get menus from mSafiri
            $apiurl = 'https://portal.msafari.co.ke/ussd';
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
        $bundled = $ussdString;
        $ussdString = str_replace('*', '', $ussdString);
        $session = Session::where('SESSION_ID', $sessionId)->first();
        if ($session) {
            if (strlen($ussdString) > 2 &&  $ussdString == $session->SELECTION &&  $session->LEVEL == 1) {
                //airtime
                $data = explode('*', $bundled);
                $telephone = $data[0];
                $telephone = str_replace(' ', '', $telephone);
                $telephone = str_replace('-', '', $telephone);
                $telephone = $this->FormatTelephone($telephone);
                $amount = $data[1];
                if (strlen($telephone) != 12) {
                    return response('END Sorry, you have entered an invalid telephone. Please try again. (*207*telephone*amount#).', 200)
                        ->header('Content-Type', 'text/plain');
                }
                if ((int)$amount < 1 || (int)$amount > 10000) {
                    return response('END Sorry, you have entered an invalid amount. Please try again. (*207*telephone*amount#).', 200)
                        ->header('Content-Type', 'text/plain');
                }
                //send back the mpesa push
                $menu = 'END Thank you. Please confirm your airtime purchase of KES.' . $amount . ' for telephone number: ' . $telephone . ' by supplying your M-Pesa pin on your phone.';
                $air = Airtimerequest::create([
                    'session_id' => $sessionId,
                    'msisdn' =>  $msisdn,
                    'creditphone' => $telephone,
                    'amount' => (int)$amount,
                    'status' => 1,
                    'updated_at' => Carbon::now(),
                    'created_at' => Carbon::now()
                ]);
                $air->update(['mpesa_account' => 'AIR' . $air->id]);
                $this->doSTKPush('AIR' . $air->id, (int)$amount, $msisdn);
                return response($menu, 200)
                    ->header('Content-Type', 'text/plain');
            }
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
                if ($selection == "0") {
                    return response("END Thank you for checking out our USSD Platform. Dial *207# to get more services.", 200)
                        ->header('Content-Type', 'text/plain');
                }
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
                $menu .= '7. Buy Airtime' . PHP_EOL;
                $menu .= '0. Exit';
                $min = 1;
                $max = 7;
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
                        $items = $this->getContent('Wrong Number');
                        $menu .= $items[0];
                        $min = $items[1];
                        $max = $items[2];
                        $title = 'Wrong number';
                        break;
                    case 3:
                        //list available content
                        $menu = 'CON Adult in the Room. Select' . PHP_EOL;
                        $items = $this->getContent('Adult in the Room');
                        $menu .= $items[0];
                        $min = $items[1];
                        $max = $items[2];
                        $title = 'Adult in the Room';
                        break;
                    case 4:
                        //list available content
                        $menu = 'CON Kesi Mashinani. Select' . PHP_EOL;
                        $items = $this->getContent('Kesi Mashinani');
                        $menu .= $items[0];
                        $min = $items[1];
                        $max = $items[2];
                        $title = 'Kesi Mashinani';
                        break;
                    case 5:
                        //list available content
                        $menu = 'CON Situation Room. Select' . PHP_EOL;
                        $items = $this->getContent('Situation Room');
                        $menu .= $items[0];
                        $min = $items[1];
                        $max = $items[2];
                        $title = 'Situation Room';
                        break;
                    case 6:
                        $menu = 'END Thank you for subscribing to Euro News.';
                        //subscribe user and terminate session
                        $title = 'Euro News';
                        $this->subscribe($session->MSISDN, '001006928422');
                        break;
                    case 7:
                        $menu = 'CON Buy Airtime' . PHP_EOL;
                        $menu .= '1. Self' . PHP_EOL;
                        $menu .= '2. Other number';
                        $min = 1;
                        $max = 2;
                        $title = 'Buy Airtime';
                        break;
                }
                break;
            case 3:

                switch ($session->TITLE) {
                    case 'Wrong number':
                        $this->subscribe($session->MSISDN, '001006928145');
                        $menu = 'END Thank you for subscribing to ' . $session->TITLE . ' Content.';
                        break;
                    case 'Kesi Mashinani':
                        $this->subscribe($session->MSISDN, '001006928147');
                        $menu = 'END Thank you for subscribing to ' . $session->TITLE . ' Content.';
                        break;
                    case 'Situation Room':
                        $this->subscribe($session->MSISDN, '001006928145');
                        $menu = 'END Thank you for subscribing to ' . $session->TITLE . ' Content.';
                        break;
                    case 'Adult in the Room':
                        $this->subscribe($session->MSISDN, '001006928423');
                        $menu = 'END Thank you for subscribing to ' . $session->TITLE . ' Content.';
                        break;
                    case 'Buy Airtime':
                        switch ($selection) {
                            case 1:
                                $menu = 'CON Buy Airtime' . PHP_EOL;
                                $menu .= '1. Enter amount';
                                $min = 10;
                                $max = 1000;
                                $title = 'Airtime Self';
                                Airtimerequest::create([
                                    'session_id' => $session->SESSION_ID, 'msisdn' => $session->MSISDN, 'creditphone' => $session->MSISDN,
                                ]);
                                break;
                            case 2:
                                $menu = 'CON Buy Airtime' . PHP_EOL;
                                $menu .= 'Enter Recipient Number(254...):';
                                $min = 0;
                                $max = 9999999999;
                                $title = 'Airtime Other';
                                Airtimerequest::create([
                                    'session_id' => $session->SESSION_ID, 'msisdn' => $session->MSISDN, 'creditphone' => $session->MSISDN
                                ]);
                                break;
                        }
                        break;
                }

                break;
            case 4:
                switch ($session->TITLE) {
                    case 'Airtime Self':
                        $air = Airtimerequest::where([['session_id', '=', $session->SESSION_ID], ['msisdn', '=', $session->MSISDN]])->first();
                        $air->update([
                            'amount' => $selection, 'status' => 1, 'mpesa_account' => 'AIR' . $air->id
                        ]);
                        //submit amount and telephone
                        $menu = 'END Thank you. Please confirm your airtime purchase of KES.' . $selection . ' by supplying your M-Pesa pin on your phone.';
                        $this->doSTKPush('AIR' . $air->id, (float)$selection, $air->msisdn);
                        break;
                    case 'Airtime Other':
                        $menu = 'CON Buy Airtime' . PHP_EOL;
                        $menu .= 'Enter amount:';
                        $min = 10;
                        $max = 1000;
                        $title = 'Airtime Other';
                        Airtimerequest::where([['session_id', '=', $session->SESSION_ID], ['msisdn', '=', $session->MSISDN]])->update([
                            'creditphone' => $this->FormatTelephone($selection)
                        ]);
                        break;
                }
                break;
            case 5:
                switch ($session->TITLE) {
                    case 'Airtime Other':
                        //submit amount and telephone
                        $a = Airtimerequest::where([['session_id', '=', $session->SESSION_ID], ['msisdn', '=', $session->MSISDN]])->first();
                        $menu = 'END Thank you. Please confirm your airtime purchase of KES.' . $selection . ' for telephone (' . $a->creditphone . ') by supplying your M-Pesa pin on your phone.';
                        $a->update([
                            'amount' => $selection, 'status' => 1, 'mpesa_account' =>  'AIR' . $a->id
                        ]);
                        $this->doSTKPush('AIR' . $a->id, (float)$selection, $a->msisdn);
                        break;
                }
                break;
            default:
        }

        return [$menu, $min, $max, $title];
    }
    function getContent($class)
    {
        $menu = '';
        $content = Content::where('ussdmenu', $class)->orderby('ussdlistnumber', 'Asc')->get();
        foreach ($content as $c) {
            $menu .= $c->ussdlistnumber . '. ' . $c->title . PHP_EOL;
        }
        if (sizeof($content) > 0) {
            return [$menu, min($content->pluck('ussdlistnumber')->toArray()), max($content->pluck('ussdlistnumber')->toArray())];
        }
        return [$class, 0, 0];
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
    function FormatTelephone($tel)
    {
        $tel = trim($tel);

        if (substr($tel, 0, 1) == '0') { //0720076063
            $tel = '254' . substr(trim($tel), 1);
        } elseif (substr($tel, 0, 1) == '7' || substr($tel, 0, 1) == '1') { //720076063
            $tel = '254' . $tel;
        } elseif (substr($tel, 0, 5) == '+2547' || substr($tel, 0, 5) == '+2541') { //+254720076063
            $tel = substr($tel, 1);
        } elseif (substr($tel, 0, 4) == '2547' || substr($tel, 0, 4) == '2541') { //254720076063
            $tel = $tel;
        } elseif (substr($tel, 0, 5) == '25407' || substr($tel, 0, 5) == '25401') { //2540720076063
            $tel = substr($tel, 0, 3) . substr($tel, 4);
        } elseif (substr($tel, 0, 6) == '+25407' || substr($tel, 0, 6) == '+25401') { //+2540720076063
            $tel = substr($tel, 1, 3) . substr($tel, 5);
        } else {
            $tel = '';
        }
        return $tel;
    }
    function doSTKPush($account, $amount, $telephone)
    {
        $payload = array(
            'account' => $account,
            'group' => 'digital',
            'amount' => $amount,
            'msisdn' => $telephone,
            'description' => 'Mawingu Airtime Purchase'
        );
        $apiurl = 'https://trans.standardmedia.co.ke/api/checkout';
        $client = new Client();
        $response = $client->request('POST', $apiurl, [
            'form_params' => $payload,
            'headers' => ["Accept: application/json", "Accept-Language: en-us"]
        ]);
        $headers = $response->getHeaders();
        $body = $response->getBody();
        $body_array = json_decode($body);

        return $body_array;
    }
}
