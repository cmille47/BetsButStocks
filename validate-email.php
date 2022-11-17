<?php

$servername = "localhost";
$username = "hplante";
$password = "pwpwpwpw";
$database = "hplante";

$connection = new mysqli($servername, $username, $password, $database);

$sql = sprintf("SELECT * FROM Users WHERE email = '%s'", $connection->real_escape_string($_GET["email"]));
                
$result = $connection->query($sql);

$is_available = $result->num_rows === 0;

header("Content-Type: application/json");

echo json_encode(["available" => $is_available]);