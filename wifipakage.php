<?php
include "dbconnection.php";
if (isset($_SERVER['HTTP_ORIGIN'])) {
  // Decide if the origin in $_SERVER['HTTP_ORIGIN'] is one
  // you want to allow, and if so:
  header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
  header('Access-Control-Allow-Credentials: true');
  header('Access-Control-Max-Age: 86400');    // cache for 1 day
}
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  $ResultCode = '';
  $EncodeData = file_get_contents('php://input');
  $submitedData = json_decode($EncodeData, true);
  $stationId = $_POST['stationId'];
  if (empty($stationId)) {
    $ResultCode = "Error";
    $massage = "The station id is empty";
    $response = array(
      'ResultCode' => $ResultCode,
      'massage' => $massage
    );
    if (!$ResultCode == '') {
      echo json_encode($response, JSON_PRETTY_PRINT);
    }
  } else {
    $getWifiStations =  mysqli_query($db, "SELECT * FROM wifistation WHERE stationId='$stationId' AND status='Active'");
    if (mysqli_num_rows($getWifiStations) > 0) {

      $getStation =  mysqli_query($db, "SELECT * FROM stationPlans WHERE stationId='$stationId' AND status='Active' ORDER BY expiration ASC");
      if (mysqli_num_rows($getStation) > 0) {
        $pakages = array();
        while ($dataInfo = mysqli_fetch_array($getStation)) {
          $plancode = $dataInfo['plancode'];
          $price = $dataInfo['amount'];
          $ResultCode = "200";
          $massage = "Request sent sucessfully. $stationId";
          //GETING PLAN TYPE
          $plantype = $dataInfo['plantype'];
          if ($plantype == "connTime") {
            //cheching Expiration
            if ($dataInfo['exprationtype'] == "d") {
              $months = $dataInfo['expiration'] / 30;
              if ($months == 1) {
                $nunExpiration = "1";
              } else if ($dataInfo['expiration'] == 7) {
                $nunExpiration = "1";
              } else {
                if ($months > 1) {
                  $nunExpiration = $months;
                }
              }
            } else {
              $nunExpiration = $dataInfo['expiration'];
            }


            //GETING DURATION TYPE
            if ($dataInfo['exprationtype'] == "m") {
              if ($dataInfo['expiration'] > 1) {
                $stringExpiration = "Minutes";
              } else {
                $stringExpiration = "Minutes";
              }
            } else if ($dataInfo['exprationtype'] == "h") {
              if ($dataInfo['expiration'] > 1) {
                $stringExpiration = "Hours";
              } else {
                $stringExpiration = "Hour";
              }
            } else if ($dataInfo['exprationtype'] == "d") {
              $months = $dataInfo['expiration'] / 30;
              if ($months == 1) {
                $stringExpiration = "Month";
              } else if ($dataInfo['expiration'] == 7) {
                $stringExpiration =  "1 Week";
              } else {
                if ($months > 1) {
                  $stringExpiration = $months . " Months";
                }
              }
            } else if ($dataInfo['exprationtype'] == "w") {
              if ($dataInfo['expiration'] > 1) {
                $stringExpiration = $dataInfo['expiration'] . " Weeks";
              } else {
                $stringExpiration = $dataInfo['expiration'] . " Week";
              }
            }
          } else {
            $nunExpiration = $dataInfo['dataLimit'];
            $stringExpiration = "MBS";
          }

          $pakage = array(
            'plancode' =>  $plancode,
            'price' => $price,
            'numExpration' => $nunExpiration,
            'stringExpiration' => $stringExpiration,
          );
          array_push($pakages, $pakage);
        }
        $response = array('ResultCode' => $ResultCode, 'massage' => $massage, "statationPakages" => $pakages);
        echo  json_encode($response, JSON_UNESCAPED_SLASHES);
      } else {
        $ResultCode = "Error";
        $massage = "No active pakage in station $stationId";
        $response = array(
          'ResultCode' => $ResultCode,
          'massage' => $massage
        );
        if (!$ResultCode == '') {
          //echo json_encode($response);
          echo json_encode($response, JSON_PRETTY_PRINT);
        }
      }
    } else {
      $ResultCode = "Error";
      $massage = "This station id $stationId is not registred  is inactive";
      $response = array(
        'ResultCode' => $ResultCode,
        'massage' => $massage
      );
      if (!$ResultCode == '') {
        //echo json_encode($response);
        echo json_encode($response, JSON_PRETTY_PRINT);
      }
    }
  }
} else {
  $ResultCode = "Error";
  $massage = "Invelid Request method";
  $response = array(
    'ResultCode' => $ResultCode,
    'massage' => $massage
  );
  if (!$ResultCode == '') {
    //echo json_encode($response);
    echo json_encode($response, JSON_PRETTY_PRINT);
  }
}