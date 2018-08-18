<!DOCTYPE html>
<!--
To change this license header, choose License Headers in Project Properties.
To change this template file, choose Tools | Templates
and open the template in the editor.
-->
<?php
session_start();
    
$servername = "localhost";
$username = "manox10h_admin";
$password = "ENTERPWD";  // Need to put proper password in here tco
$dbname = "manox10h_db";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);
// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
} 

//TODO: Update to only delete records for games that can be changed
function deleteAllPicks($conn) {
//    echo nl2br("Deleting picks, season is ".$_SESSION["sessionSeason"].", event is ".$_SESSION["sessionEventID"]."\n");
    $dStr = "DELETE FROM Pick "
            . "WHERE PlayerID = ".$_SESSION["userID"]
            ." AND EventGameID IN (SELECT EventGameID FROM EventGame EG, Event E WHERE EG.Season = E.Season AND EG.EventName = E.EventName "
            . "AND E.Season = ".$_SESSION["sessionSeason"]." AND E.EventID = ".$_SESSION["sessionEventID"].")"; 
//    echo nl2br("Executing: ".$dStr."\n");
    $resultDelete = $conn->query($dStr);
//    echo nl2br("result:".$resultDelete."\n");
}

//TODO: Update to allow insert to fail for duplicate key (because previous record not deleted - too late)
function insertPick($conn, $gameNo, $pick) {
    $qStr = "SELECT EG.EventGameID, EG.FavoriteClubAbbr, EG.DogClubAbbr FROM EventGame EG, Event E WHERE EG.Season = E.Season AND EG.EventName = E.EventName AND E.Season = ".$_SESSION["sessionSeason"]." AND E.EventID = ".$_SESSION["sessionEventID"]." AND Sequence = ".$gameNo;
    $resultSel = $conn->query($qStr);
    if ($resultSel->num_rows == 1) {
        while($row = $resultSel->fetch_assoc()) {
            $curGameID = $row["EventGameID"];
            if ($pick == 1) {
                $curPickAbbr = $row["FavoriteClubAbbr"];
            } else {
                $curPickAbbr = $row["DogClubAbbr"];
            }
        }
    } else { echo nl2br("ERROR: found zero or multiple rows for the current game\n"."string: ".$qStr."\n"); }      
    $iStr = "INSERT INTO Pick (PlayerID, EventGameID, SelectedClubAbbr) VALUES (".$_SESSION["userID"].", ".$curGameID.", '".$curPickAbbr."')";
    $resultIns = $conn->query($iStr);
    if ($resultIns == 1) {
        echo nl2br("You chose ".$curPickAbbr." for game # ".$curGameID."\n");
    } else {
        echo nl2br("ERROR: Database insert seems to have failed for game #".$curGameID."\n");
    }
}

?>

<html>
    <head>
        <meta charset="UTF-8">
        <title>Confirmation of Submission</title>
    </head>
    <body>
        <?php
        $tech2 = $_POST['tech'];
        deleteAllPicks($conn);
        foreach( $tech2 as $key => $n ) {
            insertPick($conn, $key, $n);
        }
        $conn->close();
        ?>
        <a href="http://mano.x10host.com/admin/">Go back to main page</a>
    </body>
</html>
