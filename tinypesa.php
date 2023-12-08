<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
include 'config.php';

if ($_SERVER['REQUEST_METHOD'] == "POST") {
  $ResultCode = '';
  $data = file_get_contents('php://input');
  // Decode the JSON data
  $data = json_decode($data, true);
  $acctivetionkey = $data['acctivetionkey'];
  $siteurlData = $data['siteurl'];
  if ($acctivetionkey == '') {
    $ResultCode = "201";
    $massage = "acctivetionkey is required, please purchase a new Acctivetion key";
    $response = array(
      'ResultCode' => $ResultCode,
      'errorMessage' => $massage
    );
  } else {
    $result = mysqli_query($db,"SELECT * FROM activetion_tinypesa WHERE acctivetionKey = '$acctivetionkey'");
    if (mysqli_num_rows($result) > 0) {
      $rowData = mysqli_fetch_array($result);
      $siteurl = $rowData['siteurl'];
      //CHECK IF IT IS EMPTY
      if($siteurl == ""){
        //INSERT SITE URL
        mysqli_query($db,"UPDATE activetion_tinypesa SET siteurl = '$siteurlData' WHERE acctivetionKey = '$acctivetionkey'");
        $ResultCode = "200";
        $massage = "Success Site Updated";
        $response = array(
          'ResultCode' => $ResultCode,
          'successMessage' => $massage,
        );
      }else{
        //CHECK IF IT IS THE SAME
        if($siteurlData == $siteurl){
          $ResultCode = "200";
          $massage = "Success";
          $response = array(
            'ResultCode' => $ResultCode,
            'successMessage' => $massage,
          );
        }else{
          $ResultCode = "201";
          $massage = "This Activetion key is already in use on onther device,  please purchase a new Activetion key";
          $response = array(
            'ResultCode' => $ResultCode,
            'errorMessage' => $massage
          );
        }
      }
    } else {
      $ResultCode = "201";
      $massage = "Invalid acctivetionkey, please check your acctivetionkey and try again";
      $response = array(
        'ResultCode' => $ResultCode,
        'errorMessage' => $massage
      );
    }
  }
} else {
  $ResultCode = "201";
  $massage = "Invalid HTTPS request method request please use POST";
  $response = array(
    'ResultCode' => $ResultCode,
    'errorMessage' => $massage
  );
}
if (!$ResultCode == '') {
  echo json_encode($response);
}
