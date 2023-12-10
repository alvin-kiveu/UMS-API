<?php
$responseCodes = [
  1 => "The balance is insufficient for the transaction.",
  1032 => "Request cancelled by user",
  1037 => "DS timeout user cannot be reached",
  1025 => "An error occurred while sending a push request",
  2001 => "The initiator information is invalid.",
  9999 => "An error occurred while sending a push request.",
  1019 => "The organization receiving the funds is invalid.",
  1001 => "Unable to lock subscriber, a transaction is already in process for the current subscriber"
];

$ResultCode = 1032;
echo  $responseCodes[$ResultCode];




