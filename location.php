<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  include 'config.php';
  $ResultCode = '';
  $EncodeData = file_get_contents('php://input');
  $submitedData = json_decode($EncodeData, true);
  $data = mysqli_escape_string($db, $submitedData['data']);

  $getlocation = mysqli_query($db, "SELECT * FROM hotspotLocation");
  if (mysqli_num_rows($getlocation) > 0) {
    $locations = array();
    $num = 0;
    while ($row = mysqli_fetch_array($getlocation)) {
      $num++;
      $name = $row['Name'];
      $latitude = $row['latitude'];
      $longtude = $row['longtude'];
      $location = array(
        'name' => $name,
        'latitude' => $latitude,
        'longitude' => $longtude,
      );
      array_push($locations, $location);
    }

    $response = array("ResultCode" => "Success", "numHotspot" => $num, "locations" => $locations);
    print(json_encode($response, JSON_UNESCAPED_SLASHES, JSON_PRETTY_PRINT));
  } else {
    $ResultCode = "NO HOTSPOT LOCATION";
    $massage = "no wifi location available";
    $response = array(
      'ResultCode' => $ResultCode,
      'massage' => $massage,
    );
    if (!$ResultCode == '') {
      echo json_encode($response);
    }
  }
} else {
  $ResultCode = "Error";
  $massage = "Invalid Request method";
  $response = array(
    'ResultCode' => $ResultCode,
    'errorMessage' => $massage
  );
  if (!$ResultCode == '') {
    echo json_encode($response);
  }
}