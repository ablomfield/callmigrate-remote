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

// Open Log File
$logfile = fopen("/opt/callmigrate/logs/callmigrate.log", "a") or die("Unable to open file!");
fwrite($logfile, "\n" . date("Y-m-d h:i:sa") . " - Checking for tasks.");

// Check Registration
echo ("Checking Registration\n");
if ($regstatus == 0) {
  fwrite($logfile, "\n" . date("Y-m-d h:i:sa") . " - CallMigrate Remote not registered. Starting registration process.");
  $regurl = "https://" . $cmserver . "/remote/register/";
  $getchreg = curl_init($regurl);
  curl_setopt($getchreg, CURLOPT_CUSTOMREQUEST, "POST");
  curl_setopt($getchreg, CURLOPT_RETURNTRANSFER, true);
  $regjson = curl_exec($getchreg);
  $regarr = json_decode($regjson);
  $clientid = $regarr->clientid;
  $clientsecret = $regarr->clientsecret;
  $remoteuser = $regarr->remoteuser;
  fwrite($logfile, "\n" . date("Y-m-d h:i:sa") . " - Retrieved registration details.");
  $regsql = "UPDATE settings SET regstatus = 1, clientid = '$clientid', clientsecret = '$clientsecret', cmremuser = '$remoteuser'";
  $dbconn->query($regsql);
  exec("ssh-keygen -t rsa -N '' -f ~/.ssh/id_rsa");
  fwrite($logfile, "\n" . date("Y-m-d h:i:sa") . " - Generating RSA keys.");
  exec("sudo cp ~/.ssh/id_rsa* /root/.ssh");
  fwrite($logfile, "\n" . date("Y-m-d h:i:sa") . " - Copying keys to root.");
  $sshkey = fopen("/home/cmadmin/.ssh/id_rsa.pub", "r") or die("Unable to open file!");
  $pubkey = fread($sshkey,filesize("/home/cmadmin/.ssh/id_rsa.pub"));
  $keyurl = "https://" . $cmserver . "/remote/tasks/complete/";
  $postchkey = curl_init($keyurl);
  curl_setopt($postchkey, CURLOPT_CUSTOMREQUEST, "POST");
  curl_setopt($postchkey, CURLOPT_RETURNTRANSFER, true);
  curl_setopt(
    $postchkey,
    CURLOPT_POSTFIELDS,
    http_build_query(array(
      'clientid' => $clientid,
      'clientsecret' => $clientsecret,
      'sshkey' => $pubkey
    ))
  );
  $keyjson = curl_exec($postchkey);
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
if ($taskarr->status == 200 && $taskarr->tasks > 0) {
  $tasknum = count($taskarr->tasklist);
  echo ("Found $tasknum tasks.\n");
  fwrite($logfile, "\n" . date("Y-m-d h:i:sa") . " - Found $tasknum tasks.");
  for ($x = 0; $x <= $tasknum - 1; $x++) {
    echo "Task # " . $taskarr->tasklist[$x]->id . " (" . $taskarr->tasklist[$x]->action . ") - " . $taskarr->tasklist[$x]->description . "\n";
    if ($taskarr->tasklist[$x]->action == "CHECKIN") {
      fwrite($logfile, "\n" . date("Y-m-d h:i:sa") . " - Executing check in.");
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
      fwrite($logfile, "\n" . date("Y-m-d h:i:sa") . " - Executing tunnel sync.");
      $tunnelurl = "https://" . $cmserver . "/remote/tunnels/";
      $postchtunnels = curl_init($tunnelurl);
      curl_setopt($postchtunnels, CURLOPT_CUSTOMREQUEST, "POST");
      curl_setopt($postchtunnels, CURLOPT_RETURNTRANSFER, true);
      curl_setopt(
        $postchtunnels,
        CURLOPT_POSTFIELDS,
        http_build_query(array(
          'clientid' => $clientid,
          'clientsecret' => $clientsecret
        ))
      );
      $tunneljson = curl_exec($postchtunnels);
      //print_r($tunneljson);
      echo("Deleting existing tunnels\n");
      fwrite($logfile, "\n" . date("Y-m-d h:i:sa") . " - Deleting existing tunnels.");
      $tunnelarr = json_decode($tunneljson);
      $tunnelcount = count($tunnelarr->tunnellist);
      fwrite($logfile, "\n" . date("Y-m-d h:i:sa") . " - Syncronizing $tunnelcount tunnels.");
      echo("Syncronizing $tunnelcount tunnels\n");
      mysqli_query($dbconn,"DELETE FROM tunnels");
      for ($y = 0; $y <= $tunnelcount - 1; $y++) {
        fwrite($logfile, "\n" . date("Y-m-d h:i:sa") . " - Adding " . $tunnelarr->tunnellist[$y]->application . " tunnel. " . $tunnelarr->tunnellist[$y]->remotehost . ":" . $tunnelarr->tunnellist[$y]->remoteport . " to " . $tunnelarr->tunnellist[$y]->tunnelport . ".");
        echo("Adding " . $tunnelarr->tunnellist[$y]->application . " tunnel. " . $tunnelarr->tunnellist[$y]->remotehost . ":" . $tunnelarr->tunnellist[$y]->remoteport . " to " . $tunnelarr->tunnellist[$y]->tunnelport . "\n");
        mysqli_query($dbconn,"INSERT INTO tunnels (`tunnelname`, `tunnelport`, `localhost`, `localport`) VALUES ('" . $tunnelarr->tunnellist[$y]->application . "', '" . $tunnelarr->tunnellist[$y]->tunnelport . "', '" . $tunnelarr->tunnellist[$y]->remotehost . "', '" . $tunnelarr->tunnellist[$y]->remoteport . "')");
      }
      fwrite($logfile, "\n" . date("Y-m-d h:i:sa") . " - Restarting tunnel service.");
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
    } elseif ($taskarr->tasklist[$x]->action == "RESTARTTUNNELS") {
      fwrite($logfile, "\n" . date("Y-m-d h:i:sa") . " - Executing tunnel restart.");
      echo("Restarting tunnel service\n");
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
  echo ("No tasks found\n");
  fwrite($logfile, "\n" . date("Y-m-d h:i:sa") . " - No tasks found.");
}

// Close Log File
fclose($logfile);