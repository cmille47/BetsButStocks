<?php
session_start();

if (isset($_SESSION["user_id"])) {
    
    $servername = "localhost";
    $username = "hplante";
    $password = "pwpwpwpw";
    $database = "hplante";

    $connection = new mysqli($servername, $username, $password, $database);
    if ($connection->connect_error) {
        die("Connection failed: " . $connection->connect_error);
    }

    $user = $_SESSION["user_id"];
    $contract_id = $_POST['contract_id'];
    $amount = $_POST["list_price"];

    $amount = filter_input(INPUT_POST, "list_price", FILTER_VALIDATE_INT);
    
    if ($amount > 0) {
        $insert_sql = "update Contracts set for_sale = 1, sale_price = $amount where contract_id = $contract_id;";
        $insert_result = $connection->query($insert_sql);
        echo "<script type=\"text/javascript\"> alert('Bet successfully listed!'); window.location.href='my_bets.php';</script>";
        //header("Location: show_user_bets.php");
        exit;
    }
    else {
        echo "<script type=\"text/javascript\"> alert('Listing not processed! Enter positive dinteger input!'); window.location.href='my_bets.php';</script>";
    }
    


}   
?>