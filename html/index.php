<?php
session_start();

// Retrieve Settings
include($_SERVER['DOCUMENT_ROOT'] . "/includes/settings.php");

// Retrieve and Perform Actions
if (isset($_REQUEST["action"])) {
    $action = $_REQUEST["action"];
} else {
    $action = "";
}

if ($regstatus == 1) {
    $regstatus = "Registered";
} else {
    $regstatus = "Unregistered";
}

if ($claimstatus == 1) {
    $claimstatus = "Claimed";
} else {
    $claimstatus = "Unclaimed";
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <title><?php echo ($sitetitle); ?></title>
    <link rel="icon" type="image/icon" href="/images/callmigrate.ico">
    <link rel="stylesheet" href="/css/callmigrate.css">
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <script src='https://code.jquery.com/jquery-1.4.2.js'></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Titillium+Web:ital,wght@0,200;0,300;0,400;0,600;0,700;0,900;1,200;1,300;1,400;1,600;1,700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
    <link rel="stylesheet" href="https://www.w3schools.com/lib/w3-theme-dark-grey.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.3.0/css/font-awesome.min.css">
    <link href="https://cdn.datatables.net/2.1.7/css/dataTables.dataTables.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/2.1.8/js/dataTables.min.js"></script>
</head>

<body>
    <div class="parent">
        <div class="cm-logo">
            <?php include $_SERVER['DOCUMENT_ROOT'] . "/includes/logo.php"; ?>
        </div>
        <div class="cm-title">
            <?php echo ($sitetitle); ?>
        </div>
        <div class="cm-tasks">
        </div>
        <div class="cm-avatar">
        </div>
        <div class="cm-menu">
        </div>
        <div class="cm-customer">
        </div>
        <div class="cm-body" style="width: 800px">
            <table>
                <tr>
                    <td align="right"><b>Registration Status:</b></td>
                    <td><?php echo($regstatus); ?></td>
                </tr>
                <tr>
                    <td align="right"><b>Claim Status:</b></td>
                    <td><?php echo($claimstatus); ?></td>
                </tr>                
                <tr>
                    <td align="right"><b>IP Address:</b></td>
                    <td><?php echo(shell_exec("hostname -I")); ?></td>
                </tr>
                <tr>
                    <td align="right"><b>Client ID:</b></td>
                    <td><?php echo($clientid); ?></td>
                </tr>
                <tr>
                    <td align="right"><b>Tunnels:</b></td>
                    <td>
<?php
$rstunnels = mysqli_query($dbconn, "SELECT * FROM tunnels") or die("Error in Selecting " . mysqli_error($dbconn));
$tunnelcount = $rstunnels->num_rows;
if ($tunnelcount > 0) {
    while($tunnelrow = mysqli_fetch_assoc($rstunnels)) {
        echo("                        " . $tunnelrow["tunnelname"] . " (" . $tunnelrow["localhost"] . ":" . $tunnelrow["localport"] . " to " . $tunnelrow["tunnelport"] . ")<br>\n");
    }
} else {
    echo("                        None\n");
}
?>                    </td>
                </tr>
                <tr>
                    <td align="right"><b>Tunnel Service:</b></td>
                    <td><?php echo(ucwords(shell_exec("systemctl is-active callmigrate-tunnel"))); ?></td>
                </tr>                                
            </table>
        </div>
        <div class="cm-footer">
            <?php include $_SERVER['DOCUMENT_ROOT'] . "/includes/footer.php"; ?>
        </div>
    </div>
</body>

</html>