<?php

$servername = "localhost";
$username = "user_siud";
$password = "bf9ac6aee1b248e";
$dbname = "farmers";
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
$temp_var_date = date('Y-m-d H:i:s');
$temp_var_date_1 = date('Y-m-d H:i:s',strtotime('-5 hour -6 minutes',strtotime($temp_var_date))); // timeouts...not used for now


$query = "SELECT * FROM feedback WHERE status = 0" ;


$result = $conn->query($query);
 if ($result->num_rows > 0) {

    $i=0;
  while($row = $result->fetch_assoc()) {
        $msg_address = $row["phonenumber"];
        $comments = $row["comments"];
        $name = $row["customername"];
        $msg_timeStamp = $row["feedbacktime"];
        $id = $row["id"];

        $msg_feedback = array
              (
                 'msisdn' => $msg_address,'message' => $comments,'customername' => $name,'feedbacktime' => $msg_timeStamp,
              );
        $sql_u = "UPDATE feedback SET status = 0 WHERE id=".$row['id']."" ;
        $rst = $conn->query($sql_u);
        if ($conn->query($sql_u) === TRUE) {
            echo "Updated successfully";
         }

	print_r($msg_feedback);

		    feeback_delivery($msg_feedback);
    	  unset($msg_feedback);
        $i++;
  }

}else{
  echo "No content";
}


function feeback_delivery($msg_feedback){

        $ch = curl_init('http://feedback.standardmedia.co.ke/USSD/feedback.php');
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
	$msg_feedback = json_encode($msg_feedback);       
	 curl_setopt($ch, CURLOPT_POSTFIELDS, $msg_feedback);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Content-Length: ' . strlen($msg_feedback))
        );

        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);

        $result = curl_exec($ch);

	print_r($result);
        curl_close($ch);

}

 ?>
