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
$cmserver = $rowsettings["cmserver"];

// Check Registration
echo("Checking Registration\n");
if ($regstatus == 0) {
  $regurl = "https://" . $cmserver . "/remote/register";
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
echo("Checking Tasks\n");
$taskurl = "https://" . $cmserver . "/remote/checktasks";
$postchtasks = curl_init($taskurl);
curl_setopt($postchtasks, CURLOPT_CUSTOMREQUEST, "GET");
curl_setopt($postchtasks, CURLOPT_RETURNTRANSFER, true);
$taskjson = curl_exec($postchtasks);
$taskarr = json_decode($taskjson);
print_r($taskarr);
?>