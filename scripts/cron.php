<?php
// Retrieve Settings
include("/opt/callmigrate/html/includes/settings.php");

// Open Log File
$logfile = fopen("/opt/callmigrate/logs/callmigrate.log", "a") or die("Unable to open file!");

// Check Registration
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
  if (file_exists("/root/.ssh/id_rsa)")) {
    exec("rm -f /root/.ssh/id_rsa*");
  }
  exec("ssh-keygen -t rsa -N '' -f /root/.ssh/id_rsa");
  fwrite($logfile, "\n" . date("Y-m-d h:i:sa") . " - Generating RSA keys.");
  $sshkey = fopen("/root/.ssh/id_rsa.pub", "r") or die("Unable to open file!");
  $pubkey = fread($sshkey, filesize("/root/.ssh/id_rsa.pub"));
  $keyurl = "https://" . $cmserver . "/remote/register/sshkey/";
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
  mysqli_query($dbconn,"INSERT INTO `tasks` (`taskaction`) VALUES('SYNCTUNNELS')");
  mysqli_query($dbconn,"INSERT INTO `tasks` (`taskaction`) VALUES('RESTARTSERVICES')");
  $f = fopen('/opt/callmigrate/cronwatch/cron.now', 'w');
  fwrite($f, time());
  fclose($f);
}

// Check Tasks
fwrite($logfile, "\n" . date("Y-m-d h:i:sa") . " - Checking for remote tasks.");
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
if ($taskarr->status == 200 && $claimstatus == 0 && $taskarr->claimstatus == 1) {
  $custname = $taskarr->custname;
  $dbconn->query("UPDATE settings SET claimstatus = 1, custname = '$custname';");
}
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
      $tunnelarr = json_decode($tunneljson);
      if ($tunnelarr->tunnels > 0) {
        echo ("Deleting existing tunnels\n");
        fwrite($logfile, "\n" . date("Y-m-d h:i:sa") . " - Deleting existing tunnels.");
        $tunnelcount = count($tunnelarr->tunnellist);
        fwrite($logfile, "\n" . date("Y-m-d h:i:sa") . " - Syncronizing $tunnelcount tunnels.");
        echo ("Syncronizing $tunnelcount tunnels\n");
        mysqli_query($dbconn, "DELETE FROM tunnels");
        for ($y = 0; $y <= $tunnelcount - 1; $y++) {
          fwrite($logfile, "\n" . date("Y-m-d h:i:sa") . " - Adding " . $tunnelarr->tunnellist[$y]->application . " tunnel. " . $tunnelarr->tunnellist[$y]->remotehost . ":" . $tunnelarr->tunnellist[$y]->remoteport . " to " . $tunnelarr->tunnellist[$y]->tunnelport . ".");
          echo ("Adding " . $tunnelarr->tunnellist[$y]->application . " tunnel. " . $tunnelarr->tunnellist[$y]->remotehost . ":" . $tunnelarr->tunnellist[$y]->remoteport . " to " . $tunnelarr->tunnellist[$y]->tunnelport . "\n");
          mysqli_query($dbconn, "INSERT INTO tunnels (`tunnelname`, `tunnelport`, `localproto`, `localhost`, `localport`) VALUES ('" . $tunnelarr->tunnellist[$y]->application . "', '" . $tunnelarr->tunnellist[$y]->tunnelport . "', '" . $tunnelarr->tunnellist[$y]->remoteproto . "', '" . $tunnelarr->tunnellist[$y]->remotehost . "', '" . $tunnelarr->tunnellist[$y]->remoteport . "')");
        }
      } else {
        fwrite($logfile, "\n" . date("Y-m-d h:i:sa") . " - No tunnels to sync.");
        echo ("No tunnels to sync!\n");
      }
      fwrite($logfile, "\n" . date("Y-m-d h:i:sa") . " - Restarting tunnel service.");
      exec('service callmigrate-tunnel stop');
      sleep(5);
      exec('service callmigrate-tunnel start');
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
      echo ("Restarting tunnel service\n");
      exec('service callmigrate-tunnel stop');
      sleep(5);
      exec('service callmigrate-tunnel start');
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
    } elseif ($taskarr->tasklist[$x]->action == "RESTARTSERVICES") {
      fwrite($logfile, "\n" . date("Y-m-d h:i:sa") . " - Restarting services.");
      echo ("Restarting services\n");
      exec('service callmigrate-tunnel stop');
      sleep(5);
      exec('service callmigrate-tunnel start');
      exec('service callmigrate-cronwatch restart');
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
} elseif ($taskarr->status == 403) {
  $regsql = "UPDATE settings SET regstatus = 0, claimstatus = 0, custname = '', clientid = '', clientsecret = '', cmremuser = ''";
  $dbconn->query($regsql);
  echo ("Unregistering remote\n");
  $tunnelsql = "DELETE FROM tunnels";
  $dbconn->query($tunnelsql);
  exec('service callmigrate-tunnel stop');
  fwrite($logfile, "\n" . date("Y-m-d h:i:sa") . " - Unregistering remote.");
} else {
  fwrite($logfile, "\n" . date("Y-m-d h:i:sa") . " - Found 0 tasks.");
}

// Checking Local Tasks
fwrite($logfile, "\n" . date("Y-m-d h:i:sa") . " - Checking for local tasks.");
$rstasks = mysqli_query($dbconn, "SELECT * FROM tasks") or die("Error in Selecting " . mysqli_error($dbconn));
$taskcount = $rstasks->num_rows;
fwrite($logfile, "\n" . date("Y-m-d h:i:sa") . " - Found $taskcount tasks.");
if ($taskcount > 0) {
  while ($rowtasks = mysqli_fetch_assoc($rstasks)) {
    if ($rowtasks["taskaction"] == "SYNCTUNNELS") {
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
      $tunnelarr = json_decode($tunneljson);
      if ($tunnelarr->tunnels > 0) {
        echo ("Deleting existing tunnels\n");
        fwrite($logfile, "\n" . date("Y-m-d h:i:sa") . " - Deleting existing tunnels.");
        $tunnelcount = count($tunnelarr->tunnellist);
        fwrite($logfile, "\n" . date("Y-m-d h:i:sa") . " - Syncronizing $tunnelcount tunnels.");
        echo ("Syncronizing $tunnelcount tunnels\n");
        mysqli_query($dbconn, "DELETE FROM tunnels");
        for ($y = 0; $y <= $tunnelcount - 1; $y++) {
          fwrite($logfile, "\n" . date("Y-m-d h:i:sa") . " - Adding " . $tunnelarr->tunnellist[$y]->application . " tunnel. " . $tunnelarr->tunnellist[$y]->remotehost . ":" . $tunnelarr->tunnellist[$y]->remoteport . " to " . $tunnelarr->tunnellist[$y]->tunnelport . ".");
          echo ("Adding " . $tunnelarr->tunnellist[$y]->application . " tunnel. " . $tunnelarr->tunnellist[$y]->remotehost . ":" . $tunnelarr->tunnellist[$y]->remoteport . " to " . $tunnelarr->tunnellist[$y]->tunnelport . "\n");
          mysqli_query($dbconn, "INSERT INTO tunnels (`tunnelname`, `tunnelport`, `localproto`, `localhost`, `localport`) VALUES ('" . $tunnelarr->tunnellist[$y]->application . "', '" . $tunnelarr->tunnellist[$y]->tunnelport . "', '" . $tunnelarr->tunnellist[$y]->remoteproto . "', '" . $tunnelarr->tunnellist[$y]->remotehost . "', '" . $tunnelarr->tunnellist[$y]->remoteport . "')");
        }
        fwrite($logfile, "\n" . date("Y-m-d h:i:sa") . " - Restarting tunnel service.");
        exec('service callmigrate-tunnel stop');
        sleep(5);
        exec('service callmigrate-tunnel start');
      } else {
        fwrite($logfile, "\n" . date("Y-m-d h:i:sa") . " - No tunnels to sync.");
        echo ("No tunnels to sync!\n");
      }
      mysqli_query($dbconn, "DELETE FROM `tasks` WHERE `pkid` = " . $rowtasks["pkid"]);
    } elseif ($rowtasks["taskaction"] == "RESTARTSERVICES") {
      fwrite($logfile, "\n" . date("Y-m-d h:i:sa") . " - Restarting services.");
      mysqli_query($dbconn, "DELETE FROM `tasks` WHERE `pkid` = " . $rowtasks["pkid"]);
      echo ("Restarting services\n");
      exec('service callmigrate-tunnel stop');
      sleep(5);
      exec('service callmigrate-tunnel start');
      exec('service callmigrate-cronwatch restart');
    }
  }
}

// Close Log File
fclose($logfile);
