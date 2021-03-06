<?php
session_start();

require_once('includes/config.php');

//define page title
$title = 'Mano - Damage';
//include header template
require_once('layout/header.php');  

// Get the parameters passed to the page
if (!empty(filter_input(INPUT_GET, 'seasonID', FILTER_SANITIZE_URL))) {
    $defSeasonID = filter_input(INPUT_GET, 'seasonID', FILTER_SANITIZE_URL);
    $_SESSION["sessionSeason"] = $defSeasonID;
} else {
    echo "ERROR: No season identified (using default = 2018)!";
    $defSeasonID = 2018;
    $_SESSION["sessionSeason"] = $defSeasonID;
}

if (!empty(filter_input(INPUT_GET, 'sportID', FILTER_SANITIZE_URL))) {
    $defSportID = filter_input(INPUT_GET, 'sportID', FILTER_SANITIZE_URL);
    $_SESSION["sessionSportID"] = $defSportID;
} else {
    echo "ERROR: No sport ID identified (using default = 2)!";
    $_SESSION["sessionSportID"] = 2;
}

// Query to get the total damage to date
$qStr = "SELECT P.playerDesc Player, SUM(IF(AP.winningClubAbbr='', 0, "
            . "IF(AP.selectedClubAbbr = AP.winningClubAbbr, AP.PointsRisked, -AP.PointsRisked))) Damage "
        . "FROM AllPointsRisked AP "
        . "JOIN Player P ON (AP.playerID = P.PlayerID) "
        . "WHERE AP.season = ".$_SESSION["sessionSeason"]." AND AP.sportID = ".$_SESSION["sessionSportID"]." "
        . "GROUP BY P.playerDesc "
        . "ORDER BY Damage DESC";

$qResult = $conn->query($qStr);

// Create a table to show the total damages for everyone
    
?>

<html>
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
    </head>
    <body>
        This shows total Damage for Sport #<?php echo $_SESSION["sessionSportID"]?> and Season <?php echo $_SESSION["sessionSeason"]?>:<br>
        <form name="currentPicks"  method="post" action="selectEvent.php">
<!--            <table style="width:100%">  -->
            <table>
                <caption></caption>
                <thead>
                    <tr>
                        <th>Player</th>
                        <th>Damage</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // If data was returned, display each row
                    if ($qResult->num_rows > 0) {
                        // output data of each row
                        while($row = $qResult->fetch_assoc()) {
                    ?>
                            <tr>
                                <td><?php echo $row['Player']; ?></td>
                                <td><?php echo $row['Damage']; ?></td>
                            </tr>
                    <?php
                        }
                    } else {
                        echo nl2br("ERROR: No results from Damage query: ".$qStr."\n");
                    }
                    $conn->close();
                    ?>
                </tbody>
            </table>
            <input type="submit" name="submit" value="Return">
        </form>
    </body>
</html>