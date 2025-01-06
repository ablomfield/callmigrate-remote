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
$cmremuser = $rowsettings["cmremuser"];

// Load Tunnels
$rstunnels = mysqli_query($dbconn, "SELECT * FROM tunnels") or die("Error in Selecting " . mysqli_error($dbconn));
$tunnelcount = $rstunnels->num_rows;
if ($tunnelcount > 0) {
    while($tunnelrow = mysqli_fetch_assoc($rstunnels)) {
        echo("Starting tunnel for " . $tunnelrow["tunnelname"] . "(" . $tunnelrow["localhost"] . ":" . $tunnelrow["localport"] . " to " . $tunnelrow["tunnelport"] . ")\n");
        echo("/usr/bin/autossh -M 0 -o \"ServerAliveInterval 30\" -o \"ServerAliveCountMax 3\" -NR " . $tunnelrow["tunnelport"] . ":" . $tunnelrow["localhost"] . ":" . $tunnelrow["localport"] . " " . $cmremuser . "@" . $cmserver . " > /dev/null &\n");        
        exec("/usr/bin/autossh -M 0 -o \"ServerAliveInterval 30\" -o \"ServerAliveCountMax 3\" -NR " . $tunnelrow["tunnelport"] . ":" . $tunnelrow["localhost"] . ":" . $tunnelrow["localport"] . " " . $cmremuser . "@" . $cmserver . " > /dev/null &");
    }
}
