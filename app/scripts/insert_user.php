<?php
require dirname(__FILE__) . '\..\includes\config.php';

$exit_code = 1;

// From the command line.
$username = $argv[1];
$password = $argv[2];
$role = $argv[3];

// Initialize database connection
$mysqli = new mysqli(HOST, APP_LOGIN_DB_USER, APP_LOGIN_DB_PASSWORD, APP_LOGIN_DB_DATABASE);

if ($mysqli->connect_errno) {
    printf("Connect failed: %s\n", $mysqli->connect_error);
    exit(2);
}

// Create a random salt
$salt = hash('sha512', uniqid(mt_rand(1, mt_getrandmax()), true));

// Create salted password 
$password = hash('sha512', $password . $salt);

$sql = "INSERT INTO users (username, password, salt, role) VALUES(?,?,?,?)";
if($stmt = $mysqli->prepare($sql)) {
  $stmt->bind_param('ssss', $username, $password, $salt, $role);
  $stmt->execute();
  
  printf("%d row inserted.\n", $stmt->affected_rows);
  printf("Successfully inserted user %s with role %s.\n", $username, $role);
  
  $exit_code = 0;
} else {
  printf("Error processing request: %s\n", $mysqli->error);
  $exit_code = 1;
}

$mysqli->close();
exit($exit_code);
?> 