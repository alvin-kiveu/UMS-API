<?php
header("Content-Type: application/json");
$stkCallbackResponse = file_get_contents('php://input');
$logFile = "TestUMSPayCallbackMpesaStkResponse.json";
$log = fopen($logFile, "a");
fwrite($log, $stkCallbackResponse);
fclose($log);