/* 
 * This file should be included in most other PHP files so that we
 * only have to change the password in one place instead of in every file.
 * It also has some other useful features.
 */

<?php
//buffer output to prevent stray characters from being displayed
ob_start();
//ensure we keep an active session
session_start();

//set timezone
date_default_timezone_set('America/New_York');

//database credentials
define('DBHOST','localhost');
define('DBUSER','manox10h_admin');
define('DBPASS','ENTERPWD');  // Enter password here
define('DBNAME','manox10h_LiftBuddy');

//application address
//TODO: Update email address
define('DIR','http://mano.x10host.com/admin/LiftBuddy');
define('SITEEMAIL','noreply@domain.com');

$servername = "localhost";
$username = "manox10h";
$password = "ENTERPWD";  // Enter password here
$dbname = "manox10h_LiftBuddy";

// Create connection the usual way
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
} 

//include the user class, pass in the database connection
include('classes/user.php');
include('classes/phpmailer/mail.php');
$user = new User($db); 
?>
