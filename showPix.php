<!DOCTYPE html>
<!--
To change this license header, choose License Headers in Project Properties.
To change this template file, choose Tools | Templates
and open the template in the editor.
-->

<?php
    session_start();
    // Get the parameters passed to the page
    if (!empty(filter_input(INPUT_GET, 'season', FILTER_SANITIZE_URL))) {
        $defSeasonID = filter_input(INPUT_GET, 'season', FILTER_SANITIZE_URL);
        $_SESSION["sessionSeason"] = $defSeasonID;
    } else {
        echo "ERROR: No season selected!";
    }

    if (!empty(filter_input(INPUT_GET, 'eventID', FILTER_SANITIZE_URL))) {
        $defEventID = filter_input(INPUT_GET, 'eventID', FILTER_SANITIZE_URL);
        $_SESSION["sessionEventID"] = $defEventID;
    } else {
        echo "ERROR: No event selected!";
    }

//   echo nl2br("The session season is set to ".$_SESSION["sessionSeason"]."\n");
    
    $servername = "localhost";
    $username = "manox10h_admin";
    $password = "ENTERPWD";  //// Need to put proper password in here tco
    $dbname = "manox10h_db";

    // Create connection
    $conn = new mysqli($servername, $username, $password, $dbname);
    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    } 

    // Build query string, and execute the query to get the event name
    $sql = "SELECT E.eventName "
            . "FROM Event E "
            . "WHERE E.eventID = ".$defEventID." AND E.season = ".$defSeasonID;
    $resultEvent = $conn->query($sql);

    // Build query string, and execute the query
    $sql = "SELECT EG.favoriteClubAbbr, EG.dogClubAbbr, EG.spread, EG.sequence, P.selectedClubAbbr "
            . "FROM EventGame EG JOIN Event E LEFT JOIN Pick P ON (P.eventGameID = EG.eventGameID AND P.PlayerID = ".$_SESSION["userID"].") "
            . "WHERE EG.eventName = E.eventName AND EG.season = E.season "
            . "AND E.eventID = ".$defEventID." AND E.season = ".$defSeasonID 
            . " ORDER BY EG.sequence";
    $result = $conn->query($sql);
//    echo nl2br("Query string was: ".$sql."\n")
?>

<html>
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Mano - Make Picks</title>
        <style>
            table, th, td {
                border: 1px solid black;
            }
            td {
                text-align: center;
            }      
        </style>
    </head>
    <body>
        This shows the games for the current Week:<br>
        <form name="currentGames"  method="post" action="updatePix.php">
<!--            <table style="width:100%">  -->
            <table>
                <caption>Choose your picks below...</caption>
                <thead>
                    <tr>
                        <th>Select</th>
                        <th>Favorite</th>
                        <th>Dog</th>
                        <th>Spread</th>
                        <th>Select</th>
                    </tr>
                </thead>
                <tbody>

                    <?php
                    // If data was returned, display each row, along with radio buttons to pick each game
                    if ($result->num_rows > 0) {
                        // output data of each row
                        $i = 0;
                        while($row = $result->fetch_assoc()) {
                    ?>
                            <tr>
                                <td>
                                    <?php 
                                    if ($row["selectedClubAbbr"] == $row["favoriteClubAbbr"]) {
                                        $dbValue = 1;
                                    } else if ($row["selectedClubAbbr"] == $row["dogClubAbbr"]) {
                                        $dbValue = 2;
                                    } else {
                                        $dbValue = 0;
                                    }
                                    if ($row["dogClubAbbr"] == "") {
                                        echo "";
                                    } else {
                                        echo "<input type='radio' name='tech[".$row["sequence"]."]' value='1' "
                                                .($dbValue==1?' checked=checked':'').">";
                                    } ?>
                                </td>
                                <td>
                                    <?php echo $row["favoriteClubAbbr"]; ?>
                                </td>
                                <td>
                                    <?php echo $row["dogClubAbbr"]; ?>
                                </td>
                                <td>
                                    <?php echo $row["spread"]; ?>
                                </td>
                                <td>
                                    <?php 
                                    if ($row["dogClubAbbr"] == "") {
                                        echo "";
                                    } else {
                                        echo "<input type='radio' name='tech[".$row["sequence"]."]' value='2' "
                                                .($dbValue==2?' checked=checked':'').">";
                                    } ?>
                                </td>
                            </tr>
                    <?php
                        }
                    } else {
                        echo "0 results";
                    }
                    $conn->close();
                    ?>
                </tbody>
            </table>
            <input type="submit" name="submit" value="Save Picks">
        </form>
    </body>
</html>
