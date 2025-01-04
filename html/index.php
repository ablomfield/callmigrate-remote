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
$rssettings = mysqli_query($dbconn, "SELECT * FROM settings") or die("Error in Selecting " . mysqli_err
or($dbconn));
$rowsettings = mysqli_fetch_assoc($rssettings);
$regstatus = $rowsettings["regstatus"];
if ($regstatus == 1) {
    $regstatus = "Registered";
  } else {
    $regstatus = "Unregistered";
}
?>
<html>
<head>
<title>CallMigrate Remote</title>
<link rel="icon" type="image/svg" href="/images/callmigrate.svg">
<link rel="stylesheet" href="/css/callmigrate.css">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Titillium+Web:ital,wght@0,200;0,300;0,400;0,600;0,700;0,900;1,200;1,300;1,400;1,600;1,700&display=swap" rel="stylesheet">
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<script src="https://kit.fontawesome.com/5a918f4f0f.js" crossorigin="anonymous"></script>
</head>
<body>
<div class="parent">
    <div class="cm-logo">
        <?php include $_SERVER['DOCUMENT_ROOT']."/includes/logo.php"; ?>
    </div>
    <div class="cm-title">
        <?php include $_SERVER['DOCUMENT_ROOT']."/includes/title.php"; ?>
    </div>
    <div class="cm-body">
    <?php
    echo("    <p>Registration Status: " . $regstatus . "</p>\n");
    ?>
    </div>
    <div class="cm-footer">
        <?php include $_SERVER['DOCUMENT_ROOT']."/includes/footer.php"; ?>
    </div>
</div>
</body>
</html>
