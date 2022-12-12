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
                    <a class="nav-link active" aria-current="page" href="marketplace.php">Bets For Sale</a>
                  </li>
                  <li class="nav-item">
                    <a class="nav-link active" aria-current="page" href="my_bets.php">My Bets</a>
                  </li>
                  <li class="nav-item">
                    <a class="nav-link active" aria-current="page" href="my_account.php">My Account</a>
                  </li>
                  <?php
                   if ($_SESSION["user_id"] == 77 or $_SESSION["user_id"] == 134) {
                    echo "<li class='nav-item'>
                            <a class='nav-link active' aria-current='page' href='dashboard.php'>Dashboard</a>
                          </li>";
                   }
                  ?>
                </ul>
                <h5><?= htmlspecialchars($balance_str) ?></h5>
              </div>
            </div>
          </nav>
        <div class="bg-image">
            <div class="cards">
                <?php
                    function data2prob($dp, $t){
                    
                        if ($t == 'ML') {
                            $z = (1.0 + floatval($dp)) / 14;
                            $prob = exec("python3 normalcdf.py $z gt");
                        }
                        else if ($t == 'Spread') {
                            $z = (floatval($dp) - floatval($dp)) / 14;
                            $prob = exec("python3 normalcdf.py $z gt");
                        }
                        else {
                            $z = (floatval($dp) - floatval($dp)) / 14;
                            // finds over always. need to subtract 1 from it to find the under prob
                            $prob = exec("python3 normalcdf.py $z gt");
                        }
                        return $prob;
                    }

                    $sql = "(select game_date, purchased_price, purchase_date, paidout, contract_id, Games.game_id, for_sale, sale_price, bet_choice, type, amount, home_team, away_team, ou_total, ou_under_odds, ou_over_odds, ml_home_odds, ml_away_odds, s_home_odds, s_away_odds, s_home_line, s_away_line from Contracts, Games where Contracts.game_id=Games.game_id and paidout=0 and user_id=" . $_SESSION["user_id"] .  " ORDER BY game_date, home_team" . ")";
                    $result = $connection->query($sql);
                    $n_cards = 0;
                    $data = array(array());
                    $labels = array(array());
                    $probs = array(array());
                    $type_array = array();
                    $payout_array = array();
                    $cst_array = array();

                    date_default_timezone_set('EST');
                    $cur_time = strtotime(date("Y-m-d H:i:s", time()));

                    if (!empty($result)){
                        while($row = $result->fetch_assoc()) {
                            
                            $payout = 0;

                            // update the arrays for later
                            $labels[$n_cards][] = "Original Purchase";
                            $type_array[] = $row['type'];

                            // conditionals to determine bet choice/type and gather any useful data
                            // determines what card should display
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
                                $data[$n_cards][] = $spread;
                                $team = $team . " ML";
                            }
                            else if ($row['type'] == 'Spread'){
                                $line = '0';
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
                                $data[$n_cards][] = $line;
                                if (intval($line) > 0) { $line = "+". $line; }
                                $team = $team . " " . $line;
                            }
                            else{
                                if ($row['bet_choice'] == 'Over'){
                                    $odds = $row['ou_over_odds'];
                                }
                                else{
                                    $odds = $row['ou_under_odds'];
                                }
                                $type_array[$n_cards] = $type_array[$n_cards] . $row['bet_choice'];
                                $type = 'ou_total';
                                $team1 = explode(' ', $row['away_team']);
                                $team2 = explode(' ', $row['home_team']);
                                $team = end($team1) . " at " . end($team2) . ": " . $row['bet_choice'] . " " . $row['ou_total'];
                                $data[$n_cards][] = $row['ou_total'];
                            }

                            // calculate the payout via the original amt the original contract was purchased for
                            $amount = $row['amount'];
                            if ($odds < 0){
                                $payout = round((100 / (-1 *$odds)) * $amount);
                            }
                            else{
                                $payout = round(($odds / 100) * $amount);
                            }

                            // extract total to win and ticket cost for display purposes
                            $toWin = $payout;
                            $ticket_cost = $row['purchased_price'];

                            // total payout
                            $payout = $payout + $ticket_cost;

                            $payout_array[] = $payout;
                            $cst_array[] = $amount;

                            $game = $row['game_id'];
                            $purchase_date = $row['purchase_date'];

                            $end_book_sales_time = strtotime(date('Y-m-d H:i:s', strtotime($row['game_date'])));
                            if ($end_book_sales_time < $cur_time) {
                                $explanation = "NOTE: Since the game has started, this bet is no longer available via the \"Bet NFL\" page. You may be able to list this bet for more than the optimal range.";
                            } else {$explanation = "";}

                            // get date information for game
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

                            // home and visiting team names for add. info tab
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

                            if ($ticket_cost != $amount){
                                if ($toWin > $ticket_cost){
                                    $odds = "+" . round((100 * $toWin) / $ticket_cost);
                                }
                                else{
                                    $odds = round((-100 * $ticket_cost) / $toWin);
                                }                                
                            }



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
                                                        <div id='list_price$n_cards'></div>
                                                        <p>$explanation</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>";

                            $end_list_sales_time = strtotime(date('Y-m-d H:i:s', strtotime($row['game_date'])));
                            $end_list_sales_time = strtotime(date('Y-m-d H:i:s', strtotime('+2 hours', $end_list_sales_time)));
                            if ($end_list_sales_time < $cur_time && !$row['for_sale']) {
                                echo "<ul class=\"list-group\">
                                        <button class=\"btn\" id=".$row['contract_id']." onclick=\"\"><li class=\"list-group-item\">It is too late to list this bet on the marketplace.</li></button>
                                    </ul>
                                </div>
                                </div>";
                            } 
                            else if ($end_list_sales_time < $cur_time && $row['for_sale']){
                                echo "<ul class=\"list-group\">
                                        <button class=\"btn\" id=".$row['contract_id']." onclick=\"\"><li class=\"list-group-item\">This bet did not sell on the marketplace.</li></button>
                                    </ul>
                                </div>
                                </div>";
                            }
                            else if ($end_list_sales_time > $cur_time && $row['for_sale']) {
                                echo "<ul class=\"list-group\">
                                        <button class=\"btn\" id=".$row['contract_id']." onclick=\"showBet(this)\"><li class=\"list-group-item\">" . "$" . $row['sale_price']  . "</li></button>
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

                            $sql2 = "(select collect_date, $type, s_home_line, s_away_line from Bets where Bets.game_id=$game and collect_date > STR_TO_DATE('{$purchase_date}','%Y-%m-%d %H:%i:%s:%f'))";
                            $result2 = $connection->query($sql2);

                            while($row2 = $result2->fetch_assoc()){


                                if ($row['type'] == 'ML') {
                                    if ($row['bet_choice'] == 'Home') {
                                        $data[$n_cards][] = $row2["s_home_line"];
                                    }
                                    else {
                                        $data[$n_cards][] = $row2["s_away_line"];
                                    }
                                }
                                else {
                                    $data[$n_cards][] = $row2[$type];
                                }

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
                                
                                $labels[$n_cards][] = $month . '/' . $day . ' ' . $hour . ':00 ' . $AMorPM;

                            }

                            $n_cards = $n_cards + 1;              
                        }
                        mysqli_close($connection);
                    }
                    else{
                        echo '<h1> No bets placed. Place some <a href=nfl.php>here!</a></h1>';
                    }
                    // CREATE IMPLIED PROBABILITIES HERE
                    for ($x=0; $x<$n_cards; $x++){
                        // build the of probs array
                        for ($i=0; $i < count($data[$x]); $i++){

                            $prob = data2prob($data[$x][$i], $type_array[$x]);

                            if (strpos($type_array[$x], 'Under')){
                                $prob = 1 - $prob;
                            }
                            $probs[$x][] = $prob;
                        }
                    }
                ?>
            </div>
        </div>
        <div class="fixed-bottom card bg-light" id="bet-preview" style="display: none">
        </div>
        <script type="text/javascript">

            var pArray = <?php echo json_encode($probs, JSON_NUMERIC_CHECK); ?>;
            var n = <?php echo $n_cards; ?>;
            var labels = <?php echo json_encode($labels); ?>;
            var payout_arr = <?php echo json_encode($payout_array, JSON_NUMERIC_CHECK); ?>;
            var cost_arr = <?php echo json_encode($cst_array, JSON_NUMERIC_CHECK); ?>;

            for(let i = 0; i < n; i++){

                let data = pArray[i];
                let l = labels[i];
                let c_id = 'myChart' + i.toString();
                let chart = document.getElementById(c_id);
                min = Math.min(...data);
                max = Math.max(...data);
                
                // color stuff
                let r = 255;
                let g = 0;
                let color = 'red';

                if (data[0] == data[data.length - 1]){
                    color = 'grey';
                    r = 0;
                    g = 0;
                }
                else if (data[data.length - 1] > data[0]){
                    color = 'green';
                    r = 0;
                    g = 255;
                }

                const DATA = {
                    labels: l,
                    datasets: [{
                        label: "Prob. of Bet Success",
                        data: data,
                        tension:0.1,
                        borderColor: color,
                        backgroundColor: 'rgba(' + r.toString() +','+ g.toString() + ',0,0.1)',
                    }]
                };
                
                var myChart = new Chart(c_id, 
                {
                    type: "line",
                    data: DATA,
                    options: {
                        scales: {
                            yAxes: [{
                                display: true,
                                ticks: {                                                                            
                                    suggestedMin: (min * 0.9),
                                    suggestedMax: (max * 1.1),
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

                let l_id = 'list_price' + i.toString();
                let listprice = document.getElementById(l_id);


                let og_val = (payout_arr[i] * data[0]) - (cost_arr[i] * (1 - data[0]));
                let curr_val =(payout_arr[i] * data[data.length-1]) - (cost_arr[i] * (1 - data[data.length-1]));
                let value_change = curr_val - og_val;
                let suggested_list_price = cost_arr[i] + value_change;

                if (suggested_list_price < cost_arr[i]) {
                    var list_price_str = "Optimal List Range: $" + Math.round(suggested_list_price).toString() + " or lower";
                    var small_text = "The value of your bet has decreased since you purchased it. Cut your losses now by selling it in this range.";
                }
                else if (suggested_list_price > cost_arr[i]) {
                    var list_price_str = "Optimal List Range: $" + cost_arr[i].toString() + " - $" + Math.round(suggested_list_price.toString());
                    var small_text = "The value of your bet has increased since your purchased it. Guarantee a profit now by selling it in this range.";
                } 
                else {
                    var list_price_str = "Optimal List Price: $"  + Math.round(suggested_list_price.toString());
                    var small_text = "The value of your bet has not changed since your purchased it.";
                }

                listprice.innerHTML = "<h3>" + list_price_str + "</h3><p>" + small_text + "</p>";
            };
        </script>
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
