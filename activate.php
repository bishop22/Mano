<?php
require('includes/config.php');

//collect values from the url
$memberID = trim($_GET['x']);
$active = trim($_GET['y']);

//if id is number and the active token is not empty carry on
if(is_numeric($memberID) && !empty($active)){

    //update users record set the active column to Yes where the memberID and active value match the ones provided in the array
    $uStr = "UPDATE Member "
            . "SET active = 'Yes' "
            . "WHERE memberID = ".$memberID." AND active = '".$active."'";
    $result = $conn->query($uStr);
    if ($result) {
        header('Location: login.php?action=active');
        exit;
    } else {
        echo "Sorry, your account could not be activated.  Get in touch with BONZ";
        echo $uStr;
    }
}
?>