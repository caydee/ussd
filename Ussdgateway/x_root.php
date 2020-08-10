<?php
    class x_root{
        //
        public $sms_path;
        public $sms_url;
        //
        static $datasync_ps_url="http://192.168.100.52/sg_sms/index.php/ps_datasync/ps_datasync";
        //
        static $incoming_sms_ps_url="http://192.168.100.52/sg_sms/index.php/ps_sms_notification/ps_incoming_sms";
        static $sms_delivery_receipt_ps_url="http://192.168.100.52/sg_sms/index.php/ps_sms_notification/ps_sms_delivery_receipt";
        //
        static $sg_sms_send_sub_request_ip = "http://192.168.100.52/sg_sms/index.php/ext_api/send_sub_request";
        static $sg_sms_send_alert_ip = "http://192.168.100.52/sg_sms/index.php/ext_api/send_alert";

        static $sg_sms_get_product_code_ip = "http://192.168.100.52/sg_sms/index.php/ext_api/getSubProductCodeAPI";
        //
        static $push_subscribe_url="http://192.168.100.52/sg_sms/index.php/push_subscribe/ps";
        //
        static $ntsa_22846_inspections_url = "http://41.206.37.72/inspections/cpanel/sms/smsService.php";
        static $ntsa_22847_ictsupport_url = "http://41.206.37.72/ictsupport/selfservice/smsService.php";
        //
        public function __construct() {
            //
            date_default_timezone_set("Africa/Nairobi");
            //
            if(!isset($_SESSION)){
                session_start();
            }
            //
            ini_set("display_errors", 0);
            error_reporting(~E_ALL);
            ///home/std.co.ke/sms/
            $this->sms_path = $_SERVER['DOCUMENT_ROOT']."/sms/";
            //
            $this->sms_url = "http://192.168.100.52/sms/";
        }
        //
        function reqdParamsSet($arr){
            foreach ($arr as $val){
                if(!strlen($val)){
                    return FALSE;
                }
            }
            return TRUE;
        }
        //
        function disableErrorReportingHere(){
            ini_set('display_errors', 0);
            error_reporting(~E_ALL);
        }
        //
        function enableErrorReportingHere(){
            ini_set('display_errors', 1);
            error_reporting(E_ALL);
        }
        //
        function metaRefresh($url, $say = TRUE){
            if($say){
                $this->sayRedirecting();
            }
            //
            echo "<meta http-equiv='refresh' content='0;URL={$url}'/>"; 
        }
        //
        function sayRedirecting(){
            echo "<p>Redirecting...</p>";
        }
        //
        public function getParam($str){
            if(isset($_GET[$str])){
                return $this->x_clean($_GET[$str]);
            }
            elseif(isset($_POST[$str])){
                return $this->x_clean($_POST[$str]);
            }
            else{
                return FALSE;
            }
        }
        //
        function x_clean($str){
            //

            if(is_array($str)){
                $arr = array();
                foreach ($str as $val){
                    $arr[] = $this->clean_html(trim($val)) ;
                }
                return $arr;
            }
            return $this->clean_html(trim($str)) ;
        }
        //
        function clean_html($str){
            //a basic XSS filter
            return preg_replace('/script>/i', '', $str);
        }
        //
        function x_log($descr, $data){
            //$log_fh = fopen("/home/standard_digital/httpdocs/X_Common/xLOGx/x_log.txt", "a");
            $log_fh = fopen("/home/standard_digital/httpdocs/sg_sms/assets/x_log.txt", "a");
            //
            if(!isset($_SESSION['usr']['nm'])){
                $_SESSION['usr']['nm'] = "SYS";
            }
            //
            if( fwrite($log_fh, date("Y-m-d H:i:s").";".$_SERVER['REMOTE_ADDR'].";".$_SESSION['usr']['nm'].";".$descr.";".$data."\n") ){
                fclose($log_fh);
                return TRUE;
            }
            //
            fclose($log_fh);
            return FALSE;
        }
        //
        function x_ml($tos, $subject, $msg, $from){
            //
            $msg2 = wordwrap($msg,70);
            // Always set content-type when sending HTML email
            $headers = "MIME-Version: 1.0" . "\r\n";
            $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
            $headers .= "From: {$from}\r\n";

            if(mail($tos,$subject,$msg2,$headers)){
                return TRUE;
            }
            return FALSE;
        }
        //
        function dbg($sth){
            //return 0;
            if(is_array($sth)){
                echo '<BR>';
                print_r($sth);
                echo '<BR>';
            }
            else{
                echo "<BR><small class='text-warning' style='font-size:small; color:orange;'>{$sth}</small><BR>";
            }
        }
        //
        function isLoggedIn(){
            if(empty($_SESSION['usr']['user_nm'])){
                return FALSE;
            }
            return TRUE;
        }
        //
        function x_curl($url, $params){
            $post = curl_init();

            curl_setopt($post, CURLOPT_URL, $url);
            curl_setopt($post, CURLOPT_POSTFIELDS, $params);
            curl_setopt($post, CURLOPT_RETURNTRANSFER, 1);

            $result = curl_exec($post);

            curl_close($post);
            
            return $result;
        }

        function batchLocked($batch_id){
            $fh = fopen("/home/standard_digital/httpdocs/sg_sms/assets/sms_lock/{$batch_id}.lck", "r");
            $str = fread($fh, 1000);
            fclose($fh);
            //$this->x_log("STATUS:::{$batch_id}_LOCKED", $str);
            //
            if( strlen($str)> 5 ){
                 $this->x_log("STATUS:::{$batch_id}_LOCKED", $str);
                return TRUE;
            }
            //$fh = //;
            return FALSE;
        }

        function rmBatchlock($batch_id){
            $fh = fopen("/home/standard_digital/httpdocs/sg_sms/assets/sms_lock/{$batch_id}.lck", "w");
            fwrite($fh, "");
            fclose($fh);
        }
        //
        function lockBatch($batch_id){
            $fh = fopen("/home/standard_digital/httpdocs/sg_sms/assets/sms_lock/{$batch_id}.lck", "w");
            fwrite($fh, "{$batch_id}_RUNNING_");
            fclose($fh);
        }

        //
    }

