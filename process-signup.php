<?php

$name = $_POST["name"];
$email = $_POST["email"];
$email_confirmation = $_POST["email_confirmation"];
$chosen_password = $_POST["password"];
$password_confirmation = $_POST["password_confirmation"];

if (empty($name)) {
    die("Name is required");
}

if (empty($email)) {
    die("Email is required");
}

if (empty($email_confirmation)) {
    die("Email Confirmation is required");
}

if (empty($chosen_password)) {
    die("Password is required");
}

if (empty($password_confirmation)) {
    die("Password Confirmation is required");
}

if ($email !== $email_confirmation) {
    die("Emails must match");
}

if ($chosen_password !== $password_confirmation) {
    die("Passwords must match");
}

if ( ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
    die("Valid email is required");
}

$servername = "localhost";
$username = "hplante";
$password = "pwpwpwpw";
$database = "hplante";

$connection = new mysqli($servername, $username, $password, $database);

// connect to db
if ($connection->connect_error) {
    die("Connection failed: " . $connection->connect_error);
}

$sql_check = "SELECT * FROM Users WHERE email = '$email'";

$result = $connection->query($sql_check);
if (mysqli_num_rows($result) == 0) {
    $sql_insert =  "INSERT INTO Users (name, email, password) VALUES ('$name', '$email', '$chosen_password')";
    if ($connection->query($sql_insert)) {
        header("Location: login.php");
    }
}
else {
    echo "<script type=\"text/javascript\"> alert('Account already exists! Log in or create an account with a new email!'); window.location.href='create_account.html';</script>";
    //header("Location: create_account.html");
}