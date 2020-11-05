<?php

namespace App\Http\Controllers;

use App\Session;
use App\Subscriber;
use App\Subscription;
use Carbon\Carbon;
use Exception;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Log;

class UssdController extends Controller
{
    public function Request(Request $request)
    {
        try {
            $tel = $_GET['MSISDN'];
            $serviceCode = $_GET['SERVICE_CODE'];
            $ussdString = $_GET['USSD_STRING'];
            $ussdString = str_replace('*', '', $_GET['USSD_STRING']);
            $sessionId = $_GET['SESSION_ID'];
            $contsess = Session::where('session_id', $sessionId)->first();
            if ($contsess) {
                //continuing
                $subs = Subscriber::where('telephone', $tel)->first();
                //get user input
                $currentlevel = $contsess->level + 1;
                $len = strlen($contsess->previoususerinput);
                $userinput = substr($ussdString, $len);
                $previoususerinput = substr($contsess->previoususerinput, $len - 1);
                $contsess->update(['level' => $currentlevel, 'userinput' => $userinput, 'previoususerinput' => $ussdString]);

                switch ($currentlevel) {
                    case 1:
                        if ($userinput != '1' && $userinput != '2') {
                            $contsess->update(['level' => 0]);
                            return response($this->conussd($this->topmenu()), 200)
                                ->header('Content-Type', 'text/plain');
                        }
                        $subs->update(['language_id' => (int)$userinput]);
                        $this->updateinput($sessionId, '1', '6','');
                        return response($this->conussd($this->mainmenu((int) $userinput)), 200)
                            ->header('Content-Type', 'text/plain');
                        break;
                    case 2:
                        //answers to main menu
                        if ($userinput != '1' && $userinput != '2' && $userinput != '3' && $userinput != '4' && $userinput != '5' && $userinput != '6') {
                            return response($this->conussd($this->invalid($subs->language_id, $sessionId)), 200)
                                ->header('Content-Type', 'text/plain');
                        }
                        if ($userinput == '1') {
                            //findjobs
                            $this->updateinput($sessionId, '1', '7','Jobs');

                            return response($this->conussd($this->findjobs($subs->language_id)), 200)
                                ->header('Content-Type', 'text/plain');
                        }
                        if ($userinput == '2') {//healthy living
                            $this->updateinput($sessionId, '1', '2','Healthy Living');
                            return response($this->conussd($this->healtyliving($subs->language_id)), 200)
                                ->header('Content-Type', 'text/plain');
                        }
                        if ($userinput == '3') {//dating tips
                            $this->updateinput($sessionId, '1', '5','Dating Tips');
                            return response($this->conussd($this->datingtips($subs->language_id)), 200)
                                ->header('Content-Type', 'text/plain');
                        }
                        if ($userinput == '4') {//farming tips
                            $this->updateinput($sessionId, '1', '3','Farming Tips');
                            return response($this->conussd($this->farmingtips($subs->language_id)), 200)
                                ->header('Content-Type', 'text/plain');
                        }
                        if ($userinput == '5') {//feedback
                            $this->updateinput($sessionId, '1', '2','Feedback');
                            return response($this->conussd($this->feedback($subs->language_id)), 200)
                                ->header('Content-Type', 'text/plain');
                        }
                        if ($userinput == '6') {//exit
                            if ($subs->language_id == 2) {
                                return response('END Asante kwa kupendezwa na kutazama sehemu yetu ya huduma ya Thamani. Piga *207# ili ujiandikishe na upate vidokezo juu ya anuwai.', 200)
                                    ->header('Content-Type', 'text/plain');
                            }
                            return response('END Thank you for taking interest in viewing our Value Added Service products section. Dial *207# to subscribe and get tips on the various categories.', 200)
                                ->header('Content-Type', 'text/plain');
                        }
                        break;
                    case 3:
                        switch ((int) $previoususerinput) {
                            case 1:
                                if ($userinput != '1' && $userinput != '2' && $userinput != '3' && $userinput != '4' && $userinput != '5' && $userinput != '6' && $userinput != '7') {
                                    return response($this->conussd($this->invalid($subs->language_id, $sessionId)), 200)
                                        ->header('Content-Type', 'text/plain');
                                }
                                $this->updateinput($sessionId, '1', '7','Jobs');
                                if ($userinput == '1') {
                                    if ($subs->language_id == 2) {
                                        $this->updateinput($sessionId, '1', '2','Kazi za Karani');
                                        return response('CON Jiandikishe kwa vidokezo vya kazi ya Karani kwa Ksh.50 kwa mwezi.' . PHP_EOL . '1. Sawa' . PHP_EOL . '2. Ghairi', 200)
                                            ->header('Content-Type', 'text/plain');
                                    }
                                    $this->updateinput($sessionId, '1', '2','Clerical Jobs');
                                    return response('CON Subscribe to Clerical Job tips at Ksh.50 per month.' . PHP_EOL . '1. OK' . PHP_EOL . '2. Cancel', 200)
                                        ->header('Content-Type', 'text/plain');
                                }
                                if ($userinput == '2') {
                                    if ($subs->language_id == 2) {
                                        $this->updateinput($sessionId, '1', '2','Kazi za Uuzaji');
                                        return response('CON Jiandikishe kwa vidokezo vya kazi ya Uuzaji kwa Ksh.50 kwa mwezi.' . PHP_EOL . '1. Sawa' . PHP_EOL . '2. Ghairi', 200)
                                            ->header('Content-Type', 'text/plain');
                                    }
                                    $this->updateinput($sessionId, '1', '2','Marketing Jobs');
                                    return response('CON Subscribe to Sales & Marketing Job tips at Ksh.50 per month.' . PHP_EOL . '1. OK' . PHP_EOL . '2. Cancel', 200)
                                        ->header('Content-Type', 'text/plain');
                                }
                                if ($userinput == '3') {
                                    if ($subs->language_id == 2) {
                                        $this->updateinput($sessionId, '1', '2','Kazi ya Huduma ya Wateja');
                                        return response('CON Jiandikishe kwa vidokezo vya kazi ya Huduma ya Wateja kwa Ksh.50 kwa mwezi.' . PHP_EOL . '1. Sawa' . PHP_EOL . '2. Ghairi', 200)
                                            ->header('Content-Type', 'text/plain');
                                    }
                                    $this->updateinput($sessionId, '1', '2','Customer Care Job tips');
                                    return response('CON Subscribe to Customer Care Job tips at Ksh.50 per month.' . PHP_EOL . '1. OK' . PHP_EOL . '2. Cancel', 200)
                                        ->header('Content-Type', 'text/plain');
                                }
                                if ($userinput == '4') {
                                    if ($subs->language_id == 2) {
                                        $this->updateinput($sessionId, '1', '2','Kazi ya Usimamizi');
                                        return response('CON Jiandikishe kwa vidokezo vya kazi ya Usimamizi kwa Ksh.50 kwa mwezi.' . PHP_EOL . '1. Sawa' . PHP_EOL . '2. Ghairi', 200)
                                            ->header('Content-Type', 'text/plain');
                                    }
                                    $this->updateinput($sessionId, '1', '2','Admin Job tips');
                                    return response('CON Subscribe to Admin Job tips at Ksh.50 per month.' . PHP_EOL . '1. OK' . PHP_EOL . '2. Cancel', 200)
                                        ->header('Content-Type', 'text/plain');
                                }
                                if ($userinput == '5') {
                                    if ($subs->language_id == 2) {
                                        $this->updateinput($sessionId, '1', '2','Kazi za NGO');
                                        return response('CON Jiandikishe kwa vidokezo vya kazi za NGO kwa Ksh.50 kwa mwezi.' . PHP_EOL . '1. Sawa' . PHP_EOL . '2. Ghairi', 200)
                                            ->header('Content-Type', 'text/plain');
                                    }
                                    $this->updateinput($sessionId, '1', '2','NGO Job tips');
                                    return response('CON Subscribe to NGO Job tips at Ksh.50 per month.' . PHP_EOL . '1. OK' . PHP_EOL . '2. Cancel', 200)
                                        ->header('Content-Type', 'text/plain');
                                }
                                if ($userinput == '6') {
                                    if ($subs->language_id == 2) {
                                        $this->updateinput($sessionId, '1', '2','Kazi za hoteli');
                                        return response('CON Jiandikishe kwa vidokezo vya kazi za hoteli kwa Ksh.50 kwa mwezi.' . PHP_EOL . '1. Sawa' . PHP_EOL . '2. Ghairi', 200)
                                            ->header('Content-Type', 'text/plain');
                                    }
                                    $this->updateinput($sessionId, '1', '2','Hotel Job tips');
                                    return response('CON Subscribe to Hotel Job tips at Ksh.50 per month.' . PHP_EOL . '1. OK' . PHP_EOL . '2. Cancel', 200)
                                        ->header('Content-Type', 'text/plain');
                                }
                                if ($userinput == '7') {
                                    if ($subs->language_id == 2) {
                                        $this->updateinput($sessionId, '1', '2','Kazi za Kuendeleza Biashara');
                                        return response('CON Jiandikishe kwa vidokezo vya kazi za Kuendeleza Biashara kwa Ksh.50 kwa mwezi.' . PHP_EOL . '1. Sawa' . PHP_EOL . '2. Ghairi', 200)
                                            ->header('Content-Type', 'text/plain');
                                    }
                                    $this->updateinput($sessionId, '1', '2','Business Development Job tips');
                                    return response('CON Subscribe to Business Development Job tips at Ksh.50 per month.' . PHP_EOL . '1. OK' . PHP_EOL . '2. Cancel', 200)
                                        ->header('Content-Type', 'text/plain');
                                }
                                break;
                            case 2:
                                if ($userinput != '1' && $userinput != '2') {
                                    return response($this->conussd($this->invalid($subs->language_id, $sessionId)), 200)
                                        ->header('Content-Type', 'text/plain');
                                }
                                // $contsess->update(['correctinput' => $contsess->correctinput . $userinput]);
                                if ($userinput == '1') {
                                    if ($subs->language_id == 2) {
                                        $this->updateinput($sessionId, '1', '2','Kuzuia Saratani');
                                        return response('CON Jiandikishe kwa vidokezo vya Kuzuia Saratani kwa Ksh.50 kwa mwezi.' . PHP_EOL . '1. Sawa' . PHP_EOL . '2. Ghairi', 200)
                                            ->header('Content-Type', 'text/plain');
                                    }
                                    $this->updateinput($sessionId, '1', '2','Tips to Prevent Cancer');
                                    return response('CON Subscribe to Tips to Prevent Cancer at Ksh.50 per month.' . PHP_EOL . '1. OK' . PHP_EOL . '2. Cancel', 200)
                                        ->header('Content-Type', 'text/plain');
                                }
                                if ($userinput == '2') {
                                    if ($subs->language_id == 2) {
                                        $this->updateinput($sessionId, '1', '2','Vidokezo salama vya Mimba');
                                        return response('CON Jiandikishe kwa vidokezo salama vya Mimba kwa Ksh.50 kwa mwezi.' . PHP_EOL . '1. Sawa' . PHP_EOL . '2. Ghairi', 200)
                                            ->header('Content-Type', 'text/plain');
                                    }
                                    $this->updateinput($sessionId, '1', '2','Safe Pregnancy Tips');
                                    return response('CON Subscribe to Safe Pregnancy Tips at Ksh.50 per month.' . PHP_EOL . '1. OK' . PHP_EOL . '2. Cancel', 200)
                                        ->header('Content-Type', 'text/plain');
                                }
                                break;
                            case 3:
                                if ($userinput != '1' && $userinput != '2' && $userinput != '3' && $userinput != '4' && $userinput != '5') {
                                    return response($this->conussd($this->invalid($subs->language_id, $sessionId)), 200)
                                        ->header('Content-Type', 'text/plain');
                                }

                                if ($userinput == '1') {
                                    if ($subs->language_id == 2) {
                                        $this->updateinput($sessionId, '1', '2','Vidokezo vya Wanawake');
                                        return response('CON Jiandikishe kwa vidokezo Vidokezo vya Wanawake kwa Ksh.50 kwa mwezi.' . PHP_EOL . '1. Sawa' . PHP_EOL . '2. Ghairi', 200)
                                            ->header('Content-Type', 'text/plain');
                                    }
                                    $this->updateinput($sessionId, '1', '2','Women Tips');
                                    return response('CON Subscribe to Women Tips at Ksh.50 per month.' . PHP_EOL . '1. OK' . PHP_EOL . '2. Cancel', 200)
                                        ->header('Content-Type', 'text/plain');
                                }
                                if ($userinput == '2') {
                                    if ($subs->language_id == 2) {
                                        $this->updateinput($sessionId, '1', '2','Vidokezo vya Wanaume');
                                        return response('CON Jiandikishe kwa vidokezo Vidokezo vya Wanaume kwa Ksh.50 kwa mwezi.' . PHP_EOL . '1. Sawa' . PHP_EOL . '2. Ghairi', 200)
                                            ->header('Content-Type', 'text/plain');
                                    }
                                    $this->updateinput($sessionId, '1', '2','Men Tips');
                                    return response('CON Subscribe to Men Tips at Ksh.50 per month.' . PHP_EOL . '1. OK' . PHP_EOL . '2. Cancel', 200)
                                        ->header('Content-Type', 'text/plain');
                                }
                                if ($userinput == '3') {
                                    if ($subs->language_id == 2) {
                                        $this->updateinput($sessionId, '1', '2','Vidokezo vya Ndoa');
                                        return response('CON Jiandikishe kwa Vidokezo vya Ndoa kwa Ksh.50 kwa mwezi.' . PHP_EOL . '1. Sawa' . PHP_EOL . '2. Ghairi', 200)
                                            ->header('Content-Type', 'text/plain');
                                    }
                                    $this->updateinput($sessionId, '1', '2','Marriage Tips');
                                    return response('CON Subscribe to Marriage Tips at Ksh.50 per month.' . PHP_EOL . '1. OK' . PHP_EOL . '2. Cancel', 200)
                                        ->header('Content-Type', 'text/plain');
                                }
                                if ($userinput == '4') {
                                    if ($subs->language_id == 2) {
                                        $this->updateinput($sessionId, '1', '2','Nukuu za Upendo');
                                        return response('CON Jiandikishe kwa Nukuu za Upendo kwa Ksh.50 kwa mwezi.' . PHP_EOL . '1. Sawa' . PHP_EOL . '2. Ghairi', 200)
                                            ->header('Content-Type', 'text/plain');
                                    }
                                    $this->updateinput($sessionId, '1', '2','Love Quotes');
                                    return response('CON Subscribe to Love Quotes at Ksh.50 per month.' . PHP_EOL . '1. OK' . PHP_EOL . '2. Cancel', 200)
                                        ->header('Content-Type', 'text/plain');
                                }
                                if ($userinput == '5') {
                                    if ($subs->language_id == 2) {
                                        $this->updateinput($sessionId, '1', '2','Vidokezo vya Upendo');
                                        return response('CON Jiandikishe Vidokezo vya Upendo kwa Ksh.50 kwa mwezi.' . PHP_EOL . '1. Sawa' . PHP_EOL . '2. Ghairi', 200)
                                            ->header('Content-Type', 'text/plain');
                                    }
                                    $this->updateinput($sessionId, '1', '2','Crazy Loving Tips');
                                    return response('CON Subscribe to Crazy Loving Tips at Ksh.50 per month.' . PHP_EOL . '1. OK' . PHP_EOL . '2. Cancel', 200)
                                        ->header('Content-Type', 'text/plain');
                                }
                                break;
                            case 4:
                                if ($userinput != '1' && $userinput != '2' && $userinput != '3') {
                                    return response($this->conussd($this->invalid($subs->language_id, $sessionId)), 200)
                                        ->header('Content-Type', 'text/plain');
                                }

                                if ($userinput == '1') {
                                    if ($subs->language_id == 2) {
                                        $this->updateinput($sessionId, '1', '2','Vidokezo vya hali ya hewa');
                                        return response('CON Jiandikishe kwa Vidokezo vya hali ya hewa kwa Ksh.50 kwa mwezi.' . PHP_EOL . '1. Sawa' . PHP_EOL . '2. Ghairi', 200)
                                            ->header('Content-Type', 'text/plain');
                                    }
                                    $this->updateinput($sessionId, '1', '2','Weather Tips');
                                    return response('CON Subscribe to Weather Tips at Ksh.50 per month.' . PHP_EOL . '1. OK' . PHP_EOL . '2. Cancel', 200)
                                        ->header('Content-Type', 'text/plain');
                                }
                                if ($userinput == '2') {
                                    if ($subs->language_id == 2) {
                                        $this->updateinput($sessionId, '1', '2','Aina za mchanga & Mazao');
                                        return response('CON Jiandikishe kwa Aina za mchanga & Mazao kwa Ksh.50 kwa mwezi.' . PHP_EOL . '1. Sawa' . PHP_EOL . '2. Ghairi', 200)
                                            ->header('Content-Type', 'text/plain');
                                    }
                                    $this->updateinput($sessionId, '1', '2','Soil Types & Crops');
                                    return response('CON Subscribe to Soil Types & Crops at Ksh.50 per month.' . PHP_EOL . '1. OK' . PHP_EOL . '2. Cancel', 200)
                                        ->header('Content-Type', 'text/plain');
                                }
                                if ($userinput == '3') {
                                    if ($subs->language_id == 2) {
                                        $this->updateinput($sessionId, '1', '2','Vidokezo vya kilimo');
                                        return response('CON Jiandikishe kwa Vidokezo vya kilimo kwa Ksh.50 kwa mwezi.' . PHP_EOL . '1. Sawa' . PHP_EOL . '2. Ghairi', 200)
                                            ->header('Content-Type', 'text/plain');
                                    }
                                    $this->updateinput($sessionId, '1', '2','Agricultural Tips');
                                    return response('CON Subscribe to Agricultural Tips at Ksh.50 per month.' . PHP_EOL . '1. OK' . PHP_EOL . '2. Cancel', 200)
                                        ->header('Content-Type', 'text/plain');
                                }
                                break;

                                case 5:
                                    if ($userinput != '1' && $userinput != '2') {
                                        return response($this->conussd($this->invalid($subs->language_id, $sessionId)), 200)
                                            ->header('Content-Type', 'text/plain');
                                    }
    
                                    if ($userinput == '1') {
                                        if ($subs->language_id == 2) {
                                            return response('CON Andikisha Ujumbe Wako.' . PHP_EOL . ' Andika Ujumbe Wako' . PHP_EOL . 200)
                                                ->header('Content-Type', 'text/plain');
                                        }
                                        return response('CON Give your feedback.' . PHP_EOL . ' Write your feedback'  . PHP_EOL . 200)
                                            ->header('Content-Type', 'text/plain');

                                         
                                    }
                                    if ($userinput == '2') {
                                        if ($subs->language_id == 2) {
                                            return response('CON Chagua Gatezi ya kueka Ujumbe.' . PHP_EOL . '1. Standard' . PHP_EOL . '2. Nairobian', 200)
                                                ->header('Content-Type', 'text/plain');
                                               
                                        }
                                        return response('CON Select the newspaper to give feedback on.' . PHP_EOL . '1. Standard' . PHP_EOL . '2. Nairobian', 200)
                                            ->header('Content-Type', 'text/plain');
                                          
                                    }
                                break;
                            

                            default:
                                if ($subs->language_id == 2) {
                                    return response('END Asante kwa kupendezwa na kutazama sehemu yetu ya hudma ya Thamani. Piga * 207 # ili ujiandikishe na upate vidokezo juu ya anuwai.', 200)
                                        ->header('Content-Type', 'text/plain');
                                }
                                return response('END Thank you for taking interest in viewing our Value Added Service products section. Dial *207# to subscribe and get tips on the various categories.', 200)
                                    ->header('Content-Type', 'text/plain');
                        }
                        break;
                    case 4:
                        //last - write to db
                        if ($userinput != 1 && $userinput != 2) {
                            return response($this->conussd($this->invalid($subs->language_id, $sessionId)), 200)
                                ->header('Content-Type', 'text/plain');
                        }
                        //write DB
                        $issubscribed=Subscription::where([['msisdn','=',$tel],['ussdresult','=',$contsess->userchoice]])->first();
                        if(!$issubscribed){
                            Subscription::insert([
                                'language_id'=>$subs->language_id,
                                'msisdn'=>$tel,
                                'ussdresult'=>$contsess->userchoice,
                                'created_at'=>Carbon::now(),
                                'updated_at'=>Carbon::now()
                            ]);

                           // $this->subscribe($tel,'001006919771');
                            $this->doSTKPush('VS'.$contsess->userchoice,50,$tel);
                        }
                        if ($subs->language_id == 2) {
                            return response('END Asante kwa kujiandikisha katika sehemu yetu ya huduma ya Thamani. Utakuwa ukipokea '.$contsess->userchoice.' SMS kwa shilingi 50 kwa mwezi.', 200)
                                ->header('Content-Type', 'text/plain');
                        }
                        return response('END Thank you for registering on our Value Added Service products section. You shall be receiving '.$contsess->userchoice.' SMS from the system at Kshs 50 per month.', 200)
                            ->header('Content-Type', 'text/plain');
                        break;
                    default:
                        return response($this->conussd($this->invalid($subs->language_id, $sessionId)), 200)
                            ->header('Content-Type', 'text/plain');
                }
            } else {
                $subs = Subscriber::where('telephone', $tel)->first();
                //new session
                Session::insert([
                    'session_id' => $sessionId,
                    'telephone' => $tel,
                    'service_code' => $serviceCode,
                    'userinput' => '',
                    'previoususerinput' => '',
                    'level' => $subs ? 1 : 0
                ]);
                if ($subs) {
                    $this->updateinput($sessionId, '1', '6','');
                    return response($this->conussd($this->mainmenu($subs->language_id)), 200)
                        ->header('Content-Type', 'text/plain');
                } else {
                    Subscriber::insert(['telephone' => $tel]);
                    return response($this->conussd($this->topmenu()), 200)
                        ->header('Content-Type', 'text/plain');
                }
            }
        } catch (Exception $e) {
            return response('END ' . $e->getMessage(), 200)
                ->header('Content-Type', 'text/plain');
        }
    }
    function conussd($str)
    {
        return 'CON ' . $str;
    }
    function endussd($str)
    {
        return 'END ' . $str;
    }
    function invalid($lang, $session)
    {
        Session::where('session_id', $session)->update(['level' => 1]);
        return $lang == 1 ? 'Sorry. Wrong Selection ' . $this->mainmenu($lang) : 'Samahani. Chaguo lako haliruhusiwi ' . $this->mainmenu($lang);
    }
    function updateinput($sessionId, $min, $max,$data)
    {
        Session::where('session_id', $sessionId)->update(['mininput' => $min, 'maxinput' => $max,'userchoice'=>$data]);
    }
    function topmenu()
    {
        $menu = 'Welcome to Standard Group VAS' . PHP_EOL;
        $menu .= '1. English' . PHP_EOL;
        $menu .= '2. Kiswahili';
        return $menu;
    }
    function mainmenu($lang)
    {
        $menu = 'Welcome to Standard Group VAS' . PHP_EOL;
        $menu .= '1. Find Jobs' . PHP_EOL;
        $menu .= '2. Healthy Living' . PHP_EOL;
        $menu .= '3. Dating Tips' . PHP_EOL;
        $menu .= '4. Farming Tips' . PHP_EOL;
        $menu .= '5. Feedback' . PHP_EOL;
        $menu .= '6. Exit';
        if ($lang == 2) {
            $menu = 'Karibu Standard Group VAS' . PHP_EOL;
            $menu .= '1. Kutafuta Kazi' . PHP_EOL;
            $menu .= '2. Kuishi kwa Afya' . PHP_EOL;
            $menu .= '3. Vidokezo vya Kuchumbiana' . PHP_EOL;
            $menu .= '4. Vidokezo vya Ukulima' . PHP_EOL;
            $menu .= '5. Maoni' . PHP_EOL;
            $menu .= '6. Kutoka';
        }
        return $menu;
    }
    function findjobs($lang)
    {
        $menu = 'Standard Group VAS: Find Jobs' . PHP_EOL;
        $menu .= '1. Clerk Jobs' . PHP_EOL;
        $menu .= '2. Sales & Marketing Jobs' . PHP_EOL;
        $menu .= '3. Customer Care Jobs' . PHP_EOL;
        $menu .= '4. Admin Jobs' . PHP_EOL;
        $menu .= '5. NGO Jobs' . PHP_EOL;
        $menu .= '6. Hotel Jobs' . PHP_EOL;
        $menu .= '7. Business Development Jobs';
        if ($lang == 2) {
            $menu = 'Standard Group VAS: Kutafuta Kazi' . PHP_EOL;
            $menu .= '1. Kazi za Karani' . PHP_EOL;
            $menu .= '2. Kazi za Uuzaji' . PHP_EOL;
            $menu .= '3. Kazi za Huduma ya Wateja' . PHP_EOL;
            $menu .= '4. Kazi za Usimamizi' . PHP_EOL;
            $menu .= '5. Kazi za NGO' . PHP_EOL;
            $menu .= '6. Kazi za hoteli' . PHP_EOL;
            $menu .= '7. Kazi za Kuendeleza Biashara';
        }
        return $menu;
    }
    function healtyliving($lang)
    {
        $menu = 'Standard Group VAS: Healthy Living' . PHP_EOL;
        $menu .= '1. Prevent Cancer Tips' . PHP_EOL;
        $menu .= '2. Safe Pregnancy Tips';
        if ($lang == 2) {
            $menu = 'Standard Group VAS: Kuishi kwa Afya' . PHP_EOL;
            $menu .= '1. Vidokezo vya Kuzuia Saratani' . PHP_EOL;
            $menu .= '2. Vidokezo salama vya Mimba';
        }
        return $menu;
    }
    function datingtips($lang)
    {
        $menu = 'Standard Group VAS: Dating Tips' . PHP_EOL;
        $menu .= '1. Women Tips' . PHP_EOL;
        $menu .= '2. Men Tips' . PHP_EOL;
        $menu .= '3. Marriage Tips' . PHP_EOL;
        $menu .= '4. Love Quotes' . PHP_EOL;
        $menu .= '5. Crazy Loving Tips';
        if ($lang == 2) {
            $menu = 'Standard Group VAS: Vidokezo vya Kuchumbiana' . PHP_EOL;
            $menu .= '1. Vidokezo vya Wanawake' . PHP_EOL;
            $menu .= '2. Vidokezo vya Wanaume' . PHP_EOL;
            $menu .= '3. Vidokezo vya Ndoa' . PHP_EOL;
            $menu .= '4. Nukuu za Upendo' . PHP_EOL;
            $menu .= '5. Vidokezo vya Upendo';
        }
        return $menu;
    }
    function farmingtips($lang)
    {
        $menu = 'Standard Group VAS: Farming Tips' . PHP_EOL;
        $menu .= '1. Weather Tips' . PHP_EOL;
        $menu .= '2. Soil Types & Crops' . PHP_EOL;
        $menu .= '3. Agricultural Tips';
        if ($lang == 2) {
            $menu = 'Standard Group VAS: Vidokezo vya Ukulima' . PHP_EOL;
            $menu .= '1.Vidokezo vya hali ya hewa' . PHP_EOL;
            $menu .= '2. Aina za mchanga & Mazao' . PHP_EOL;
            $menu .= '3. Vidokezo vya kilimo';
        }
        return $menu;
    }
    function videos($lang)
    {
        $menu = 'Standard Group VAS: Videos' . PHP_EOL;
        $menu .= '1. Sports Videos' . PHP_EOL;
        $menu .= '2. Music Videos' . PHP_EOL;
        if ($lang == 2) {
            $menu = 'Standard Group VAS: Video' . PHP_EOL;
            $menu .= '1. Video za Michezo' . PHP_EOL;
            $menu .= '2. Video za Muziki' . PHP_EOL;
        }
        return $menu;
    }
    function feedback($lang)
    {
        $menu = 'Standard Group VAS: Feedback' . PHP_EOL;
        $menu .= '1. General Feedback' . PHP_EOL;
        $menu .= '2. Newspaper Feedback' . PHP_EOL;
        if ($lang == 2) {
            $menu = 'Standard Group VAS: Maoni' . PHP_EOL;
            $menu .= '1. Maoni ya Jumla' . PHP_EOL;
            $menu .= '2. Maoni ya Magazeti' . PHP_EOL;
        }
        return $menu;
    }

    public function getussdmenus(Request $request)
    {
        $sessionid = '62323723';
        $msisdn = '254720076063';
        $servicecode = '*167#';
        $ussdstring = '1';
        try {
            $apiurl = 'http://127.0.0.1:8000/api/ussdmenus';
            $client = new Client(['auth' => ['username', 'password']]);
            $response = $client->request('GET', $apiurl, ['query' => ['sessionid' => $sessionid, "msisdn" => $msisdn, "servicecode" => $servicecode, "ussdstring" => $ussdstring]]);
            if (!$response) {
                return null;
            } else {
                $body = $response->getBody();
                $object = json_decode($body);
                return null;
            }
        } catch (Exception $ex) {
            return null;
        }
    }
    public function ussdmenus(Request $request)
    {
        //return response()->json('This are the menu');
        $headers = apache_request_headers();
        $creds = explode(':', base64_decode(str_replace('Basic ', '', $headers['Authorization'])));
        log::info(json_encode($_SERVER['QUERY_STRING']));
        log::info(json_encode($creds));
        $menu = 'CON Main Menu \n';
        $menu .= '1. Register \n';
        $menu .= '2. Feedback \n';
        $menu .= '3. Exit';

        //Log::info($creds[0]);//username
        //Log::warning($creds[1]);//password
        return response()->json($menu);
    }

    function subscribe($msisdn, $offercode)
    {
        try {
            $apiurl = 'https://ktnkenya.com/vas/public/api/SubscribeUser';

            $client = new Client();
            $response = $client->request(
                'POST',
                $apiurl,
                [
                    'headers' => [
                        'Content-Type' => ' application/json',
                    ],
                    'body' => json_encode([
                        'msisdn' => $msisdn,
                        'offercode' => $offercode
                    ])
                ]
            );
            return null;
        } catch (Exception $ex) {
            return null;
        }
    }

    function doSTKPush($account,$amount,$telephone){
        $payload = array(
            'account' =>$account,
            'group' => 'digital',
            'amount' => $amount,
            'msisdn' =>$telephone,
            'description' => 'Ussd Subscription'
        );
        $apiurl = 'https://trans.standardmedia.co.ke/api/checkout';
        $client = new Client();
        $response = $client->request('POST', $apiurl, [
            'form_params' =>$payload,
            'headers' => ["Accept: application/json","Accept-Language: en-us"]
        ]);
       return 0;
    }
}
