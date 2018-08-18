<?php require('includes/config.php');

//if logged in redirect to main page
if( $user->is_logged_in() ){ header('Location: selectEvent.php'); exit(); }

//if form has been submitted process it
if(isset($_POST['submit'])){
    //Make sure all POSTS are declared
    if (!isset($_POST['email'])) {
        $error[] = "Please fill out all fields";
    }

    //email validation
    if(!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)){
        $error[] = 'Please enter a valid email address';
    } else {
        $email = $_POST['email'];
        $qStr = "SELECT email FROM Member "
                . "WHERE email = '".$email."'";
        $result = $conn->query($qStr);
        if ($result->num_rows == 0) {
            $error[] = 'Email provided is not recognized.';
        }
    }
    //if no errors have been created carry on
    if(!isset($error)){
        //create the activation code
        $qStr = "SELECT password, email FROM Member WHERE email = '".$email."'";
        $result = $conn->query($qStr);
        $row = $result->fetch_assoc();

        $token = hash_hmac('SHA256', $user->generate_entropy(8), $row['password']);//Hash and Key the random data
        $storedToken = hash('SHA256', ($token));//Hash the key stored in the database, the normal value is sent to the user
        
        $uStr = "UPDATE Member SET resetToken = '".$token."', resetComplete='No' "
                . "WHERE email = '".$email."'";
        $result = $conn->query($uStr);
        if (!$result) {
            $error[] = "Error updating DB with token: '".$uStr."'";
        }
        
        date_default_timezone_set('America/New York'); 
        $from = SITEEMAIL;
        $to = $email;  
        $mailbody="<p>Someone requested that your Mano password be reset.</p>
            <p>If this was a mistake, just ignore this email and nothing will happen.</p>
            <p>To reset your password, visit the following address: <a href='".DIR."/resetpassword.php?key=$token'>".DIR."/resetpassword.php?key=$token</a></p>"; 
        $subject="Mano Password Reset" ; 

        $headers = "Content-type: text/html; charset=ISO-8859-1\r\n"; 
        $headers .= "From: $from\r\n"; 
        $headers .= "Reply-To: $from\r\n"; 
        $headers .= "MIME-Version: 1.0\r\n"; 
        $headers .= "X-Mailer: PHP/" . phpversion(); 

        $resp = mail($to, $subject, $mailbody, $headers); 

        if( $resp ){ 
            $outcome = "Mail sent"; 
        } else { 
            $outcome = "Mail not sent"; 
        } 

            //redirect to index page
            header('Location: login.php?action=reset');
            exit;
    }
}

//define page title
$title = 'Reset Account';
//include header template
require('layout/header.php');
?>

<div class="container">
    <div class="row">
        <div class="col-xs-12 col-sm-8 col-md-6 col-sm-offset-2 col-md-offset-3">
            <form role="form" method="post" action="" autocomplete="off">
                <h2>Reset Mano Password</h2>
                <p><a href='login.php'>Back to login page</a></p>
                <hr>

                <?php
                //check for any errors
                if(isset($error)){
                    foreach($error as $error){
                        echo '<p class="bg-danger">'.$error.'</p>';
                    }
                }
                if(isset($_GET['action'])){
                    //check the action
                    switch ($_GET['action']) {
                        case 'active':
                            echo "<h2 class='bg-success'>Your account is now active you may now log in.</h2>";
                            break;
                        case 'reset':
                            echo "<h2 class='bg-success'>Please check your inbox for a reset link.</h2>";
                            break;
                    }
                }
                ?>

                <div class="form-group">
                    <input type="email" name="email" id="email" class="form-control input-lg" placeholder="Email" value="" tabindex="1">
                </div>

                <hr>
                <div class="row">
                    <div class="col-xs-6 col-md-6"><input type="submit" name="submit" value="Send Reset Link" class="btn btn-primary btn-block btn-lg" tabindex="2"></div>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
//include header template
require('layout/footer.php');
?>