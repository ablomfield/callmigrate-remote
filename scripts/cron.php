<?php
// Import Settings
$yamlsettings = yaml_parse_file('/opt/callmigrate/settings.yaml');
$dbserver = $yamlsettings['Database']['ServerName'];
$dbuser = $yamlsettings['Database']['Username'];
$dbpass = $yamlsettings['Database']['Password'];
$dbname = $yamlsettings['Database']['DBName'];

// Load Settings
$dbconn = new mysqli($dbserver, $dbuser, $dbpass, $dbname);
if ($dbconn->connect_error) {
  die("Connection failed: " . $dbconn->connect_error);
}
$rssettings = mysqli_query($dbconn, "SELECT * FROM settings") or die("Error in Selecting " . mysqli_error($dbconn));
$rowsettings = mysqli_fetch_assoc($rssettings);
$regstatus = $rowsettings["regstatus"];
$clientid = $rowsettings["clientid"];
$clientsecret = $rowsettings["clientsecret"];
$cmserver = $rowsettings["cmserver"];

// Check Registration
echo ("Checking Registration\n");
if ($regstatus == 0) {
  $regurl = "https://" . $cmserver . "/remote/register/";
  $getchreg = curl_init($regurl);
  curl_setopt($getchreg, CURLOPT_CUSTOMREQUEST, "GET");
  curl_setopt($getchreg, CURLOPT_RETURNTRANSFER, true);
  $regjson = curl_exec($getchreg);
  $regarr = json_decode($regjson);
  $clientid = $regarr->clientid;
  $clientsecret = $regarr->clientsecret;
  $regsql = "UPDATE settings SET regstatus = 1, clientid = '$clientid', clientsecret = '$clientsecret'";
  $dbconn->query($regsql);
}

// Check Tasks
echo ("Checking Tasks\n");
$taskurl = "https://" . $cmserver . "/remote/tasks/list/";
$postchtasks = curl_init($taskurl);
curl_setopt($postchtasks, CURLOPT_CUSTOMREQUEST, "POST");
curl_setopt($postchtasks, CURLOPT_RETURNTRANSFER, true);
curl_setopt(
  $postchtasks,
  CURLOPT_POSTFIELDS,
  http_build_query(array(
    'clientid' => $clientid,
    'clientsecret' => $clientsecret
  ))
);
$taskjson = curl_exec($postchtasks);
$taskarr = json_decode($taskjson);
print_r($taskarr->tasklist);
echo ("\n");
if ($taskarr->status == 200) {
  echo ("Status 200\n");
  $tasknum = count($taskarr->tasklist);
  echo ("Found $tasknum tasks.\n");
  for ($x = 0; $x <= $tasknum - 1; $x++) {
    echo "Task # " . $taskarr->tasklist[$x]->id . " (" . $taskarr->tasklist[$x]->action . ") - " . $taskarr->tasklist[$x]->description . "\n";
    if ($taskarr->tasklist[$x]->action == "CHECKIN") {
      $compurl = "https://" . $cmserver . "/remote/tasks/complete/";
      $postchcomp = curl_init($compurl);
      curl_setopt($postchcomp, CURLOPT_CUSTOMREQUEST, "POST");
      curl_setopt($postchcomp, CURLOPT_RETURNTRANSFER, true);
      curl_setopt(
        $postchcomp,
        CURLOPT_POSTFIELDS,
        http_build_query(array(
          'clientid' => $clientid,
          'clientsecret' => $clientsecret,
          'taskid' => $taskarr->tasklist[$x]->id
        ))
      );
      $compjson = curl_exec($postchcomp);
    } elseif ($taskarr->tasklist[$x]->action == "SYNCTUNNELS") {

    } elseif ($taskarr->tasklist[$x]->action == "RESTARTTUNNELS") {
      exec('sudo service callmigrate-tunnel restart');
      $compurl = "https://" . $cmserver . "/remote/tasks/complete/";
      $postchcomp = curl_init($compurl);
      curl_setopt($postchcomp, CURLOPT_CUSTOMREQUEST, "POST");
      curl_setopt($postchcomp, CURLOPT_RETURNTRANSFER, true);
      curl_setopt(
        $postchcomp,
        CURLOPT_POSTFIELDS,
        http_build_query(array(
          'clientid' => $clientid,
          'clientsecret' => $clientsecret,
          'taskid' => $taskarr->tasklist[$x]->id
        ))
      );
      $compjson = curl_exec($postchcomp);
    }
  }
} else {
  echo ("Status " . $taskarr->status . "\n");
}
