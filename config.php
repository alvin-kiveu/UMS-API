<?php
$host = 'localhost';
$root = 'umeskias_umsportal_user';
$pass = 'iLimAb1Yt0h2';
$database = 'umeskias_umsportal';


$DBHOST_LIVE = "localhost";
$DBUSERNAMELIVE = "umeskias_new_user_2_umeskia";
$DBPASSWORDLIVE = "Y6-w-I8)y{K@";
$DBDATABASELIVE = "umeskias_umeskia_main_db";

$db = mysqli_connect($DBHOST_LIVE, $DBUSERNAMELIVE, $DBPASSWORDLIVE, $DBDATABASELIVE);
if (!$db) {
    $msg =  "WEBSITE NOT CONNECTED TO THE DATABASE";
} else {
    $msg = "CONNECTED TO THE DATABASE";
}
$now = date_create();
$eaa = date_timestamp_get($now);
$time = $eaa + 10800;


 $hostmain="localhost";
  $usermain= "umeskias_umeskia_user";
  $passmain ="1qbejxBr%blT";
  $dbmain = "umeskias_umskiasoftwares";
  $connect = mysqli_connect($hostmain,$usermain,$passmain,$dbmain);
  if(!$connect){
      echo "DATABASE CONECTION ERROR";
  }
