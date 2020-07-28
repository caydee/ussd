<?php
header('Content-type: text/plain');

$phone = $_GET['MSISDN'];
$session_id = $_GET['SESSION_ID'];
$service_code = $_GET['SERVICE_CODE'];
$ussd_string= $_GET['USSD_STRING'];

//set default level to zero
$level = 0;

$ussd_string_exploded = explode ("*",$ussd_string);

// Get menu level from ussd_string reply
$level = count($ussd_string_exploded);

if($level == 1 or $level == 0){
    
    display_menu(); // show the home/first menu
}

if ($level > 1)
{

    if ($ussd_string_exploded[0] == "1")
    {
        // If user selected 1 send them to the registration menu
        register($ussd_string_exploded,$phone, $dbh);
    }

  else if ($ussd_string_exploded[0] == "2"){
        //If user selected 2, send them to the about menu
        about($ussd_string_exploded);
    }
}

function ussd_proceed($ussd_text){
    echo "CON $ussd_text";
}

function ussd_stop($ussd_text){
    echo "END $ussd_text";
}

//This is the home menu function
function display_menu()
{
    $ussd_text =    "1. Register \n 2. About \n"; // add \n so that the menu has new lines
    ussd_proceed($ussd_text);
}


// Function that hanldles About menu
function about($ussd_text)
{
    $ussd_text =    "USSD Test Application";
    ussd_stop($ussd_text);
}

// Function that handles Registration menu
function register($details,$phone){
    if(count($details) == 2)
    {
        
        $ussd_text = "Please enter your Full Name and Email, each seperated by commas:";
        ussd_proceed($ussd_text); // ask user to enter registration details
    }
    if(count($details)== 3)
    {
        if (empty($details[1])){
                $ussd_text = "Sorry we do not accept blank values";
                ussd_proceed($ussd_text);
        } else {
        $input = explode(",",$details[1]);//store input values in an array
        $full_name = $input[0];//store full name
        $email = $input[1];//store email
        $phone_number =$phone;//store phone number 

        
            $ussd_text = $full_name." your registration was successful. Your email is ".$email." and phone number is ".$phone_number;
            ussd_proceed($ussd_text);       
    }
}
}
?>