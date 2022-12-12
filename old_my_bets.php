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
        <link rel="stylesheet" href="my_bets.css">
        <title>My Bets</title>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.9.4/Chart.js"> </script>
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
                    <a class="nav-link active" aria-current="page" href="listings.php">Bets For Sale</a>
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
            <div class="cards">
                <?php
                    $sql = "(select game_date, purchase_date, paidout, contract_id, Games.game_id, for_sale, sale_price, bet_choice, type, amount, home_team, away_team, ou_total, ou_under_odds, ou_over_odds, ml_home_odds, ml_away_odds, s_home_odds, s_away_odds, s_home_line, s_away_line from Contracts, Games where Contracts.game_id=Games.game_id and paidout=0 and user_id=" . $_SESSION["user_id"] .  " ORDER BY game_date, home_team" . ")";
                    $result = $connection->query($sql);
                    $n_cards = 0;
                    $prev = '';

                    if (!empty($result)){
                        while($row = $result->fetch_assoc()) {
                            
                            $payout = 0;
                            $data = array();
                            $labels = array();
                            $labels[] = "Original Purchase";
                            # logic to figure out what the card should be about and payout
                            if ($row['type'] == 'ML'){
                                if ($row['bet_choice'] == 'Home'){
                                    $odds = $row['ml_home_odds'];
                                    $team = $row['home_team'];
                                    $type = 'ml_home_odds';
                                    $spread = $row['s_home_line'];
                                }
                                else{
                                    $odds = $row['ml_away_odds'];
                                    $team = $row['away_team'];
                                    $type = 'ml_away_odds';
                                    $spread = $row['s_away_line'];
                                }
                                $data[] = $odds;
                                $team = $team . " ML";
                                $label = "Probability of Bet Success";
                            }
                            else if ($row['type'] == 'Spread'){
                                $line = '0';
                                $label = "Probability of Bet Success";
                                if ($row['bet_choice'] == 'Home'){
                                    $odds = $row['s_home_odds'];
                                    $line = $row['s_home_line'];
                                    $team = $row['home_team'];
                                    $type = 's_home_line';
                                }
                                else{
                                    $odds = $row['s_away_odds'];
                                    $line = $row['s_away_line'];
                                    $team = $row['away_team'];
                                    $type = 's_away_line';
                                }
                                $data[] = $line;
                                if (intval($line) > 0) { $line = "+". $line; }
                                $team = $team . " " . $line;
                            }
                            else{
                                $label = "Probability of Bet Success";
                                if ($row['bet_choice'] == 'Over'){
                                    $odds = $row['ou_over_odds'];
                                }
                                else{
                                    $odds = $row['ou_under_odds'];
                                }
                                $type = 'ou_total';
                                $team1 = explode(' ', $row['away_team']);
                                $team2 = explode(' ', $row['home_team']);
                                $team = end($team1) . " at " . end($team2) . ": " . $row['bet_choice'] . " " . $row['ou_total'];
                                $data[] = $row['ou_total'];
                            }

                            $amount = $row['amount'];
                            if ($odds < 0){
                                $payout = round((100 / (-1 *$odds)) * $amount);
                            }
                            else{
                                $payout = round(($odds / 100) * $amount);
                            }

                            $toWin = $payout;
                            $ticket_cost = $row['amount'];
                            $payout = $payout + $ticket_cost;
                            $game = $row['game_id'];
                            $purchase_date = $row['purchase_date'];

                            $probs = array();
                            if ($row['type'] == 'ML') {
                                if ($odds < 0) {
                                    $prob = round(((-1*$odds) / ((-1*$odds) + 100)), 4);
                                    $probs[] = $prob;
                                } 
                                else {
                                    $prob = round((100 / ($odds + 100)), 4);
                                    $probs[] = $prob;
                                }
                            }
                            else if ($row['type'] == 'Spread') {
                                $z = (floatval($data[0]) - floatval($data[0])) / 14;
                                $prob = exec("python3 normalcdf.py $z gt");
                                $probs[] = $prob;
                            }
                            else {
                                $z = (floatval($data[0]) - floatval($data[0])) / 14;
                                if ($row['bet_choice'] == 'Over') {
                                    $prob = exec("python3 normalcdf.py $z gt");
                                    $probs[] = $prob;
                                }
                                else {
                                    $prob = exec("python3 normalcdf.py $z lt");
                                    $probs[] = $prob;
                                }
                            }

                            $sql2 = "(select collect_date, $type, s_home_line, s_away_line from Bets where Bets.game_id=$game and collect_date > STR_TO_DATE('{$purchase_date}','%Y-%m-%d %H:%i:%s:%f'))";
                            $result2 = $connection->query($sql2);

                            while($row2 = $result2->fetch_assoc()){

                                $prob = exec("python3 normalcdf.py z gt");
                                echo "<h1>$prob</h1>";
                                
                                $cur_val = floatval($row2[$type]); 
                                $data[] = $cur_val;

                                $dt = $row2['collect_date'];

                                $date = explode(' ', $dt)[0];
                                $date_comp = explode('-', $date);
                                $month = $date_comp[1];
                                $day = $date_comp[2];

                                $time = explode(' ', $dt)[1];
                                $time_comp = explode(':', $time);
                                $hour = intval($time_comp[0]);

                                if ($hour >= 12) {
                                    $AMorPM = 'PM';
                                    if ($hour > 12) {
                                        $hour = $hour - 12;
                                    }
                                }
                                else {
                                    $AMorPM = 'AM';
                                    if ($hour == 0) {
                                        $hour = $hour + 12;
                                    }
                                }


                                $labels[] = $month . '/' . $day . ' ' . $hour . ':00 ' . $AMorPM;
                                
                                if ($cur_val > 0) {
                                    $implied_prob = round((100.0 / ($cur_val + 100.0)), 4);
                                    $implied_probs[] = $implied_prob;
                                }
                                else {
                                    $new_cur_val = -1 * $cur_val;
                                    $implied_prob = round(($new_cur_val / ($new_cur_val + 100.0)), 4);
                                    $implied_probs[] = $implied_prob;
                                }

                                if ($row['type'] == 'ML') {
                                    $probs[] = $implied_prob;
                                }
                                else if ($row['type'] == 'Spread') {
                                    $z = (floatval($data[0]) - floatval($row2[$type])) / 14;
                                    $prob = exec("python3 normalcdf.py $z gt");
                                    $probs[] = $prob;
                                }
                                else {
                                    $z = (floatval($data[0]) - floatval($row2[$type])) / 14;
                                    if ($row['bet_choice'] == 'Over') {
                                        $prob = exec("python3 normalcdf.py $z gt");
                                        $probs[] = $prob;
                                    }
                                    else {
                                        $prob = exec("python3 normalcdf.py $z lt");
                                        $probs[] = $prob;
                                    }
                                }
                            }

                            // default color is red
                            $r = 255;
                            $g = 0;
                            $color = 'red';

                            $min = min($probs);
                            $max = max($probs);

                            $original_value = ($payout * $probs[0]) - ($ticket_cost * (1 - $probs[0]));
                            $current_value = ($payout * end($probs)) - ($ticket_cost * (1 - end($probs)));
                            $value_change = $current_value - $original_value;
                            $suggested_list_price = intval($ticket_cost + $value_change);

                            if ($suggested_list_price < $ticket_cost) {
                                $list_price_str = "Optimal List Range: $" . $suggested_list_price . " or lower";
                                $small_text = "The value of your bet has decreased since you purchased it. Cut your losses now by selling it in this range.";
                            }
                            else if ($suggested_list_price > $ticket_cost) {
                                $list_price_str = "Optimal List Range: $" . $ticket_cost . " - $" . $suggested_list_price;
                                $small_text = "The value of your bet has increased since you purchased it. Guarantee a profit now by selling it in this range.";
                            } 
                            else {
                                $list_price_str = "Optimal List Price: $" . $suggested_list_price;
                                $small_text = "The value of your bet has not changed since you purchased it.";
                            }

                            $labels = json_encode($labels);

                            if ($probs[0] == end($probs)){
                                $color = 'grey';
                                $r = 0;
                                $g = 0;
                            } else if ($probs[0] > end($probs)) {
                                $r = 255;
                                $g = 0;
                                $color = 'red';
                            } else {
                                $r = 0;
                                $g = 255;
                                $color = 'green';
                            }

                            $data = json_encode($probs);

                            if (intval($odds) > 0) { $odds = "+". $odds; }
                            
                            if ($n_cards % 2 == 0){
                                if ($n_cards > 0){
                                    echo "</div> <div class=\"card-group\">";
                                }
                                else{
                                    echo "<div class=\"card-group\">";
                                }
                            } 
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

                            $hteam = $row['home_team'];
                            $vteam = $row['away_team'];
                            echo "<div class=\"card\">
                                    <div class=\"card-body\">
                                        <h5 class=\"card-title\">$team <span style=\"float:right;\"> $odds </span> 
                                        <br>TICKET COST: \$$ticket_cost
                                        <br>TO WIN: \$$toWin
                                        <br>TO COLLECT: \$$payout
                                        </h5>
                                        <div class=\"accordion\" id=\"accordionExample\">
                                            <div class=\"accordion-item\">
                                                <h2 class=\"accordion-header\" id=\"heading$n_cards\">
                                                    <button class=\"accordion-button collapsed\" type=\"button\" data-bs-toggle=\"collapse\" data-bs-target=\"#collapse$n_cards\" aria-expanded=\"false\" aria-controls=\"collapse$n_cards\">
                                                        Ticket Analytics
                                                    </button>
                                                </h2>
                                                <div id=\"collapse$n_cards\" class=\"accordion-collapse collapse\" aria-labelledby=\"heading$n_cards\" data-bs-parent=\"#accordionExample\">
                                                    <div class=\"accordion-body\">
                                                        <p> $vteam at $hteam
                                                        <br>$game_dayofweek, $game_month/$game_day at $game_hour:$game_min $game_AMorPM  
                                                        </p>
                                                            <div><canvas id=\"myChart$n_cards\"></canvas></div>
                                                            <script> 
                                                                const DATA$n_cards = {
                                                                    labels:$labels,
                                                                    datasets: [{
                                                                        label:\"$label\",
                                                                        data:$data,
                                                                        tension:0.1,
                                                                        borderColor: \"$color\",
                                                                        backgroundColor: \"rgba($r,$g,0,0.1)\"
                                                                    }]
                                                                };
                                                                var myChart = new Chart(\"myChart$n_cards\", {
                                                                    type: \"line\",
                                                                    data: DATA$n_cards,
                                                                    options: {
                                                                        scales: {
                                                                            yAxes: [{
                                                                                display: true,
                                                                                ticks: {                                                                            
                                                                                    suggestedMin: ($min * 0.9),
                                                                                    suggestedMax: ($max * 1.1),
                                                                                    maxTicksLimit: 7
                                                                                }
                                                                            }],
                                                                            xAxes: [{
                                                                                ticks:{
                                                                                    maxTicksLimit: 8
                                                                                }
                                                                            }]
                                                                        },
                                                                        aspectRatio : 1.5
                                                                    }
                                                                });
                                                            </script>                                                        
                                                            <h3>$list_price_str</h3>
                                                            <p>$small_text</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>";
                                if ($row['for_sale']){
                                    echo "<ul class=\"list-group\">
                                            <button class=\"btn\" id=".$row['contract_id']." onclick=\"showBet(this)\"><li class=\"list-group-item\">" . "$" . $row['sale_price'] . "</li></button>
                                        </ul>
                                    </div>
                                    </div>";
                                }
                                else{
                                    echo "<ul class=\"list-group\">
                                            <button class=\"btn\" id=".$row['contract_id']." onclick=\"showBet(this)\"><li class=\"list-group-item\">" . "List Your Bet Here"  . "</li></button>
                                        </ul>
                                    </div>
                                    </div>";
                                }
                            $n_cards = $n_cards + 1;              
                        }
                        mysqli_close($connection);
                    }
                    else{
                        echo '<h1> No bets placed. Place some <a href=nfl.php>here!</a></h1>';
                    }
                ?>
            </div>
        </div>
        <div class="fixed-bottom card bg-light" id="bet-preview" style="display: none">
        </div>
        <script
            src="https://code.jquery.com/jquery-3.6.1.js"
            integrity="sha256-3zlB5s2uwoUzrXK3BT7AX3FyvojsraNFxCc2vC/7pNI="
            crossorigin="anonymous">
            </script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.2/dist/js/bootstrap.min.js" integrity="sha384-IDwe1+LCz02ROU9k972gdyvl+AESN10+x7tBKgc9I5HFtuNz0wWnPclzo6p9vxnk" crossorigin="anonymous"></script>
        <script type="text/javascript" src="contract_transfer.js"></script>
        <script type="text/javascript" src="bet_lister.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.9.4/Chart.js"> </script>
    </body>
</html>
