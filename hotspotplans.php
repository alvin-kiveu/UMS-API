<?php
include 'config.php';
if (isset($_SERVER['HTTP_ORIGIN'])) {
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
    $ResultCode = "201";
    $massage = "Station Id is required, Please insert station Id. $stationId";
    $response = array(
      'ResultCode' => $ResultCode,
      'massage' => $massage
    );
    if (!$ResultCode == '') {
      echo json_encode($response, JSON_PRETTY_PRINT);
    }
  } else {
    $getWifiStations =  mysqli_query($db, "SELECT * FROM wifistation WHERE stationId='$stationId'");
    $wifiStationInfo = mysqli_fetch_array($getWifiStations);
    $stationCoustomerCare = $wifiStationInfo['coustomerCare'];
    $stationStatus = $wifiStationInfo['status'];
    if (mysqli_num_rows($getWifiStations) > 0) {
      if ($stationStatus == "Active") {
        $getStation =  mysqli_query($db, "SELECT * FROM stationPlans WHERE stationId='$stationId' AND status='Active' AND version='v2' ORDER BY amount ASC");
        if (mysqli_num_rows($getStation) > 0) {
          $pakages = array();
          while ($dataInfo = mysqli_fetch_array($getStation)) {
            $plancode = $dataInfo['plancode'];
            $price = $dataInfo['amount'];
            $connectiontype = $dataInfo['connectiontype'];
            $numberOfDevices = $dataInfo['device'];
            if ($numberOfDevices == 0) {
              $devices = "Unlimited";
            } else {
              $devices = $numberOfDevices;
            }
            $ResultCode = "200";
            $massage = "Request sent sucessfully. $stationId";
            //GETING PLAN TYPE
            $plantype = $dataInfo['plantype'];
            if ($plantype == "time") {
              //cheching Expiration
              $planConType = "Time plan";
              if ($dataInfo['exprationtype'] == "d") {
                if ($dataInfo['expiration'] == 1) {
                  $nunExpiration = "1";
                } else if ($dataInfo['expiration'] == 7) {
                  $nunExpiration = "1";
                } else if ($dataInfo['expiration'] == 30) {
                  $nunExpiration = "1";
                } else {
                  $nunExpiration = $dataInfo['expiration'];
                }
              } else if ($dataInfo['exprationtype'] == "w") {
                if ($dataInfo['expiration'] == 1) {
                  $nunExpiration = "1";
                } else if ($dataInfo['expiration'] == 4) {
                  $nunExpiration = "1";
                } else {
                  $nunExpiration = $dataInfo['expiration'];
                }
              } else {
                $nunExpiration = $dataInfo['expiration'];
              }


              //GETING DURATION TYPE
              if ($dataInfo['exprationtype'] == "m") {
                if ($dataInfo['expiration'] > 1) {
                  $stringExpiration = " Minutes";
                } else {
                  $stringExpiration = " Minutes";
                }
              } else if ($dataInfo['exprationtype'] == "h") {
                if ($dataInfo['expiration'] > 1) {
                  $stringExpiration = " Hours";
                } else {
                  $stringExpiration = " Hour";
                }
              } else if ($dataInfo['exprationtype'] == "d") {
                $months = $dataInfo['expiration'] / 30;
                if ($months == 1) {
                  $stringExpiration = " Month";
                } else if ($dataInfo['expiration'] == 7) {
                  $stringExpiration =  " Week";
                } else {
                  if ($dataInfo['expiration'] == 1) {
                    $stringExpiration = " Day";
                  } else {
                    $stringExpiration = " Days";
                  }
                }
              } else if ($dataInfo['exprationtype'] == "w") {
                if ($dataInfo['expiration'] == 4) {
                  $stringExpiration = " Month";
                } else if (($dataInfo['expiration'] > 1)) {
                  $stringExpiration = " Weeks";
                } else {
                  $stringExpiration = " Week";
                }
              }else if ($dataInfo['exprationtype'] == "mn") {
                if ($dataInfo['expiration'] == 1) {
                  $stringExpiration = " Month";
                } else {
                  $stringExpiration = " Months";
                }
              }else if ($dataInfo['exprationtype'] == "y") {
                if ($dataInfo['expiration'] == 1) {
                  $stringExpiration = " Year";
                } else {
                  $stringExpiration = " Years";
                }
              }




            } else if ($plantype == "data") {
              $nunExpiration = $dataInfo['dataLimit'];
              if ($nunExpiration >= 1000) {
                $nunExpiration = $nunExpiration / 1000;
                $stringExpiration = "GB";
              } else if ($nunExpiration >= 1000000) {
                $nunExpiration = $nunExpiration / 1000000;
                $stringExpiration = "TB";
              } else {
                $nunExpiration = $dataInfo['dataLimit'];
                $stringExpiration = "MB";
              }

              $planConType = "Data plan";
            }else if($plantype == "speed") {
              $nunExpiration = $dataInfo['dataLimit'];
              //IF  $nunExpiration IS 5M/5M THEN IT WILL BE 5 MBS SO GET THE FIRST NUMBER ONLY
              $nunExpiration = substr($nunExpiration, 0, 1);
              $stringExpiration = "Mbps";
              $planConType = "Speed plan";
            }

            if ($connectiontype == "usedtime") {
              $validity = "Unlimited";
            } else {
              if ($plantype == "data" || $plantype == "speed") {
                
                if ($dataInfo['exprationtype'] == "d") {
                  if ($dataInfo['expiration'] == 1) {
                    $validityNumExp = "1";
                  } else if ($dataInfo['expiration'] == 7) {
                    $validityNumExp = "1";
                  } else if ($dataInfo['expiration'] == 30) {
                    $validityNumExp = "1";
                  } else {
                    $validityNumExp = $dataInfo['expiration'];
                  }
                } else if ($dataInfo['exprationtype'] == "w") {
                  if ($dataInfo['expiration'] == 1) {
                    $validityNumExp = "1";
                  } else if ($dataInfo['expiration'] == 4) {
                    $validityNumExp = "1";
                  } else {
                    $validityNumExp = $dataInfo['expiration'];
                  }
                } else if ($dataInfo['exprationtype'] == "mn") {
                  if ($dataInfo['expiration'] == 1) {
                    $validityNumExp = "1";
                  } else {
                    $validityNumExp = $dataInfo['expiration'];
                  }
                } else if ($dataInfo['exprationtype'] == "y") {
                  if ($dataInfo['expiration'] == 1) {
                    $validityNumExp = "1";
                  } else {
                    $validityNumExp = $dataInfo['expiration'];
                  }
                } else {
                  $validityNumExp = $dataInfo['expiration'];
                }



                //GETING DURATION TYPE
                if ($dataInfo['exprationtype'] == "m") {
                  if ($dataInfo['expiration'] > 1) {
                    $validityStringexp = " Minutes";
                  } else {
                    $validityStringexp = " Minutes";
                  }
                } else if ($dataInfo['exprationtype'] == "h") {
                  if ($dataInfo['expiration'] > 1) {
                    $validityStringexp = " Hours";
                  } else {
                    $validityStringexp = " Hour";
                  }
                } else if ($dataInfo['exprationtype'] == "d") {
                  $months = $dataInfo['expiration'] / 30;
                  if ($months == 1) {
                    $validityStringexp = " Month";
                  } else if ($dataInfo['expiration'] == 7) {
                    $validityStringexp =  " Week";
                  } else {
                    if ($dataInfo['expiration'] == 1) {
                      $validityStringexp = " Day";
                    } else {
                      $validityStringexp = " Days";
                    }
                  }
                } else if ($dataInfo['exprationtype'] == "w") {
                  if ($dataInfo['expiration'] == 4) {
                    $validityStringexp = " Month";
                  } else if (($dataInfo['expiration'] > 1)) {
                    $validityStringexp = " Weeks";
                  } else {
                    $validityStringexp = " Week";
                  }
                }else if ($dataInfo['exprationtype'] == "mn") {
                  if ($dataInfo['expiration'] == 1) {
                    $validityStringexp = " Month";
                  } else {
                    $validityStringexp = " Months";
                  }
                }else if ($dataInfo['exprationtype'] == "y") {
                  if ($dataInfo['expiration'] == 1) {
                    $validityStringexp = " Year";
                  } else {
                    $validityStringexp = " Years";
                  }
                }



                $validity = $validityNumExp . '' . $validityStringexp;
              } else {
                  $validity = $nunExpiration . '' . $stringExpiration;
              }
            }
            $adsImage = "<a href='https://umeskiasoftwares.com/ums?bp=XCRaX5CZAkgt'><img src='https://umeskiasoftwares.com/products/1683840182.png' alt='umeskia_softwares ads'></a>";


            $pakage = array(
              'plancode' =>  $plancode,
              'price' => $price,
              'numExpration' => $nunExpiration,
              'stringExpiration' => $stringExpiration,
              'plantype' => $planconntype,
              'planConType' => $planConType,
              'validity' => $validity,
              'devices' => $devices
            );
            array_push($pakages, $pakage);
          }
          $response = array('ResultCode' => $ResultCode, 'massage' => $massage,  'coustomercare' => $stationCoustomerCare, 'adsImage' => $adsImage, 'statationPakages' => $pakages);
          echo  json_encode($response, JSON_UNESCAPED_SLASHES);
        } else {
          $ResultCode = "201";
          $massage = "Hotspot installed but no plan is available, please contact your hotspot provider  : $stationCoustomerCare.";
          $response = array(
            'ResultCode' => $ResultCode,
            'massage' => $massage,
            'coustomercare' => $stationCoustomerCare
          );
          if (!$ResultCode == '') {
            echo json_encode($response, JSON_PRETTY_PRINT);
          }
        }
      } else {
        $ResultCode = "201";
        // $massage = "Hotspot is deactivated, please contact your hotspot provider  : $stationCoustomerCare. ";
        $massage = "<span style='color:black; font-weight:600;'>We apologize for the inconvenience, but our hotspot service is currently unavailable due to a server upgrade that is currently in progress.
       <br><br><br><br>
       This upgrade is necessary to improve our service and provide you with a better user experience.
       </span>";
        $response = array(
          'ResultCode' => $ResultCode,
          'massage' => $massage,
          'coustomercare' => $stationCoustomerCare
        );
        if (!$ResultCode == '') {
          echo json_encode($response, JSON_PRETTY_PRINT);
        }
      }
    } else {
      $ResultCode = "201";
      $massage = "This station id $stationId is not registred  or is incorrect";
      $response = array(
        'ResultCode' => $ResultCode,
        'massage' => $massage,
        'coustomercare' => $stationCoustomerCare
      );
      if (!$ResultCode == '') {
        echo json_encode($response, JSON_PRETTY_PRINT);
      }
    }
  }
} else {
  $ResultCode = "201";
  $massage = "Invelid Request method";
  $response = array(
    'ResultCode' => $ResultCode,
    'massage' => $massage
  );
  if (!$ResultCode == '') {
    echo json_encode($response, JSON_PRETTY_PRINT);
  }
}
