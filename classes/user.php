<?php
include('password.php');
class User extends Password {
    private $_db;
    function __construct($db){
    	parent::__construct();
    	$this->_db = $db;
    }
	private function get_user_hash($conn, $username) {
            $qStr = "SELECT password, username, memberID "
                    . "FROM Member "
                    . "WHERE username = '".$username."' AND active='Yes'";
            $result = $conn->query($qStr);
            if ($result->num_rows == 0) {
                return '';
            } else {
                $hashedPassword = $result->fetch_assoc();
                return $hashedPassword['password'];
            }
	}

        public function isValidUsername($username){
            if (strlen($username) < 3) { return false; }
            if (strlen($username) > 17) { return false; }
            if (!ctype_alnum($username)) { return false; }
            return true;
	}
	public function login($conn, $username, $password) {
            if (!$this->isValidUsername($username)) { return false; }
            if (strlen($password) < 3) { return false; }
            $storedPassword = $this->get_user_hash($conn, $username);
            if ($this->password_verify($password, $storedPassword)) {
                $_SESSION['loggedin'] = true;
                $_SESSION['username'] = $row['username'];
                $_SESSION['memberID'] = $row['memberID'];
                return true;
            } else {
//                return 'bad password: '.$password.' vs. '.$row['password'];
                return false;
            }
	}
        
	public function logout(){
            session_destroy();
	}
	public function is_logged_in(){
            if(isset($_SESSION['loggedin']) && $_SESSION['loggedin'] == true){
                    return true;
            }
	}
}
