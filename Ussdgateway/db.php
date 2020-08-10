<?php
//
class db {
    //
    public static $nconn;
    //put your code here
    public function __construct() {
        ;
    }
    //
   function N_db_conn( $db_nm = "sms_bernsoft", $user_nm = "user_siud", $pwd = "bf9ac6aee1b248e" ){
       //37.188.98.231
       db::$nconn = @mysqli_connect("localhost", "user_siud", 'bf9ac6aee1b248e',
                                   $db_nm, 3306);
       if(db::$nconn){
           return db::$nconn;
       }
       return FALSE;
   }
   //
    function n_exec($sql){
        //
        $res = mysqli_query(db::$nconn, $sql);
        //mysqli_close(db::$nconn);
        //
        if( $res !== FALSE ){
            //mysqli_close(db::$nconn);
            return $res;
        }
        else{
            $this->db_log("MYSQLI_ERROR", mysqli_error($link).'\n'.$sql);
            
            //mysqli_close(db::$nconn);
            return FALSE;
        }
    }
    //
    function get_single_item($sql, $item_name){
        //
        $arr = mysqli_fetch_assoc( mysqli_query(db::$nconn, $sql) );
        //mysqli_close(db::$nconn);
        //
        if( $arr !== FALSE ){
            return $arr[$item_name];
        }
        return FALSE;
    }
    //
    function get_single_row($sql){
        //
        $arr = mysqli_fetch_assoc( mysqli_query(db::$nconn, $sql) );
        //mysqli_close(db::$nconn);
        //
        if( $arr !== FALSE ){
            return $arr;
        }
        return FALSE;
    }
    //
    function db_log($descr, $data){
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
}
