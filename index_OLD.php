<?php

session_start();
$errorMsg = "";
$validUser = $_SESSION["login"] === true;

$curPlayerID = $_POST["playerID"];
$inPlayerCode = $_POST["password"];

// Conect to the database and prepare to run any necessary functions
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

// Lookup the password for the current player
$qStrP = "SELECT playerCode, playerDesc FROM Player WHERE playerID = ".$curPlayerID;
$curPwd = $conn->query($qStrP);
if ($curPwd->num_rows > 0) {
    $rowPwd = $curPwd->fetch_assoc();
    $curPlayerCode = $rowPwd['playerCode'];
    $curPlayerDesc = $rowPwd['playerDesc'];
} else {
    // echo nl2br("ERROR: Player's password not found\n");
}
if (isset($_POST["sub"])) {
    // $validUser = $_POST["username"] == "admin" && $_POST["password"] == "password";
    $validUser = $inPlayerCode == $curPlayerCode;
    if (!$validUser) {
        $errorMsg = "Invalid username or password.";
    } else {
        $_SESSION["login"] = true;
        $_SESSION["user"] = $curPlayerDesc;
        $_SESSION["userID"] = $curPlayerID;
  }
}
if($validUser) {
   header("Location: selectEvent.php"); die();
}

$qStr = "SELECT playerID, playerDesc, playerCode FROM Player WHERE activeInd = 1";
$result = $conn->query($qStr);
$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
  <meta http-equiv="content-type" content="text/html;charset=utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Login</title>
</head>
<body>
  <form name="input" action="" method="post">
    <label for="username">Username:</label>
    <?php
    if ($result->num_rows > 0) {
    ?>
        <select name="playerID" id="playerID">
        <?php
        while($row = $result->fetch_assoc()) {
        ?> 
            <option <?=($row['playerID']==$defPlayerID)?'selected="selected"':''?> value="<?php echo $row['playerID'] ?>"><?php echo $row['playerDesc'] ?></option>
        <?php
        }
        ?>
        </select><br>
    <?php
    } else {
        echo "Error: no sports were found";
    }
    ?>
    <label for="password">Password:</label><input type="password" value="" id="password" name="password" />
    <div class="error"><?= $errorMsg ?></div>
    <input type="submit" value="Login" name="sub" />
  </form>
</body>
</html>