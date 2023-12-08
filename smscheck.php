<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    include 'config.php';
    $ResultCode = '';
    $EncodeData = file_get_contents('php://input');
    $submitedData = json_decode($EncodeData, true);

    $ResultCode = "200";
    $massage = "your apikey sent sucessfully.";
    $response = array(
        'success' => $ResultCode,
        'massage' => $massage,
        'api_key' => "6198b32ccfeb7"
    );
} else {
    $ResultCode = "Error";
    $massage = "Invelid Request method";
    $response = array(
        'ResultCode' => $ResultCode,
        'errorMessage' => $massage
    );
}

if (!$ResultCode == '') {
    echo json_encode($response);
}