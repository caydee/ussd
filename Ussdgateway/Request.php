<?php
    header('Content-type: text/plain');

    file_put_contents('ussdlogs.txt', "\n".json_encode($_REQUEST));
    if( !isset($_GET) )
    {
        echo "CON Not Allowed!";
        sleep(30);
    }

   // require_once "/home/standard_digital/httpdocs/X_Common/x_root.php";

  //  $x_rt = new x_root();

   // $x_rt->disableErrorReportingHere();

  //  $x_rt->x_log("Hello USSD",json_encode($_GET));


    $text           =   $_GET['USSD_STRING'];
    $phonenumber    =   $_GET['MSISDN'];
    $serviceCode    =   $_GET['SERVICE_CODE'];
    $sess_id        =   (int)$_GET['SESSION_ID'];
    $level          =   explode("*", urldecode($text));
    $app_title      =   "Standard Digital";


    $go_to_main_menu_or_exit    =   "0. Main Menu\n99.Exit";
    $unknown_selection          =   "{$app_title}:\nUnknown selection made {$go_to_main_menu_or_exit}";
    $exit                       =   "{$app_title}:\nThank you for using our service";


    function processUssd($text){
        echo "CON {$text}";
    }


    function mainMenu(){

        $text  =   "Welcome to {$app_title}:\n"
                   ."1. Breaking News\n"
                   ."2. NTSA\n"
                   ."3. Farm Kenya Connect\n"
                   ."5. Newspaper Feedback\n"
                   ."99. Exit";

        processUssd($text);
    }

    function newsMenu(){

        $text  =   "Subscribe to\n"
                   ."1. Swahili News\n"
                   ."2. English News\n"
                   ."99. Exit";

        processUssd($text);
    }

    function ntsaMenu(){

        $text   =    "1. Driving license status\n"
                     ."2. Vehicle inspection status\n"
                     ."99. Exit";

        processUssd($text);
    }

    function farmersMenu(){
        $text   =    "Register for: \n"
                     ."1. Farm Kenya Connect \n"
                     //."2. Eldoret Event \n"
                     ."99. Exit";

        processUssd($text);
    }

    function feedbackMenu(){
        $text   =    "Thank you for choosing The Standard. Please reply with your comments: \n";

        processUssd($text);
    }

    function sendFeedback(array $custdata){
        global $phonenumber;
        $conn = @mysqli_connect("localhost", "user_siud", 'bf9ac6aee1b248e',
            "farmers", 3306);
        if(!$conn){
            return "An error occurred. Please try again later"; //echo mysql_error();
        }
        else{
            $message = $custdata['message'];
            $name = $custdata['custname'];
            $sql = mysqli_query($conn, "INSERT INTO feedback(phonenumber,comments,customername) VALUES('$phonenumber','$message','$name')");
            if(!$sql){
                return "An error occurred: ".$sql;
            }
            else{
                return "Ok";
            }
        }
        mysqli_close($conn);
    }


    function exitUssd(){

        echo "END Thank You for using our service";
    }


    if (isset($text))
    {
        if ($text == "")
        {

            mainMenu();

        }
        else if ( $level[0] == 0)
        {
            mainMenu();

        }
        else if( $level[0] == 99 && !isset($level[1]) && !isset($level[2]) )
        {
            exitUssd();

        }elseif ($text == "99") {

             exitUssd();

        }elseif ($text == "0") {

            mainMenu();

        }

        if((isset($level[0]) && $level[0]!="") && !isset($level[1]))
        {
            if ($level[0] == 1 && $text == "1"){

                newsMenu();
                //echo "CON \n {$level[0]}\n {$level[1]}";

            }elseif ($level[0] == 2 && $text == "2"){

                ntsaMenu();
                //echo "CON \n {$level[0]}\n {$level[1]}";


            }elseif ($level[0] == 3 && $text == "3"){

                farmersMenu();
                //echo "\n{$text}";
                // $level[0] = "3";
                // echo "CON \n {$level[0]}\n {$level[1]}";

            }
            elseif($level[0] == 5 /*&& $text == "3"*/){
                feedbackMenu();
            }
        }
        else if((isset($level[0]) && $level[0]!="" ) && (isset($level[1]) && $level[1] != "") && !isset($level[2]))
        {
            if($level[0] == 1 )
            {
                if($level[1] ==1)
                {
                   // get_alert(32);
                    //echo "CON \n {$level[0]}\n {$level[1]}";
                    exitUssd();
                }
                else if($level[1] == 2)
                {
                   // get_alert(8);
                   exitUssd();

                } else if ( $level[1] == 0)
                {

                    mainMenu();

                }else if ( $level[1] == 99) {

                    exitUssd();

                }


            }
            if($level[0] == 2 )
            {
                if ($level[1] == 1) {

                    echo "END Thank you the service is under development";

                }elseif ($level[1] == 2) {

                    echo "END Thank you the service is under development";
                }
                else if($level[1] == 99){
                    echo "END Thank You for using our service";
                }
            }

            if($level[0] == 3 )
            {
                if($level[1] == 1)
                {
                    $regType = "initiative";
                   // checkFarmer($phonenumber, $exit);
                    echo   "CON Welcome to Farm Kenya Connect \n Enter your fullname";
                    // echo "CON \n {$level[0]}\n {$level[1]}";
                }
                else if($level[1] == 2)
                {
                    $regType = "event";
                   // checkFarmer($phonenumber, $exit);
                    echo   "CON Register for Event\n Enter your fullname";
                    //echo "CON \n {$level[0]}\n {$level[1]}";
                }

            }

            if($level[0] == 5)
            {
                if(isset($level[1])){
                    $feedback = $level[1];
                    echo "CON Would you like us to contact you? \n"
                         ."1. Yes \n"
                         ."2. No \n";
                }
            }

        }
        else if(isset($level[2]) && $level[2]!="" && !isset($level[3]))
        {

            if($level[0] == 3 )
            {
                echo   "CON Enter Your Age ";

            }

            if($level[0] == 5)
            {
                if($level[2] == 1)
                {
                    echo "CON Please enter your name: ";
                }
                elseif($level[2] == 2)
                {
                    $custfeedback = array(
                        'msisdn' => $phonenumber,
                        'message' => $level[1],
                        'custname' => ''
                    );
                    //insert to DB
                //    $fb_response = sendFeedback($custfeedback);
                    //echo $fb_response."\n";
                    echo "END Thank you for your valued feedback.";
                }
            }
        }
        else if(isset($level[3]) && $level[3]!="" && !isset($level[4]))
        {
            if($level[0] == 3 )
            {
                echo   "CON Enter Your Gender";
            }

            if($level[0] == 5)
            {
                $custfeedback = array(
                    'msisdn' => $phonenumber,
                    'message' => $level[1],
                    'custname' => $level[3]
                );
          //      $results = sendFeedback($custfeedback);
                echo "END Thank you for your valued feedback, ".$level[3].".\n\n Our Customer Care team will be contacting you soon on matters aforementioned.\n";
            }
        }
        else if(isset($level[4]) && $level[4]!="" && !isset($level[5]))
        {

            if($level[0] == 3 )
            {
                echo   "CON Are you into Farming or Agri-business?(Farm/Agri)";

            }
        }
        else if(isset($level[5]) && $level[5]!="" && !isset($level[6]))
        {

            if($level[0] == 3 )
            {
                echo   "CON What type of Farming or Agri-business do you do?";

            }
        }
        else if(isset($level[6]) && $level[6]!="" && !isset($level[7]))
        {

            if($level[0] == 3 )
            {
                echo   "CON What is the size of your farm?";
            }
        }
        else if(isset($level[7]) && $level[7]!="" && !isset($level[8]))
        {

            if($level[0] == 3 )
            {
                echo   "CON Where is your farm located(Region/Location)? ";

            }
        }
         else if(isset($level[8]) && $level[8]!="" && !isset($level[9]))
        {

            if($level[0] == 3 )
            {
                echo   "CON What Farming tips would you like to learn?";

            }
        }
        else if(isset($level[9]) && $level[9]!="" && !isset($level[10]))
        {

            if($level[0] == 3 )
            {
                echo   "CON Will you attend Farm Kenya Connect in Eldoret?(Yes/No) ";

            }
        }
        else if(isset($level[10]) && $level[10]!="" && !isset($level[11]))
        {

            if($level[0] == 3 )
            {
                if($level[1] == 1)
                {
                    $data['reg_type']   = "initiative";
                }
                else if($level[1] == 2 )
                {
                    $data['reg_type']   = "eldoret";
                }
                $data["name"]           =  $level[2];
                $data["age"]            =  $level[3];
                $data["phone_number"]   =  $phonenumber;
                $data["gender"]         =  $level[4];
                $data["activity_type"]  =  $level[5];
                $data["farming_type"]   =  $level[6];
                $data["farm_size"]      =  $level[7];
                $data["location"]       =  $level[8];
                $data["attendance"]     =  $level[9];
                $data["learning"]       =  $level[10];
                $data["channel"]        =  "USSD";
               // saveFarmers($data);
                echo   "END  Thank you ".$level[2].". You are now a member of Farm Kenya Connect";

            }
        }
    }
    function saveFarmers($arr)
    {
        $nconn = @mysqli_connect("localhost", "user_siud", 'bf9ac6aee1b248e',
            "farmers", 3306);
        $sql = "insert into subscribers set name = '{$arr['name']}', age = '{$arr['age']}', phone_number = '{$arr['phone_number']}',"
               . " gender =  '{$arr['gender']}', farming_type = '{$arr['farming_type']}', learning = '{$arr['learning']}', activity_type = '{$arr['activity_type']}', attendance = '{$arr['attendance']}', farm_size = '{$arr['farm_size']}',"
               . " location = '{$arr['location']}', channel = '{$arr['channel']}', reg_type = '{$arr['reg_type']}'";

        if( mysqli_query($nconn, $sql) === FALSE )
        {

            echo "END Error processing your request";
        }

        mysqli_close($nconn);
    }
    function checkFarmer($number, $exit)
    {
        $nconn  =   @mysqli_connect("localhost", "user_siud", 'bf9ac6aee1b248e',"farmers", 3306);
        $sql    =   "select * from subscribers where   phone_number = '{$number}'";
        $res    =   mysqli_query($nconn, $sql);
        if(mysqli_num_rows($res)>0)
        {
            die("END You are already registered \n".$exit);
        }

    }
    function get_alert($products_category_id){
        $nconn = @mysqli_connect("localhost", "user_siud", 'bf9ac6aee1b248e',
            "sg_sms_db", 3306);

        $sql = "select msg from sub_product_content_msg_ where products_category_id='{$products_category_id}' and status=7 order by id desc limit 1";
        $row = mysqli_fetch_assoc( mysqli_query($nconn, $sql) );
        $msg = sanitize_from_word( $row['msg'] );
        $msg = substr($msg, 0, 160);
        echo "END ".$msg;

        mysqli_close($nconn);
    }

    function sanitize_from_word( $content ){
        // Convert microsoft special characters
        $replace = array(
            "‘" => "'",
            "’" => "'",
            "”" => '"',
            "“" => '"',
            "–" => "-",
            "—" => "-",
            "…" => "&#8230;"
        );

        foreach($replace as $k => $v)
        {
            $content = str_replace($k, $v, $content);
        }

        // Remove any non-ascii character
        $content = preg_replace('/[^\x20-\x7E]*/','', $content);

        return $content;
    }

    function feedbackData() {

      header('Content-type: application/json');

      $nconn  =   @mysqli_connect("localhost", "user_siud", 'bf9ac6aee1b248e',"farmers", 3306);
      $sql    =   "select * from feedback";
      $res    =   mysqli_query($nconn, $sql);
      if(mysqli_num_rows($res)>0)
      {
          $data = $res -> fetch_all(MYSQLI_ASSOC);
      }
      echo json_encode( $data );
    }

    // if(isset($_GET['get-data'])){
    //     die(feedbackData());
    // }

?>
