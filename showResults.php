<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
session_start();

require_once('includes/config.php');

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

// Identify the current date and time, so we can display only games already underway or completed
$curDate = date("Y-m-d");
$curTime = date("H");

// Query to get the distinct list of games for this event (week)
// Updated to only include the games that are underway or completed
$gStr = "SELECT DISTINCT eventGameID "
        . "FROM EventGame EG "
        . "JOIN Event E ON (EG.season = E.season AND EG.eventName = E.eventName) "
        . "WHERE E.season = ".$_SESSION["sessionSeason"]." AND E.eventID = ".$_SESSION["sessionEventID"]
        . " AND ((EG.gameDate < '".$curDate."') "
        . "OR (EG.gameDate = '".$curDate."' AND EG.gameTimeHour <= ".$curTime."))";
$gResult = $conn->query($gStr);

// Query to get the distinct list of players for this event (week)
$pStr = "SELECT DISTINCT P.playerID, PL.playerDesc "
        . "FROM Pick P JOIN Player PL ON (P.playerID = PL.playerID) "
        . "JOIN EventGame EG ON (P.eventGameID = EG.eventGameID) "
        . "JOIN Event E ON (EG.season = E.season AND EG.eventName = E.eventName) "
        . "WHERE E.season = ".$_SESSION["sessionSeason"]." AND E.eventID = ".$_SESSION["sessionEventID"];
$pResult = $conn->query($pStr);

function getResult($conn, $curPlayerID, $curEventGameID) {
    $qStr = "SELECT P.selectedClubAbbr, EG.winningClubAbbr, COUNT(*)*E.scorePerGame PointsRisked "
            . "FROM Pick P "
            . "JOIN Pick P2 ON (P.eventGameID = P2.eventGameID AND P.selectedClubAbbr != P2.selectedClubAbbr) "
            . "JOIN EventGame EG ON (P.eventGameID = EG.eventGameID) "
            . "JOIN Event E ON (EG.season = E.season AND EG.eventName = E.eventName)"
            . "WHERE P.playerID = ".$curPlayerID." AND P.eventGameID = ".$curEventGameID." "
            . "GROUP BY P.selectedClubAbbr, EG.winningClubAbbr";
    $qResult = $conn->query($qStr);
    if ($qResult->num_rows == 1) {
        $curRow = $qResult->fetch_assoc();
        if ($curRow['winningClubAbbr'] =="") {
            $sign = "+/-";
        } else if ($curRow['winningClubAbbr'] == $curRow['selectedClubAbbr']) {
            $sign = "+";
        } else {
            $sign = "-";
        }
        $outVal = $curRow['selectedClubAbbr']." (".$sign.$curRow['PointsRisked'].")";
    } else if ($qResult-> num_Rows == 0) {
        $outVal = '';
    } else {
        echo "Error: multiple picks were found";
        $outVal = '';
    }
    return $outVal;
}
    
// Create a table and use the list of all players to create the table header
// Find the list of all games for the current event
// For each one, create a new row in the table
// For each player, look for their pick for the current game and write to the table
    
    //define page title
    $title = 'Mano - View Summary';
    //include header template
    require('layout/header.php');   
?>

<html>
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
    </head>
    <body>
        This shows all picks for the current Week, with current status:<br>
        <form name="currentPicks"  method="post" action="selectEvent.php">
<!--            <table style="width:100%">  -->
            <table>
                <caption>Here are the current picks and status...</caption>
                <thead>
                    <tr>
                        <?php if ($pResult->num_rows > 0) {
                            $playerList = array();
                            while($row = $pResult->fetch_assoc()) {  
                                array_push($playerList, $row['playerID']); ?>
                        <th>
                            <?php echo $row['playerDesc'];?>
                        </th>
                        <?php
                            }
                        } else {
                            echo nl2br("ERROR: found no players for season = ".$_SESSION["sessionSeason"]." AND eventID = ".$_SESSION["sessionEventID"]."\n");
                            echo nl2br("Query was: ".$pStr."\n");
                        }
                        ?>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // If data was returned, display each row, along with radio buttons to pick each game
                    if ($gResult->num_rows > 0) {
                        // output data of each row
                        $numPlayers = count($playerList);
                        while($row = $gResult->fetch_assoc()) {
                    ?>
                            <tr>
                                <?php
                                for ($i = 0; $i < $numPlayers; $i++) { ?>
                                <td>
                                    <?php 
                                    echo getResult($conn, $playerList[$i], $row['eventGameID']); ?>
                                </td>
                                <?php } ?>
                            </tr>
                    <?php
                        }
                    } else {
                        echo nl2br("ERROR: No results from query: ".$gStr."\n");
                    }
                    $conn->close();
                    ?>
                </tbody>
            </table>
            <input type="submit" name="submit" value="Return">
        </form>
    </body>
</html>