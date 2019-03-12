<?php

namespace Auth;


class Auth {

    private static $SESSION_USERID = 'user_id';
    private static $SESSION_USERNAME = 'username';
    private static $UNAUTHENTICATED_ROLE_VALUE = '__unauthenticated__';

    private $hostname = null;
    private $database = null;
    private $username = null;
    private $password = null;

    private $mysqli = null;

    function __construct(){}
	
    public function configure($hostname = null, $database = null, $username = null, $password = null) {
        $this->hostname = $hostname;
        $this->database = $database;
        $this->username = $username;
        $this->password = $password;

        $this->mysqli = new \mysqli(
            $this->hostname,
            $this->username,
            $this->password,
            $this->database
        );
    }

    public function login($user = null, $password = null) {

        $sql = "SELECT id, password, salt FROM users WHERE username = ? LIMIT 1";
        if($stmt = $this->mysqli->prepare($sql)) {
            $stmt->bind_param('s', $user);
            $stmt->execute();
            $stmt->store_result();

            $id = null;
            $curr_password = null;
            $salt = null;

            $stmt->bind_result($id, $curr_password, $salt);
            $stmt->fetch();

            if ($stmt->num_rows == 1) {
                //$password = hash('sha512', (hash('sha512', $password) . $salt));
				$password = hash('sha512', $password . $salt);

                if ($curr_password == $password) {
                    $_SESSION[Auth::$SESSION_USERID] = preg_replace("/[^0-9]+/","", $id);
                    $_SESSION[Auth::$SESSION_USERNAME] = preg_replace("/[^a-zA-Z0-9_\-]+/","", $user);

                    return true;
                } else {
                  return false;
                }
            } else {
                return false;
            }
        }
    }

    public function validateSession() {
        if (isset($_SESSION[Auth::$SESSION_USERID], $_SESSION[Auth::$SESSION_USERNAME])) {
            $sql = "SELECT id FROM users WHERE id = ? LIMIT 1";
            $id = $_SESSION[Auth::$SESSION_USERID];

            if ($stmt = $this->mysqli->prepare($sql)) {
                $stmt->bind_param('i', $id);
                $stmt->execute();
                $stmt->store_result();

                if ($stmt->num_rows == 1) {
                    return true;
                } else {
                    return false;
                }
            }
        } else {
            return false;
        }
    }

    public function getUserSessionRole() {
        if (isset($_SESSION[Auth::$SESSION_USERID], $_SESSION[Auth::$SESSION_USERNAME])) {
            $sql = "SELECT role FROM users WHERE id = ? LIMIT 1";
            $id = $_SESSION[Auth::$SESSION_USERID];

            if ($stmt = $this->mysqli->prepare($sql)) {
                $stmt->bind_param('i', $id);
                $stmt->execute();
                $stmt->store_result();


                $role = null;

                $stmt->bind_result($role);
                $stmt->fetch();

                if ($stmt->num_rows == 1) {
                    return preg_replace("/[^a-zA-Z0-9_\-]+/","", $role);
                } else {
                    return Auth::$UNAUTHENTICATED_ROLE_VALUE;
                }
            }
        } else {
            return Auth::$UNAUTHENTICATED_ROLE_VALUE;
        }
    }
} 