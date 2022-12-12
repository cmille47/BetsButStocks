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
    $homeTeam = $_POST['homeTeam'];
    mysqli_autocommit($connection,FALSE);
    mysqli_begin_transaction($connection);
    try{
        $sql = "select Games.game_id, Bets.ml_home_odds, Bets.ml_away_odds, Bets.s_home_odds, Bets.s_away_odds, Bets.s_home_line, Bets.s_away_line, Bets.ou_total, Bets.ou_over_odds, Bets.ou_under_odds from Games, Bets where Games.game_id = Bets.game_id and home_team like \"$homeTeam\" order by Bets.collect_date desc limit 1;";
        $result = $connection->query($sql);
        $row = $result->fetch_assoc();
        
        $game_id = $row["game_id"];
        $ml_home_odds = $row["ml_home_odds"];
        $ml_away_odds = $row["ml_away_odds"];
        $s_home_odds = $row["s_home_odds"];
        $s_away_odds = $row["s_away_odds"];
        $s_home_line = $row["s_home_line"];
        $s_away_line = $row["s_away_line"];
        $ou_total = $row["ou_total"];
        $ou_over_odds = $row["ou_over_odds"];
        $ou_under_odds = $row["ou_under_odds"];

        $type = $_POST["type"];
        $choice = $_POST["choice"];
        $amount = filter_input(INPUT_POST, "wager", FILTER_VALIDATE_INT);
        if ($amount > 0) {
            $ifamount='#';
            $update_sql = "update Users set balance=balance-$amount where user_id = $user;";
            $update_result = $connection->query($update_sql);
            if ($update_result) {
                $ifupdateresult='$';
                $pay_book = "update Users set balance=balance+$amount where user_id = 77;";
                $pay_book_result = $connection->query($pay_book);
                $insert_sql = "insert into Contracts (user_id, game_id, type, bet_choice, amount, ml_home_odds, ml_away_odds, s_home_odds, s_away_odds, s_home_line, s_away_line, ou_total, ou_over_odds, ou_under_odds, purchase_date, original_purchase_date, purchased_price) values ($user, $game_id, '$type', '$choice', $amount, $ml_home_odds, $ml_away_odds, $s_home_odds, $s_away_odds, $s_home_line, $s_away_line, $ou_total, $ou_over_odds, $ou_under_odds, NOW(), NOW(), $amount);";
                $insert_result = $connection->query($insert_sql);
                $commit = mysqli_commit($connection);
                if (!$commit) {
                    echo "Commit transaction failed";
                    exit();
                } else {
                    echo "<script type=\"text/javascript\"> alert('Bet successfully placed!'); window.location.href='my_bets.php';</script>"; // redirects after alert of successful purchase
                }
            }
            else {
                echo "<script type=\"text/javascript\"> alert('Bet not processed! Withdrawal amount exceeded!'); window.location.href='nfl.php';</script>";
                mysqli_rollback($connection);
            }
        }
        else {
            echo "<script type=\"text/javascript\"> alert('Bet not processed! Enter positive integer input!'); window.location.href='nfl.php';</script>";
        }
        
    } catch (error $e){
        mysqli_rollback($connection);
        echo "<script type=\"text/javascript\"> alert('Failed to process bet! Please try again!'); window.location.href='nfl.php';</script>";
    }
    mysqli_close($connection);
}   
?>