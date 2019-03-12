<?php
require dirname(__FILE__) . '\..\includes\config.php';

$exit_code = 1;

// From the command line.
$username = $argv[1];

// Initialize database connection
$mysqli = new mysqli(HOST, APP_LOGIN_DB_USER, APP_LOGIN_DB_PASSWORD, APP_LOGIN_DB_DATABASE);

if ($mysqli->connect_errno) {
    printf("Connect failed: %s\n", $mysqli->connect_error);
    exit(2);
}

$sql = "DELETE FROM users WHERE username = ?";
if($stmt = $mysqli->prepare($sql)) {
  $stmt->bind_param('s', $username);
  $stmt->execute();
  
  printf("%d row affected.\n", $stmt->affected_rows);
  printf("Successfully deleted user %s.\n", $username);
  
  $exit_code = 0;
} else {
  printf("Error processing request: %s\n", $mysqli->error);
  $exit_code = 1;
}

$mysqli->close();
exit($exit_code);
?> 