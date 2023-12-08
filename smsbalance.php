<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    include 'config.php';
    $ResultCode = '';
    $EncodeData = file_get_contents('php://input');
    $submitedData = json_decode($EncodeData, true);
    $api_key = mysqli_escape_string($db, $submitedData['api_key']);
    $email = mysqli_escape_string($db, $submitedData['email']);
    if ($api_key == '' or $email == '') {
        $ResultCode = "102";
        $massage = "Request is missing required query parameter!";
        $response = array(
            'ResultCode' => $ResultCode,
            'errorMessage' => $massage
        );
    } else {
        $checkcridential = mysqli_query($db, "SELECT *  FROM ums_users  WHERE email='$email'");
        if (mysqli_num_rows($checkcridential) > 0) {
            $checkdata = mysqli_query($db, "SELECT *  FROM ums_users  WHERE email='$email' AND apiKey='$api_key'");
            if (mysqli_num_rows($checkdata) > 0) {
                $getCreadit = mysqli_fetch_array(mysqli_query($db, "SELECT * FROM umeskiaservice WHERE email='$email' AND productName='sms'"));
                $creditBalance = $getCreadit['accountBalance'];
                $ResultCode = "200";
                $response = array(
                    'success' => $ResultCode,
                    'creditBalance' => $creditBalance
                );
            } else {
                $ResultCode = "Error";
                $massage = "Invalid  Api key : $api_key, please try again";
                $response = array(
                    'ResultCode' => $ResultCode,
                    'errorMessage' => $massage
                );
            }
        } else {
            $ResultCode = "Error";
            $massage = "Invalid email";
            $response = array(
                'ResultCode' => $ResultCode,
                'errorMessage' => $massage
            );
        }
    }
} else {
    $ResultCode = "Error";
    $massage = "Invalid Request method";
    $response = array(
        'ResultCode' => $ResultCode,
        'errorMessage' => $massage
    );
}

if (!$ResultCode == '') {
    echo json_encode($response);
}