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

$statusmsg = "";

if ($action == "runcron") {
    $f = fopen('/opt/callmigrate/cronwatch/cron.now', 'w');
    fwrite($f, time());
    fclose($f);
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
    <script>
        var timeout = setInterval(reloadstatus, 5000);

        function reloadstatus() {
            $('#statusholder').load('/includes/status.php');
        }
    </script>
</head>

<body onload="reloadstatus()">
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
            <div id="statusholder">
            </div>
            <table>
                <tr>
                    <form method="post">
                    <input type="hidden" name="action" value="runcron">
                    <td>
                        <input type="submit" value="cron" class="button">
                    </td>
                    </form>
                    <form method="post">
                    <input type="hidden" name="action" value="synctunnels">
                    <td>
                        <input type="submit" value="Sync" class="button">
                    </td>
                    </form>
                    <form method="post">
                    <input type="hidden" name="action" value="restartservice">
                    <td>
                        <input type="submit" value="Restart" class="button">
                    </td>
                    </form>
                </tr>
            </table>
            <form method="post">
                <input type="hidden" name="action" value="restarttunnels">
                <input type="submit" value="Restart Service" class="button">
            </form>
            <br>
            <?php echo ($statusmsg); ?>
        </div>
        <div class="cm-footer">
            <?php include $_SERVER['DOCUMENT_ROOT'] . "/includes/footer.php"; ?>
        </div>
    </div>
</body>

</html>