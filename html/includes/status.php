<?php
// Retrieve Settings
include($_SERVER['DOCUMENT_ROOT'] . "/includes/settings.php");
?>
<table>
    <tr>
        <td align="right"><b>Registration Status:</b></td>
        <td><?php echo ($regstatus); ?></td>
    </tr>
    <tr>
        <td align="right"><b>Claim Status:</b></td>
        <td><?php echo ($claimstatus); ?></td>
    </tr>
    <tr>
        <td align="right"><b>IP Address:</b></td>
        <td><?php echo (shell_exec("hostname -I")); ?></td>
    </tr>
    <tr>
        <td align="right"><b>Client ID:</b></td>
        <td><?php echo ($clientid); ?></td>
    </tr>
    <tr>
        <td align="right"><b>Tunnels:</b></td>
        <td>
            <?php
            $rstunnels = mysqli_query($dbconn, "SELECT * FROM tunnels") or die("Error in Selecting " . mysqli_error($dbconn));
            $tunnelcount = $rstunnels->num_rows;
            if ($tunnelcount > 0) {
                while ($tunnelrow = mysqli_fetch_assoc($rstunnels)) {
                    echo ("                        " . $tunnelrow["tunnelname"] . " (" . $tunnelrow["localhost"] . ":" . $tunnelrow["localport"] . " to " . $tunnelrow["tunnelport"] . ")<br>\n");
                }
            } else {
                echo ("                        None\n");
            }
            ?> </td>
    </tr>
    <tr>
        <td align="right"><b>Tunnel Service:</b></td>
        <td><?php echo (ucwords(shell_exec("systemctl is-active callmigrate-tunnel"))); ?></td>
    </tr>
</table>