<?php   session_start();  ?>

<!DOCTYPE html>
<!--
To change this license header, choose License Headers in Project Properties.
To change this template file, choose Tools | Templates
and open the template in the editor.
-->

<html>
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Choose Sport,Season and Week/Event</title>
    </head>
    <body>
        <form>
            <a href="logout.php">Click here to logout</a>
            <br>
            <?php
            // put your code here
            $defSportID = 2;
            $defSeasonID = 2018;
            $defWeekID = 2;

            // Check for variables in the URL to override defaults
            
            echo nl2br("You are logged in as: ".$_SESSION['user']."\n\n");

            if (!empty(filter_input(INPUT_GET, 'sportID', FILTER_SANITIZE_URL))) {
                $defSportID = filter_input(INPUT_GET, 'sportID', FILTER_SANITIZE_URL);
            } else {
                $defSportID = 2;
            }

            if (!empty(filter_input(INPUT_GET, 'curSeason', FILTER_SANITIZE_URL))) {
                $defSeasonID = filter_input(INPUT_GET, 'curSeason', FILTER_SANITIZE_URL);
            } else {
                $defSeasonID = 2018;
            }

            if (!empty(filter_input(INPUT_GET, 'curEvent', FILTER_SANITIZE_URL))) {
                $defWeekID = filter_input(INPUT_GET, 'curEvent', FILTER_SANITIZE_URL);
            } else {
                $defWeekID = 2;
            }
                
            // Conect to the database and prepare to run any necessary functions
            $servername = "localhost";
            $username = "manox10h_admin";
            $password = "ENTERPWD";  // Need to enter password here tco
            $dbname = "manox10h_db";

            // Create connection
            $conn = new mysqli($servername, $username, $password, $dbname);
            // Check connection
            if ($conn->connect_error) {
                die("Connection failed: " . $conn->connect_error);
            } 
            $qStr = "SELECT sportID, sportName FROM Sport ORDER BY sportID";
            $result = $conn->query($qStr);

            if ($result->num_rows > 0) {
            ?>
            Select desired sport:
                <select name="sportID" id="sportID">
                <?php
                while($row = $result->fetch_assoc()) {
                ?>

                    <option <?=($row['sportID']==$defSportID)?'selected="selected"':''?> value="<?php echo $row['sportID'] ?>"><?php echo $row['sportName'] ?></option>


                <?php
                }
                ?>
                </select><br>
            <?php
            } else {
                echo "Error: no sports were found";
            }

            // Now build the combo box for the "Season" (based on selected sport)
            $qStr = "SELECT DISTINCT season FROM Event WHERE sportID = ".$defSportID;
            $resultSeason = $conn->query($qStr);

            if ($resultSeason->num_rows > 0) {
            ?>
                Select desired season:
                <select name="curSeason" id="curSeason">
                <?php
                while($row = $resultSeason->fetch_assoc()) {
                ?>
                    <option <?=($row['season']==$defSeasonID)?'selected="selected"':''?> value="<?php echo $row['season'] ?>"><?php echo $row['season'] ?></option>
                <?php
                }
                ?>
                </select><br><br>
            <?php
            } else {
                echo nl2br("\nError: no seasons were found");
            }

            // Add the link that will show total damage to date for this sport and season
            ?>
            <a href="showDamage.php?seasonID=<?php echo $defSeasonID?>&sportID=<?php echo $defSportID?>">Show damage for this sport and season</a>
            <br><br>
                                
            <?php
            // Now build the list of URLs for the possible Events/Weeks (based on selected season)

            $qStr = "SELECT E.eventID, E.eventName, MIN(EG.gameDate) minDate, MAX(EG.gameDate) maxDate "
                    . "FROM Event E "
                    . "JOIN EventGame EG ON (EG.season = E.season AND EG.eventName = E.eventName) "
                    . "WHERE E.sportID = ".$defSportID." AND E.season = ".$defSeasonID." "
                    . "GROUP BY E.eventID, E.eventName";
            $resultEvent = $conn->query($qStr);
                    
            $curDate = date("Y-m-d");
            $curTime = date("H:i");
            if ($resultEvent->num_rows > 0) {
            ?>
                Click on desired Event/Week from this list:<br>
                <?php
                while($row = $resultEvent->fetch_assoc()) {
                    if (($curDate < $row['maxDate']) || (($curDate == $row['maxDate']) && ($curTime < "12:00"))) {
                        ?>
                        <a href="http://mano.x10host.com/admin/showPix.php?eventID=<?php echo $row['eventID']?>&season=<?php echo $defSeasonID?>"><?php echo nl2br($row['eventName']."\n"); ?></a>
                    
                    <?php    
                    } 
                    if (($curDate > $row['minDate']) || (($curDate == $row['minDate']) && ($curTime >= "12:00"))) {
                        ?>
                        <a href="http://mano.x10host.com/admin/showAllPix.php?eventID=<?php echo $row['eventID']?>&season=<?php echo $defSeasonID?>"><?php echo nl2br("Show picks - ".$row['eventName']."\n"); ?></a>
                        <a href="http://mano.x10host.com/admin/showResults.php?eventID=<?php echo $row['eventID']?>&season=<?php echo $defSeasonID?>"><?php echo nl2br("Show pick status - ".$row['eventName']."\n"); ?></a>
                <?php
                    }
                }
                ?>                    
            <?php
            } else {
                echo nl2br("\nError: no events were found for selected sport and season");
            }
            
            $conn->close();
            ?>
            <br>
            <input type="submit" name="submit" value="Update Events">
        </form>
    </body>
</html>