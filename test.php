<?php
function hostpinnacleSmsReport($uuid)
{
    $userid = "umeskia";
    $password = "z18aypEW";
    $url = "https://smsportal.hostpinnacle.co.ke/SMSApi/report/status?userid=$userid&password={$password}&uuid={$uuid}&output=json";
    $response = file_get_contents($url);
    return $response;
}
$uuid = "8403414672158573510";
$response = hostpinnacleSmsReport($uuid);
$data = json_decode($response, true);
$status = $data['response']['status'];
$uuid = $data['response']['report_statusList'][0]['status']['uuid'];
$msgId = $data['response']['report_statusList'][0]['status']['msgId'];
$length = $data['response']['report_statusList'][0]['status']['length'];
$msgType = $data['response']['report_statusList'][0]['status']['msgType'];
$StatusDelivery = $data['response']['report_statusList'][0]['status']['Status'];
$mobileNo = $data['response']['report_statusList'][0]['status']['mobileNo'];
$text = $data['response']['report_statusList'][0]['status']['text'];
$deliveryStatus = $data['response']['report_statusList'][0]['status']['Status'];
$submittedTime = $data['response']['report_statusList'][0]['status']['Submitted Time'];
$deliveredTime = $data['response']['report_statusList'][0]['status']['Delivered Time'];

// Output the data
echo "Status: $status\n";
echo "Message ID: $msgId\n";
echo "Mobile Number: $mobileNo\n";
echo "Text: $text\n";
echo "Delivery Status: $deliveryStatus\n";
echo "Submitted Time: $submittedTime\n";
echo "Delivered Time: $deliveredTime\n";


