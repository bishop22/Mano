<?php
//include config
require_once('includes/config.php');

if(isset($_POST['submit'])) {
//if(isset(filter_input(INPUT_POST, 'submit'))) {
    if (!isset($_POST['username'])) { $error[] = "Please fill out all fields"; }
    if (!isset($_POST['email'])) { $error[] = "Please fill out all fields"; }
    if (!isset($_POST['password'])) { $error[] = "Please fill out all fields"; }

    $username = $_POST['username'];
//    $username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING);

    //very basic validation
    if(!$user->isValidUsername($username)){
        $error[] = 'Usernames must be at least 3 Alphanumeric characters';
    } else {
        $qStr = "SELECT username FROM Member WHERE username = '".$username."'";
        
        $result = $conn->query($qStr);
        if ($result->num_rows > 0) {
            $error[] = 'Username provided is already in use.';
        }
    }

    if(strlen($_POST['password']) < 3) {
            $error[] = 'Password is too short.';
    }
    if(strlen($_POST['passwordConfirm']) < 3) {
            $error[] = 'Confirm password is too short.';
    }
    if($_POST['password'] != $_POST['passwordConfirm']) {
            $error[] = 'Passwords do not match.';
    }
    //email validation
    $email = htmlspecialchars_decode($_POST['email'], ENT_QUOTES);
    if(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error[] = 'Please enter a valid email address';
    } else {
        $qStr = "SELECT email FROM Member WHERE email = '".$email."'";
        $result = $conn->query($qStr);
        if ($result->num_rows > 0) {
            $error[] = 'Email provided is already in use.';
        }
    }

    //if no errors have been created carry on
    if(!isset($error)){
        //hash the password
        $hashedpassword = $user->password_hash($_POST['password'], PASSWORD_BCRYPT);
        //create the activation code
        $activasion = md5(uniqid(rand(),true));
        $iStr = "INSERT INTO Member (username, password, email, active) "
                . "VALUES ('".$username."', '".$hashedpassword."', '".$email."', '".$activasion."')";
        $result = $conn->query($iStr);

//        try {
//            //insert into database with a prepared statement
//            $stmt = $db->prepare('INSERT INTO Member (username,password,email,active) VALUES (:username, :password, :email, :active)');
//            $stmt->execute(array(
//                    ':username' => $username,
//                    ':password' => $hashedpassword,
//                    ':email' => $email,
//                    ':active' => $activasion
//            ));
            $id = $conn->insert_id;
            //send email

//  Send an activation message using PHP mail() 
            date_default_timezone_set('America/New York'); 
//            echo date('l jS \of F Y h:i:s A');  

            $from = SITEEMAIL;
            $to = $_POST['email'];  
            $mailbody="<p>Thank you for registering for a Mano account!</p>
                <p>To activate your account, please click on this link: <a href='".DIR."/activate.php?x=$id&y=$activasion'>".DIR."/activate.php?x=$id&y=$activasion</a></p>
                <p>Regards, The Mano team</p>" . date('l jS \of F Y h:i:s A'); 
            $subject="Mano a Mano Registration Confirmation" ; 

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

        header('Location: index.php?action=joined');
        exit;
    }
}

//define page title
$title = 'Mano a Mano Registration';
//include header template
require('layout/header.php');
?>


<div class="container">

    <div class="row">

        <div class="col-xs-12 col-sm-8 col-md-6 col-sm-offset-2 col-md-offset-3">
            <form role="form" method="post" action="" autocomplete="off">
                <h2>Please Sign Up for Mano a Mano 2018 and Beyond</h2>
                <p>Already a member? <a href='login.php'>Login</a></p>
                <hr>

                <?php
                //check for any errors
                if(isset($error)){
                    foreach($error as $error){
                        echo '<p class="bg-danger">'.$error.'</p>';
                    }
                }
                //if action is joined show sucess
                if(isset($_GET['action']) && $_GET['action'] == 'joined'){
                    echo "<h2 class='bg-success'>Registration successful, please check your email to activate your account.</h2>";
                }
                ?>

                <div class="form-group">
                    <input type="text" name="username" id="username" class="form-control input-lg" placeholder="User Name" value="<?php if(isset($error)){ echo htmlspecialchars($_POST['username'], ENT_QUOTES); } ?>" tabindex="1">
                </div>
                <div class="form-group">
                    <input type="email" name="email" id="email" class="form-control input-lg" placeholder="Email Address" value="<?php if(isset($error)){ echo htmlspecialchars($_POST['email'], ENT_QUOTES); } ?>" tabindex="2">
                </div>
                <div class="form-group">
                    <input type="text" name="firstname" id="firstname" class="form-control input-lg" placeholder="First Name" value="<?php if(isset($error)){ echo htmlspecialchars($_POST['email'], ENT_QUOTES); } ?>" tabindex="3">
                </div>
                <div class="form-group">
                    <input type="text" name="lastname" id="lastname" class="form-control input-lg" placeholder="Last Name" value="<?php if(isset($error)){ echo htmlspecialchars($_POST['email'], ENT_QUOTES); } ?>" tabindex="4">
                </div>
                <div class="row">
                    <div class="col-xs-6 col-sm-6 col-md-6">
                        <div class="form-group">
                            <input type="password" name="password" id="password" class="form-control input-lg" placeholder="Password" tabindex="5">
                        </div>
                    </div>
                    <div class="col-xs-6 col-sm-6 col-md-6">
                        <div class="form-group">
                            <input type="password" name="passwordConfirm" id="passwordConfirm" class="form-control input-lg" placeholder="Confirm Password" tabindex="6">
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-xs-6 col-md-6"><input type="submit" name="submit" value="Register" class="btn btn-primary btn-block btn-lg" tabindex="7"></div>
                </div>
            </form>
        </div>
    </div>

</div>

<?php
//include header template
require('layout/footer.php');
?>
