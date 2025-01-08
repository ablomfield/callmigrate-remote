<?php
// Retrieve YAML Settings
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
$cmserver = $rowsettings["cmserver"];
$clientid = $rowsettings["clientid"];
$clientsecret = $rowsettings["clientsecret"];
$sitetitle = $rowsettings["sitetitle"];
$regstatus = $rowsettings["regstatus"];
$claimstatus = $rowsettings["claimstatus"];
$custname = $rowsettings["custname"];

// Set Debug
if (isset($_SESSION["enabledebug"])) {
    if ($_SESSION['enabledebug'] <> 1) {
        $_SESSION["enabledebug"] = 0;
    }
} else {
    $_SESSION["enabledebug"] = 0;
}
?>