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
    $sql = "SELECT * FROM Users WHERE user_id = {$_SESSION["user_id"]}";
          
    $result = $connection->query($sql);
    
    $user = $result->fetch_assoc();
    $balance = $user['balance'];
    $balance_str = "Current Balance: $" . $balance;
}
else {
    Header("Location: index.php");
  }
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-Zenh87qX5JnK2Jl0vWa8Ck2rdkQ2Bzep5IDxbcnCeuOxjzrPF/et3URy9Bv1WTRi" crossorigin="anonymous">
        <link rel="stylesheet" href="marketplace.css">
        <title>Bets for Sale</title>
    </head>
    <body>
        <nav class="navbar navbar-expand-lg bg-light fixed-top">
            <div class="container-fluid">
              <a class="navbar-brand" href="index.php">BetsButStocks</a>
              <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
              </button>
              <div class="collapse navbar-collapse align-center" id="navbarSupportedContent">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                  <li class="nav-item">
                    <a class="nav-link active" aria-current="page" href="nfl.php">Bet NFL</a>
                  </li>
                  <li class="nav-item">
                    <a class="nav-link active" aria-current="page" href="marketplace.php">Bets For Sale</a>
                  </li>
                  <li class="nav-item">
                    <a class="nav-link active" aria-current="page" href="my_bets.php">My Bets</a>
                  </li>
                  <li class="nav-item">
                    <a class="nav-link active" aria-current="page" href="my_account.php">My Account</a>
                  </li>
                </ul>
                <h5><?= htmlspecialchars($balance_str) ?></h5>
              </div>
            </div>
          </nav>

        <div class="bg-image">
            <h1 style="color:white; padding-top:75px; text-align: center;">Available Bets</h1>
            <div class="cards">

                <?php

                        $sql = "select Games.home_team as home_team, Games.away_team as away_team, Games.game_id as game_id, Games.game_date as game_date, Contracts.contract_id as contract_id, 
                                    Contracts.type as c_type, Contracts.bet_choice as c_bet_choice, Contracts.amount as amount, Contracts.ml_home_odds as c_ml_home_odds, Contracts.ml_away_odds as c_ml_away_odds,
                                    Contracts.s_home_odds as c_s_home_odds, Contracts.s_away_odds as c_s_away_odds, Contracts.s_home_line as c_s_home_line, Contracts.s_away_line as c_s_away_line,
                                    Contracts.ou_total as c_ou_total, Contracts.ou_over_odds as c_ou_over_odds, Contracts.ou_under_odds as c_ou_under_odds, Contracts.sale_price as sale_price,
                                    Bets.ml_home_odds as b_ml_home_odds, Bets.ml_away_odds as b_ml_away_odds, Bets.s_home_odds as b_s_home_odds, Bets.s_away_odds as b_s_away_odds, Bets.s_home_line as b_s_home_line,
                                    Bets.s_away_line as b_s_away_line, Bets.ou_total as b_ou_total, Bets.ou_over_odds as b_ou_over_odds, Bets.ou_under_odds as b_ou_under_odds
                                from Contracts, Games, Bets 
                                where Contracts.game_id = Games.game_id
                                and Contracts.for_sale = 1 
                                and Contracts.paidout = 0 and 
                                user_id != " . $_SESSION["user_id"] . "
                                and Games.game_date > DATE_ADD(NOW(), INTERVAL - 2 HOUR) 
                                and Bets.game_id = Games.game_id
                                and Bets.collect_date > DATE_ADD((SELECT MAX(collect_date) FROM Bets), INTERVAL -5 MINUTE)
                                order by Games.game_date;;";
                        //" . $_SESSION["user_id"] . "
                        $result = $connection->query($sql);

                        $n_cards = 0;
                        $prev = '';

                        date_default_timezone_set('EST');
                        $cur_time = strtotime(date("Y-m-d H:i:s", time()));

                        while($row = $result->fetch_assoc()) {
                            //echo $row['game_date'];

                            if ($row['c_type'] == 'ML'){
                                if ($row['c_bet_choice'] == 'Home'){
                                    $odds = $row['c_ml_home_odds'];
                                    $team = $row['home_team'];
                                    $type = 'ml_home_odds';
                                    $book_odds = $row['b_ml_home_odds'];
                                    
                                }
                                else{
                                    $odds = $row['c_ml_away_odds'];
                                    $team = $row['away_team'];
                                    $type = 'ml_away_odds';
                                    $book_odds = $row['b_ml_away_odds'];
                                    
                                }
                                $team = $team . " ML";
                                $book_odds_int = $book_odds;
                                if ($book_odds > 0) {$book_odds = "+" . $book_odds;}
                                $book_odds_str = $team . " at " . $book_odds . " odds";
                            }
                            else if ($row['c_type'] == 'Spread'){
                                $line = '0';
                                if ($row['c_bet_choice'] == 'Home'){
                                    $odds = $row['c_s_home_odds'];
                                    $line = $row['c_s_home_line'];
                                    $team = $row['home_team'];
                                    $type = 's_home_line';
                                    $book_odds = $row['b_s_home_odds'];
                                    $book_line = $row['b_s_home_line'];
                                }
                                else{
                                    $odds = $row['c_s_away_odds'];
                                    $line = $row['c_s_away_line'];
                                    $team = $row['away_team'];
                                    $type = 's_away_line';
                                    $book_odds = $row['b_s_away_odds'];
                                    $book_line = $row['b_s_away_line'];
                                }
                                $book_line_int = $book_line;
                                $list_line_int = $line;
                                $book_odds_int = $book_odds;
                                if (intval($line) > 0) { $line = "+". $line; }
                                if (intval($book_line) > 0) { $book_line = "+". $book_line; }
                                if ($book_odds > 0) {$book_odds = "+" . $book_odds;}
                                $book_team = $team . " " . $book_line;
                                $team = $team . " " . $line;
                                
                                $book_odds_str = $team . " at " . $book_odds . " odds";
                            }
                            else{
                                if ($row['c_bet_choice'] == 'Over'){
                                    $odds = $row['c_ou_over_odds'];
                                    $book_odds = $row['b_ou_over_odds'];
                                }
                                else{
                                    $odds = $row['c_ou_under_odds'];
                                    $book_odds = $row['b_ou_under_odds'];
                                }

                                $book_line_int = $row['b_ou_total'];
                                $list_line_int = $row['c_ou_total'];
                                $book_odds_int = $book_odds;
                                //$list_odds_int = $odds;

                                $type = 'ou_total';
                                $team1 = explode(' ', $row['away_team']);
                                $team2 = explode(' ', $row['home_team']);
                                $team = end($team1) . " at " . end($team2) . ": " . $row['c_bet_choice'] . " " . $row['c_ou_total'];
                                if ($book_odds > 0) {$book_odds = "+" . $book_odds;}
                                $book_odds_str = end($team1) . " at " . end($team2) . " " . $row['c_bet_choice'] . " " . $row['b_ou_total'] . " at " . $book_odds . " odds";
                            }

                            $initial_amount = $row['amount'];
                            $sale_price = $row['sale_price'];
                            if ($odds < 0 ) {
                                $win = round((100 / (-1 *$odds)) * $initial_amount);
                            } else {
                                $win = round(($odds / 100) * $initial_amount);
                            }

                            if ($win > $sale_price) {
                                $list_odds = "+" . round((100 * $win) / $row['sale_price']);
                            } else {
                                $list_odds = round((-100 * $row['sale_price']) / $win);
                            }

                            if (substr($list_odds, 0, 1) == "+") {$list_odds_int = intval(substr($list_odds, 1));}
                            else {$list_odds_int = intval($list_odds);}
                            

                            if ($row['c_type'] == "ML") {
                                if (($list_odds_int > $book_odds_int)) {$explanation = "This ticket has better odds than the book is currently offering.";}
                                else if ($list_odds_int == $book_odds_int) {$explanation = "This ticket has the same odds that the book is currently offering";}
                                else {$explanation = "Better odds are available via the \"Bet NFL\" page.";}
                            } else if ($row['c_type'] == "Spread") {
                                if ($list_odds_int == $book_odds_int && $list_line_int && $book_line_int) {$explanation = "This ticket has the same odds that the book is currently offering";}
                                else if (($list_line_int > $book_line_int && $list_odds_int > $book_odds_int) || ($list_odds_int > $book_odds_int && $list_line_int == $book_line_int) || ($list_line_int > $book_line_int && $list_odds_int == $book_odds_int)) {$explanation = "This ticket has more value than the book is currently offering.";}
                                else if (($list_line_int < $book_line_int && $list_odds_int < $book_line_int) || ($list_odds_int < $book_odds_int && $list_line_int == $book_line_int) || ($list_line_int < $book_line_int && $list_odds_int == $book_odds_int)){$explanation = "The book can offer better value than this ticket on the \"Bet NFL\" page.";}
                                else {$explanation = "There is no definitive answer as to whether this ticket offers more value than is available via the \"Bet NFL\" page. Use your best judgement.";}
                            } else {
                                if ($list_odds_int == $book_odds_int && $list_line_int && $book_line_int) {$explanation = "This ticket has the same odds that the book is currently offering";}
                                else if ($row['c_bet_choice'] == 'Over'){
                                    if (($list_line_int < $book_line_int && $list_odds_int == $book_odds_int) || ($list_line_int == $book_line_int && $list_odds_int > $book_odds_int) || ($list_line_int < $book_line_int && $list_odds_int > $book_odds_int)) {$explanation = "This ticket has more value than the book is currently offering.";}
                                    else if (($list_line_int > $book_line_int && $list_odds_int == $book_odds_int) || ($list_line_int == $book_line_int && $list_odds_int < $book_odds_int) || ($list_line_int > $book_line_int && $list_odds_int < $book_odds_int)) {$explanation = "The book can offer better value than this ticket on the \"Bet NFL\" page.";}
                                    else {$explanation = "There is no definitive answer as to whether this ticket offers more value than is available via the \"Bet NFL\" page. Use your best judgement.";}
                                } else {
                                    if (($list_line_int > $book_line_int && $list_odds_int == $book_odds_int) || ($list_line_int == $book_line_int && $list_odds_int > $book_odds_int) || ($list_line_int > $book_line_int && $list_odds_int > $book_odds_int)) {$explanation = "This ticket has more value than the book is currently offering.";}
                                    else if (($list_line_int < $book_line_int && $list_odds_int == $book_odds_int) || ($list_line_int == $book_line_int && $list_odds_int < $book_odds_int) || ($list_line_int < $book_line_int && $list_odds_int < $book_odds_int)) {$explanation = "The book can offer better value than this ticket on the \"Bet NFL\" page.";}
                                    else {$explanation = "There is no definitive answer as to whether this ticket offers more value than is available via the \"Bet NFL\" page. Use your best judgement.";}
                                }

                            }

                            $end_book_sales_time = strtotime(date('Y-m-d H:i:s', strtotime($row['game_date'])));
                            if ($end_book_sales_time < $cur_time) {
                                $book_odds_str = "UNAVAILABLE";
                                $explanation = "Since the game has started the only way to purchase this bet is through the marketplace.";
                            }


                            $ticket_cost = $row['sale_price'];
                            $payout = $win + $sale_price;
                            $game = $row['game_id'];

                            $game_dt = $row['game_date'];
                            $game_dayofweek = date('l', strtotime($game_dt));
                            
                            $game_date = explode(' ', $game_dt)[0];
                            $game_date_comp = explode('-', $game_date);
                            $game_month = $game_date_comp[1];
                            $game_day = $game_date_comp[2];

                            $game_time = explode(' ', $game_dt)[1];
                            $game_time_comp = explode(':', $game_time);
                            $game_hour = intval($game_time_comp[0]);
                            $game_min = $game_time_comp[1];
                            $bet_hour = $game_hour + 2;

                            if ($game_hour >= 12) {
                                $game_AMorPM = 'PM';
                                if ($game_hour > 12) {
                                    $game_hour = $game_hour - 12;
                                }
                            }
                            else {
                                $game_AMorPM = 'AM';
                                if ($game_hour == 0) {
                                    $game_hour = $game_hour + 12;
                                }
                            }

                            if ($bet_hour >= 12) {
                                $bet_AMorPM = 'PM';
                                if ($bet_hour > 12) {
                                    $bet_hour = $bet_hour - 12;
                                }
                            }
                            else {
                                $bet_AMorPM = 'AM';
                                if ($bet_hour == 0) {
                                    $bet_hour = $bet_hour + 12;
                                }
                            }

                            $hteam = $row['home_team'];
                            $vteam = $row['away_team'];

                            if (intval($odds) > 0) { $odds = "+". $odds; }
                            
                            if ($n_cards % 2 == 0){
                                if ($n_cards > 0){
                                    echo "</div> <div class=\"card-group\">";
                                }
                                else{
                                    echo "<div class=\"card-group\">";
                                }
                            } 

                            echo "<div class=\"card\">
                                    <div class=\"card-body\">
                                        <h5 class=\"card-title\">$team <span style=\"float:right;\"> $list_odds </span> 
                                        <br>TICKET COST: \$$sale_price
                                        <br>TO WIN: \$$win
                                        <br>TO COLLECT: \$$payout
                                        <br><br>EXPIRES: $game_dayofweek, $game_month/$game_day at $bet_hour:$game_min $bet_AMorPM
                                        </h5>
                                        <div class=\"accordion\" id=\"accordionExample\">
                                            <div class=\"accordion-item\">
                                                <h2 class=\"accordion-header\" id=\"heading$n_cards\">
                                                    <button class=\"accordion-button collapsed\" type=\"button\" data-bs-toggle=\"collapse\" data-bs-target=\"#collapse$n_cards\" aria-expanded=\"false\" aria-controls=\"collapse$n_cards\">
                                                        More Information
                                                    </button>
                                                </h2>
                                                <div id=\"collapse$n_cards\" class=\"accordion-collapse collapse\" aria-labelledby=\"heading$n_cards\" data-bs-parent=\"#accordionExample\">
                                                    <div class=\"accordion-body\">
                                                        <p> $vteam at $hteam
                                                        <br>$game_dayofweek, $game_month/$game_day at $game_hour:$game_min $game_AMorPM  
                                                        </p>
                                                        <p><strong>Current Book Offering:<br>$book_odds_str</strong><br>$explanation</p>
                                                    </div>
                                                </div>
                                            </div>
                                        <ul class=\"list-group\"><button class=\"btn\" id=".$row['contract_id']." onclick=\"showBet(this)\"><li class=\"list-group-item\"><b>Purchase</b></button></li></ul>
                                        </div>
                                        </div>
                                        </div>";
                            $n_cards = $n_cards + 1;  
                        }
                    if ($n_cards == 0) {echo "<p style=\"text-align:center;color:white;\">There are currently no bets listed on the marketplace.</p>";}
                        
                    //}
                    //if ($n_cards % 2 != 0) {echo "</div>";}
                    //echo "</div>";

                    echo "</div>";
                    echo "<h1 style=\"padding-top:50px; color: white; text-align:center;\">My Listed Bets</h1>";

                    $sql = "select Games.home_team as home_team, Games.away_team as away_team, Games.game_id as game_id, Games.game_date as game_date, Contracts.contract_id as contract_id, 
                                Contracts.type as c_type, Contracts.bet_choice as c_bet_choice, Contracts.amount as amount, Contracts.ml_home_odds as c_ml_home_odds, Contracts.ml_away_odds as c_ml_away_odds,
                                Contracts.s_home_odds as c_s_home_odds, Contracts.s_away_odds as c_s_away_odds, Contracts.s_home_line as c_s_home_line, Contracts.s_away_line as c_s_away_line,
                                Contracts.ou_total as c_ou_total, Contracts.ou_over_odds as c_ou_over_odds, Contracts.ou_under_odds as c_ou_under_odds, Contracts.sale_price as sale_price,
                                Bets.ml_home_odds as b_ml_home_odds, Bets.ml_away_odds as b_ml_away_odds, Bets.s_home_odds as b_s_home_odds, Bets.s_away_odds as b_s_away_odds, Bets.s_home_line as b_s_home_line,
                                Bets.s_away_line as b_s_away_line, Bets.ou_total as b_ou_total, Bets.ou_over_odds as b_ou_over_odds, Bets.ou_under_odds as b_ou_under_odds
                            from Contracts, Games, Bets 
                            where Contracts.game_id = Games.game_id
                            and Contracts.for_sale = 1 
                            and Contracts.paidout = 0 and 
                            user_id = " . $_SESSION["user_id"] . "
                            and Games.game_date > DATE_ADD(NOW(), INTERVAL - 2 HOUR) 
                            and Bets.game_id = Games.game_id
                            and Bets.collect_date > DATE_ADD((SELECT MAX(collect_date) FROM Bets), INTERVAL -5 MINUTE)
                            order by Games.game_date;";
                            
                    $result = $connection->query($sql);

                    if ($n_cards % 2 == 0) {$n_cards = $n_cards + 2;}
                    else {$n_cards = $n_cards + 1;}

                    $temp = $n_cards;
                    $prev = '';
                    $n_ur_cards = 0;

                    while($row = $result->fetch_assoc()) {

                        //$n_cards = 0;
                        
                        if ($row['c_type'] == 'ML'){
                            if ($row['c_bet_choice'] == 'Home'){
                                $odds = $row['c_ml_home_odds'];
                                $team = $row['home_team'];
                                $type = 'ml_home_odds';
                                $book_odds = $row['b_ml_home_odds'];
                                
                            }
                            else{
                                $odds = $row['c_ml_away_odds'];
                                $team = $row['away_team'];
                                $type = 'ml_away_odds';
                                $book_odds = $row['b_ml_away_odds'];
                                
                            }
                            $team = $team . " ML";
                            $book_odds_int = $book_odds;
                            if ($book_odds > 0) {$book_odds = "+" . $book_odds;}
                            $book_odds_str = $team . " at " . $book_odds . " odds";
                        }
                        else if ($row['c_type'] == 'Spread'){
                            $line = '0';
                            if ($row['c_bet_choice'] == 'Home'){
                                $odds = $row['c_s_home_odds'];
                                $line = $row['c_s_home_line'];
                                $team = $row['home_team'];
                                $type = 's_home_line';
                                $book_odds = $row['b_s_home_odds'];
                                $book_line = $row['b_s_home_line'];
                            }
                            else{
                                $odds = $row['c_s_away_odds'];
                                $line = $row['c_s_away_line'];
                                $team = $row['away_team'];
                                $type = 's_away_line';
                                $book_odds = $row['b_s_away_odds'];
                                $book_line = $row['b_s_away_line'];
                            }
                            $book_line_int = $book_line;
                            $list_line_int = $line;
                            $book_odds_int = $book_odds;
                            if (intval($line) > 0) { $line = "+". $line; }
                            if (intval($book_line) > 0) { $book_line = "+". $book_line; }
                            if ($book_odds > 0) {$book_odds = "+" . $book_odds;}
                            $book_team = $team . " " . $book_line;
                            $team = $team . " " . $line;
                            
                            $book_odds_str = $team . " at " . $book_odds . " odds";
                        }
                        else{
                            if ($row['c_bet_choice'] == 'Over'){
                                $odds = $row['c_ou_over_odds'];
                                $book_odds = $row['b_ou_over_odds'];
                            }
                            else{
                                $odds = $row['c_ou_under_odds'];
                                $book_odds = $row['b_ou_under_odds'];
                            }

                            $book_line_int = $row['b_ou_total'];
                            $list_line_int = $row['c_ou_total'];
                            $book_odds_int = $book_odds;
                            //$list_odds_int = $odds;

                            $type = 'ou_total';
                            $team1 = explode(' ', $row['away_team']);
                            $team2 = explode(' ', $row['home_team']);
                            $team = end($team1) . " at " . end($team2) . ": " . $row['c_bet_choice'] . " " . $row['c_ou_total'];
                            if ($book_odds > 0) {$book_odds = "+" . $book_odds;}
                            $book_odds_str = end($team1) . " at " . end($team2) . " " . $row['c_bet_choice'] . " " . $row['b_ou_total'] . " at " . $book_odds . " odds";
                        }

                        $initial_amount = $row['amount'];
                        $sale_price = $row['sale_price'];
                        if ($odds < 0 ) {
                            $win = round((100 / (-1 *$odds)) * $initial_amount);
                        } else {
                            $win = round(($odds / 100) * $initial_amount);
                        }

                        if ($win > $sale_price) {
                            $list_odds = "+" . round((100 * $win) / $row['sale_price']);
                        } else {
                            $list_odds = round((-100 * $row['sale_price']) / $win);
                        }

                        if (substr($list_odds, 0, 1) == "+") {$list_odds_int = intval(substr($list_odds, 1));}
                        else {$list_odds_int = intval($list_odds);}
                        

                        if ($row['c_type'] == "ML") {
                            if (($list_odds_int > $book_odds_int)) {$explanation = "This ticket has better odds than the book is currently offering.";}
                            else if ($list_odds_int == $book_odds_int) {$explanation = "This ticket has the same odds that the book is currently offering";}
                            else {$explanation = "Better odds are available via the \"Bet NFL\" page.";}
                        } else if ($row['c_type'] == "Spread") {
                            if ($list_odds_int == $book_odds_int && $list_line_int && $book_line_int) {$explanation = "This ticket has the same odds that the book is currently offering";}
                            else if (($list_line_int > $book_line_int && $list_odds_int > $book_odds_int) || ($list_odds_int > $book_odds_int && $list_line_int == $book_line_int) || ($list_line_int > $book_line_int && $list_odds_int == $book_odds_int)) {$explanation = "This ticket has more value than the book is currently offering.";}
                            else if (($list_line_int < $book_line_int && $list_odds_int < $book_line_int) || ($list_odds_int < $book_odds_int && $list_line_int == $book_line_int) || ($list_line_int < $book_line_int && $list_odds_int == $book_odds_int)){$explanation = "The book can offer better value than this ticket on the \"Bet NFL\" page.";}
                            else {$explanation = "There is no definitive answer as to whether this ticket offers more value than is available via the \"Bet NFL\" page. Use your best judgement.";}
                        } else {
                            if ($list_odds_int == $book_odds_int && $list_line_int && $book_line_int) {$explanation = "This ticket has the same odds that the book is currently offering";}
                            else if ($row['c_bet_choice'] == 'Over'){
                                if (($list_line_int < $book_line_int && $list_odds_int == $book_odds_int) || ($list_line_int == $book_line_int && $list_odds_int > $book_odds_int) || ($list_line_int < $book_line_int && $list_odds_int > $book_odds_int)) {$explanation = "This ticket has more value than the book is currently offering.";}
                                else if (($list_line_int > $book_line_int && $list_odds_int == $book_odds_int) || ($list_line_int == $book_line_int && $list_odds_int < $book_odds_int) || ($list_line_int > $book_line_int && $list_odds_int < $book_odds_int)) {$explanation = "The book can offer better value than this ticket on the \"Bet NFL\" page.";}
                                else {$explanation = "There is no definitive answer as to whether this ticket offers more value than is available via the \"Bet NFL\" page. Use your best judgement.";}
                            } else {
                                if (($list_line_int > $book_line_int && $list_odds_int == $book_odds_int) || ($list_line_int == $book_line_int && $list_odds_int > $book_odds_int) || ($list_line_int > $book_line_int && $list_odds_int > $book_odds_int)) {$explanation = "This ticket has more value than the book is currently offering.";}
                                else if (($list_line_int < $book_line_int && $list_odds_int == $book_odds_int) || ($list_line_int == $book_line_int && $list_odds_int < $book_odds_int) || ($list_line_int < $book_line_int && $list_odds_int < $book_odds_int)) {$explanation = "The book can offer better value than this ticket on the \"Bet NFL\" page.";}
                                else {$explanation = "There is no definitive answer as to whether this ticket offers more value than is available via the \"Bet NFL\" page. Use your best judgement.";}
                            }

                        }

                        $end_book_sales_time = strtotime(date('Y-m-d H:i:s', strtotime($row['game_date'])));
                        if ($end_book_sales_time < $cur_time) {
                            $book_odds_str = "UNAVAILABLE";
                            $explanation = "Since the game has started the only way to purchase this bet is through the marketplace.";
                        }


                        $ticket_cost = $row['sale_price'];
                        $payout = $win + $sale_price;
                        $game = $row['game_id'];

                        $game_dt = $row['game_date'];
                        $game_dayofweek = date('l', strtotime($game_dt));
                        
                        $game_date = explode(' ', $game_dt)[0];
                        $game_date_comp = explode('-', $game_date);
                        $game_month = $game_date_comp[1];
                        $game_day = $game_date_comp[2];

                        $game_time = explode(' ', $game_dt)[1];
                        $game_time_comp = explode(':', $game_time);
                        $game_hour = intval($game_time_comp[0]);
                        $game_min = $game_time_comp[1];
                        $bet_hour = $game_hour + 2;

                        if ($game_hour >= 12) {
                            $game_AMorPM = 'PM';
                            if ($game_hour > 12) {
                                $game_hour = $game_hour - 12;
                            }
                        }
                        else {
                            $game_AMorPM = 'AM';
                            if ($game_hour == 0) {
                                $game_hour = $game_hour + 12;
                            }
                        }

                        if ($bet_hour >= 12) {
                            $bet_AMorPM = 'PM';
                            if ($bet_hour > 12) {
                                $bet_hour = $bet_hour - 12;
                            }
                        }
                        else {
                            $bet_AMorPM = 'AM';
                            if ($bet_hour == 0) {
                                $bet_hour = $bet_hour + 12;
                            }
                        }

                        $hteam = $row['home_team'];
                        $vteam = $row['away_team'];

                        if (intval($odds) > 0) { $odds = "+". $odds; }
                        
                        if ($n_cards % 2 == 0){
                            if ($n_ur_cards > 0){
                                echo "</div><div class=\"card-group\">";
                            }
                            else{
                                echo "<div class=\"card-group\">";
                            }
                        } 

                        echo "<div class=\"card\">
                                <div class=\"card-body\">
                                    <h5 class=\"card-title\">$team <span style=\"float:right;\"> $list_odds </span> 
                                    <br>TICKET COST: \$$sale_price
                                    <br>TO WIN: \$$win
                                    <br>TO COLLECT: \$$payout
                                    <br><br>EXPIRES: $game_dayofweek, $game_month/$game_day at $bet_hour:$game_min $bet_AMorPM
                                    </h5>
                                    <div class=\"accordion\" id=\"accordionExample\">
                                        <div class=\"accordion-item\">
                                            <h2 class=\"accordion-header\" id=\"heading$n_cards\">
                                                <button class=\"accordion-button collapsed\" type=\"button\" data-bs-toggle=\"collapse\" data-bs-target=\"#collapse$n_cards\" aria-expanded=\"false\" aria-controls=\"collapse$n_cards\">
                                                    More Information
                                                </button>
                                            </h2>
                                            <div id=\"collapse$n_cards\" class=\"accordion-collapse collapse\" aria-labelledby=\"heading$n_cards\" data-bs-parent=\"#accordionExample\">
                                                <div class=\"accordion-body\">
                                                    <p> $vteam at $hteam
                                                    <br>$game_dayofweek, $game_month/$game_day at $game_hour:$game_min $game_AMorPM  
                                                    </p>
                                                    <p><strong>Current Book Offering:<br>$book_odds_str</strong><br>$explanation</p>
                                                </div>
                                            </div>
                                    </div>
                                    <ul class=\"list-group\">
                                        <button class=\"btn\" id=".$row['contract_id']. "onclick=\"showBet(this)\"><li class=\"list-group-item\">" . "Navigate to the \"My Bets\" page to change the list price for this bet.</li></button>
                                    </ul>
                                </div>
                                </div>
                                </div>";
                        $n_cards = $n_cards + 1;
                        $n_ur_cards = $n_ur_cards + 1;

                    }
                    if ($n_cards == $temp) {echo "<p style=\"color:white; text-align: center;padding-top:50px;\">You currently don't have any bets listed on the marketplace.</p>";}
                    mysqli_close($connection);
                ?>

            </div>
        </div>

        <div class="fixed-bottom card bg-light" id="bet-preview" style="display: none"></div>
        <script
            src="https://code.jquery.com/jquery-3.6.1.js"
            integrity="sha256-3zlB5s2uwoUzrXK3BT7AX3FyvojsraNFxCc2vC/7pNI="
            crossorigin="anonymous">
        </script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-OERcA2EqjJCMA+/3y+gxIOqMEjwtxJY7qPCqsdltbNJuaOe923+mo//f6V8Qbsw3" crossorigin="anonymous"></script>
        <script type="text/javascript" src="contract_transfer.js"></script>
    </body>
    
</html>