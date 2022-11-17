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

    mysqli_begin_transaction($connection);
    try{
        $contract_id = $_POST["contract_id"];
        $sql = "select user_id, sale_price from Contracts where contract_id = $contract_id";
        $result = $connection->query($sql);
        $row = $result->fetch_assoc();

        $buyer = $_SESSION["user_id"];
        $seller = $row["user_id"];
        $sale_price = $row["sale_price"];


        $charge_buyer = "update Users set balance=balance-$sale_price where user_id = $buyer;";
        $update_result = $connection->query($charge_buyer);

        if ($update_result) {
            $pay_seller = "update Users set balance=balance+$sale_price where user_id = $seller;";
            $result_sale = $connection->query($pay_seller);

            $sql = "select * from Contracts where contract_id = $contract_id;";
            $result = $connection->query($sql);
            $row = $result->fetch_assoc();

            $sql = "update Contracts set paidout = 1 where contract_id = $contract_id;";
            $result = $connection->query($sql);

            $sql = "insert into Contracts (user_id, game_id, type, bet_choice, amount, ml_home_odds, ml_away_odds, s_home_odds, s_away_odds, s_home_line, s_away_line, ou_total, ou_over_odds, ou_under_odds, purchase_date, previous_owner_id, purchased_price, original_purchase_date) values ($buyer, {$row["game_id"]}, '{$row["type"]}', '{$row["bet_choice"]}', {$row["amount"]}, {$row["ml_home_odds"]}, {$row["ml_away_odds"]}, {$row["s_home_odds"]}, {$row["s_away_odds"]}, {$row["s_home_line"]}, {$row["s_away_line"]}, {$row["ou_total"]}, {$row["ou_over_odds"]}, {$row["ou_under_odds"]}, NOW(), $seller, $sale_price, STR_TO_DATE('{$row["original_purchase_date"]}', '%Y-%m-%d %H:%i:%s.%f'));";
            $result = $connection->query($sql);

            //header("Location: show_user_bets.php");
            mysqli_commit($connection);
            echo "<script type=\"text/javascript\"> alert('Bet successfully purchased!'); window.location.href='show_user_bets.php';</script>";
            exit;
        }
        else {
            echo "<script type=\"text/javascript\"> alert('Bet not processed! Withdrawal amount exceeded!'); window.location.href='listings.php';</script>";
            mysqli_rollbakc($connection);
        }
    } catch (error $e) {
        echo "Something went wrong sorry";
        mysqli_rollbakc($connection);
    }



}   
?>