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
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
        <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
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
        <!-- <div>   -->
        <!-- I dont know why I have to put something here
        <div class="container">
            <div class="row">
                <div class="col-lg"> what what 
                    Typing things becuase I like to type things a lot
                </div>
                <div class="col-sm">
                    "what"
                </div>
                <div class="col-sm">
                    "where"
                </div>
            </div>
        </div>  -->
        <!-- </div> -->
        
        <br />
        <br />
        <br />
        <div class="container-fluid">
            <div class="row">
               <!-- base layer for not leader board -->
                <div class="col-lg-10">
                  <div class = "row">
                    <div class="col-lg-2 text-center">
                    
                      <button type="button" class="btn btn-primary" style="pointer-events: none;">
                        Earnings
                        </br>
                        <?php
                          $sql = "select balance from Users where user_id = 77";
                          $result = $connection->query($sql);
                          $profit = 0;
                          // non outstanding profit
                          if (mysqli_num_rows($result)) {
                            while($row = $result->fetch_assoc()) {                            
                              $profit = $row['balance'] - 1000000;
                            }
                            $sql = "select outstanding_revenue from Liabilities order by date desc limit 1;";
                            $result = $connection->query($sql);
                            while($row = $result->fetch_assoc()) { 
                              $profit = $profit - $row['outstanding_revenue'];
                            }
                            echo "<tr style='color: #fff;'>
                                      <td>{$profit}</td>
                                    </tr><br />";
                          } else {
                            echo "N/A";
                          }
                        ?>
                      </button>
                    </div>
                    <div class="col-lg-3 text-center">
                      <button type="button" class="btn btn-success" style="pointer-events: none;">
                        Revenue
                        </br>
                        <?php
                          $sql = "select sum(amount) as rev from Contracts where previous_owner_id is null;";
                          $result = $connection->query($sql);
                          $row = $result->fetch_assoc();
                          $rev = $row["rev"];
                          echo "$rev";
                        ?>
                      </button>
                      </br>
                      </br>
                      
                    </div>
                    <div class="col-lg-2 text-center">
                      <button type="button" class="btn btn-danger" style="pointer-events: none;">
                        Number of Users
                        </br>
                        <?php
                          $sql = "select count(*) as cnt from Users;";
                          $result = $connection->query($sql);
                          $row = $result->fetch_assoc();
                          $num_users = $row["cnt"];
                          echo "$num_users";
                        ?>
                      </button>
                      </br>
                      </br>
                    </div>
                    <div class="col-lg-3 text-center">
                      <button type="button" class="btn btn-warning" style="pointer-events: none;">
                        Bets Traded
                        </br>
                        <?php
                          $sql = "select count(*) as cnt from Contracts where previous_owner_id is not null;";
                          $result = $connection->query($sql);
                          $row = $result->fetch_assoc();
                          $num_trades = $row["cnt"];
                          echo "$num_trades";
                        ?>
                      </button>
                      </br>
                      </br>
                    </div>
                    <div class="col-lg-2 text-center">
                      <button type="button" class="btn btn-info" style="pointer-events: none;">
                      Bets Purchased
                      </br>
                      <?php
                        $sql = "select count(*) as cnt from Contracts where previous_owner_id is null;";
                        $result = $connection->query($sql);
                        $row = $result->fetch_assoc();
                        $num_bought = $row["cnt"];
                        echo "$num_bought";
                      ?>
                      </button>
                      </br>
                      </br>
                    </div>
                  </div>
                  <div class = "row">
                    <div class="col-lg-6">
                    <h4><b>Liability Tracker</b><h4>
                      <?php
                        $labels = array();

                          $sql = "select date, outstanding_revenue, min_liability, max_liability from Liabilities;";
                          $result = $connection->query($sql);

                          while($row = $result->fetch_assoc()){
                            $dt = $row['date'];

                            $date = explode(' ', $dt)[0];
                            $date_comp = explode('-', $date);
                            $month = $date_comp[1];
                            $day = $date_comp[2];


                            $labels[] = $month . '/' . $day;

                            $outstanding_revenue[] = $row['outstanding_revenue'];
                            $min_liability[] = $row['min_liability'];
                            $max_liability[] = $row['max_liability'];
                          }
                          $labels = json_encode($labels);
                          $outstanding_revenue = json_encode($outstanding_revenue);
                          $min_liability = json_encode($min_liability);
                          $max_liability = json_encode($max_liability);

                      echo "<div class=\"chart-container\" style=\"position: relative; width:40vw\"><canvas id=\"myChart\" width=400px></canvas></div>
                      <script>
                        const DATA = {
                          labels: $labels,
                          datasets: [
                                {
                                label:\"Outstanding Revenue\",
                                data:$outstanding_revenue,
                                tension:0.1,
                                borderColor: \"green\",
                                backgroundColor: 'rgba(0,230,0,0.1)'
                                },
                                {
                                  label:\"Minimum Liability\",
                                  data:$min_liability,
                                  tension:0.1,
                                  borderColor: \"red\",
                                  backgroundColor: 'rgba(230,0,0,0.1)'
                                },
                                {
                                  label:\"Maximum Liability\",
                                  data:$max_liability,
                                  tension:0.1,
                                  borderColor: \"blue\",
                                  backgroundColor: 'rgba(0,0,230,0.1)'
                                }
                            ]
                        };
                        var myChart = new Chart(\"myChart\", {
                          type: \"line\",
                          data: DATA,
                          options: {
                            scales: {
                              yAxes: [{
                                display: true,
                                ticks: {                                                                            
                                  suggestedMin: (0),
                                  suggestedMax: (20000),
                                  maxTicksLimit: 10
                                }
                              }],
                              xAxes: [{
                                ticks:{
                                  maxTicksLimit: 50
                                }
                              }]
                            },
                            aspectRatio : 1.5
                          }
                        });
                        "
                        ?>
                      </script>
                    </div>
                    <div class="col-lg-6 text-center">
                      <h4><b>Potential Earnings on Trades</b></h4>
                      </br>
                      </br>
                    
                      <!-- <h1 id="result">Hello</h1>
                      <label for="customRange1" class="form-label">Example range</label> -->
                      
                      <?php
                        $sql = "select sum(purchased_price) as s from Contracts where previous_owner_id is not null";
                        $result = $connection->query($sql);
                        $row = $result->fetch_assoc();
                        $amount = $row["s"];
                        echo "
                        <div class='col-lg-12 text-center'>
                          <h1 id = 'result'>0</h1>
                          </br>
                          </br>
                        </div>
                        <div class='col-lg-12'>
                          <div class='slidecontainer'>
                            <input type='range' min='0' max='15' value='0' step = '.01' class='form-range' id='myRange'>
                            
                          </div>
                          <small id='result2'></small>
                        </div>
                          <meta name='amount' content='$amount'/>
                          <script>
                            var slider = document.getElementById('myRange');
                            var output = document.getElementById('result');
                            var output2 = document.getElementById('result2');
                            var amt = document.getElementsByName('amount')[0].content;
                            output.innerHTML = slider.value; // Display the default slider value

                            // Update the current slider value (each time you drag the slider handle)
                            slider.oninput = function() {
                              output.innerHTML = '$' + Math.round(this.value * amt/ 100); 
                              output2.innerHTML = this.value + '%';
                            }
                          </script>";
                        $sql = "select sum(purchased_price - amount) as loss from Contracts where previous_owner_id is not null";
                        $result = $connection->query($sql);
                        $row = $result->fetch_assoc();
                        $loss = $row["loss"];
                        echo "
                            </br>
                            </br>
                            <h5><b>Direct Loss from Trading: $$loss</b></h5>
                        ";
                        
                        $sql = "select count(CASE WHEN res < 0 then 1 ELSE NULL END) as neg, count(CASE WHEN res > 0 then 1 ELSE NULL END) as pos from (select p.game_id, (p.s - n.s) as res from 
                        ( select game_id, CASE WHEN s < 0 OR s IS NULL THEN 0 ELSE s END AS s from 
                        (Select g.game_id, sum(amount) as s from Contracts c, Games g where previous_owner_id is NULL and c.game_id = g.game_id group by g.game_id) x) 
                        p, (select game_id, CASE WHEN s < 0 OR s IS NULL THEN 0 ELSE s END AS s from
                        ( Select g.game_id, sum(loss) as s from Contracts c, Games g where c.game_id = g.game_id group by g.game_id) y) n 
                        where p.game_id = n.game_id) result;";
                        $result = $connection->query($sql);
                        $row = $result->fetch_assoc();
                        $losses = $row["neg"];
                        $gains =  $row["pos"];
                        echo"
                          </br>
                          </br>
                          <h5><b>Money Making Games: $gains</b></h5>
                          <h5><b>Money Losing Games: $losses</b></h5>
                        ";
                      ?>
                      </br>
                      </br>
                    </div>
                  </div>
                </div>
                <div class="col-lg-2 ">
                  <button type="button" class="btn btn-light text-start" style="pointer-events: none;">
                    <?php
                      $sql = "select name as User, balance as Balance from Users where user_id != 77 order by balance desc limit 15;";
                      $result = $connection->query($sql);
                      $dollar = '$';
                      $ranking = 1;
                      echo "<h6><b>Leaderboard</b></h6>";
                      if (mysqli_num_rows($result)) {
                        while($row = $result->fetch_assoc()) { 
                          echo "<tr style='color: #fff;'>
                                  <font size = '-1'>
                                    <td>{$ranking}</td>
                                    <td>{$row['User']}</td>
                                    <td>{$dollar}{$row['Balance']}</td>
                                  </font>
                                </tr><br />";
                          $ranking++;
                        }
                      }
                    ?>
                </div>
            </div>
            <br>
            <!-- WHere the money at -->
            <div class = "row">
              <div class = "col-lg-12 text-center">
                  <h4><b>Bet Distributions</b></h4>
                    </br>
                    </br>
              </div>
              </br>
            </div>
            <div class = "row">
              <div class = "col-lg-3 text-center">
                <b>Game</b>
              </div>
              <div class = "col-lg-3 text-center">
                <b>Moneyline</b>
              </div>
              <div class = "col-lg-3 text-center">
                <b>Spread</b>
              </div>
              <div class = "col-lg-3 text-center">
                  <b>Over Under</b>
              </div>
            </div>
            <?php
              $sql = "select distinct game_id from Games where completed = 0";
              $result = $connection->query($sql);
              $games = array();
              if (mysqli_num_rows($result)) {
                while($row = $result->fetch_assoc()) {
                  array_push($games, $row['game_id']);
                }
              }
              foreach ($games as $game_id){
                // get names
                $home_team = "Error";
                $away_team = "Error";
                $sql = "select home_team, away_team from Games where game_id = $game_id;";
                $result = $connection->query($sql);  
                if (mysqli_num_rows($result)) {
                  while($row = $result->fetch_assoc()) {
                    $home_team = $row["home_team"];
                    $away_team = $row["away_team"];
                  }
                }
                // Moneyline
                $MLhome_amt = 0;
                $sql = "select sum(amount) as s from Contracts where bet_choice = 'Home' and type = 'ML' and game_id = $game_id;";
                $result = $connection->query($sql);  
                if (mysqli_num_rows($result)) {
                  while($row = $result->fetch_assoc()) {
                    $MLhome_amt = $row["s"];
                  }
                }
                $MLaway_amt = 0;
                $sql = "select sum(amount) as s from Contracts where bet_choice = 'Away' and type = 'ML' and game_id = $game_id;";
                $result = $connection->query($sql);  
                if (mysqli_num_rows($result)) {
                  while($row = $result->fetch_assoc()) {
                    $MLaway_amt = $row["s"];
                  }
                }

                // Spread
                $Shome_amt = 0;
                $sql = "select sum(amount) as s from Contracts where bet_choice = 'Home' and type = 'Spread' and game_id = $game_id;";
                $result = $connection->query($sql);  
                if (mysqli_num_rows($result)) {
                  while($row = $result->fetch_assoc()) {
                    $Shome_amt = $row["s"];
                  }
                }
                $Saway_amt = 0;
                $sql = "select sum(amount) as s from Contracts where bet_choice = 'Away' and type = 'Spread' and game_id = $game_id;";
                $result = $connection->query($sql);  
                if (mysqli_num_rows($result)) {
                  while($row = $result->fetch_assoc()) {
                    $Saway_amt = $row["s"];
                  }
                }

                // Over Under
                $OUover_amt = 0;
                $sql = "select sum(amount) as s from Contracts where bet_choice = 'Over' and type = 'OU' and game_id = $game_id;";
                $result = $connection->query($sql);  
                if (mysqli_num_rows($result)) {
                  while($row = $result->fetch_assoc()) {
                    $OUover_amt = $row["s"];
                  }
                }
                $OUunder_amt = 0;
                $sql = "select sum(amount) as s from Contracts where bet_choice = 'Under' and type = 'OU' and game_id = $game_id;";
                $result = $connection->query($sql);  
                if (mysqli_num_rows($result)) {
                  while($row = $result->fetch_assoc()) {
                    $OUunder_amt = $row["s"];
                  }
                }

                echo "<div class = 'row'>
                      <div class = 'col-lg-3 text-center'>
                          $home_team 
                          </br>
                          vs 
                          </br>
                          $away_team
                      </div>
                      <div class = 'col-lg-3 text-center'>
                        <script src='https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.1.4/Chart.bundle.min.js'></script>
                        <script>
                            $(document).ready(function() {
                                var ctx = $('#ML$game_id');
                                var myLineChart = new Chart(ctx, {
                                    type: 'pie',
                                    data: {
                                        labels: ['Home', 'Away'],
                                        datasets: [{
                                            data: [$MLhome_amt, $MLaway_amt],
                                            backgroundColor: [ 'rgba(100, 255, 0, 1)', 'rgba(255, 0, 0, 1)']
                                        }]
                                    },
                                });
                            });
                          </script> 
                          <div class='chartjs-size-monitor' style='position: absolute; left: 0px; top: 0px; right: 0px; bottom: 0px; overflow: hidden; pointer-events: none; visibility: hidden; z-index: -1;'>
                            <div class='chartjs-size-monitor-expand' style='position:absolute;left:0;top:0;right:0;bottom:0;overflow:hidden;pointer-events:none;visibility:hidden;z-index:-1;'>
                                <div style='position:absolute;width:1000000px;height:1000000px;left:0;top:0'></div>
                            </div>
                            <div class='chartjs-size-monitor-shrink' style='position:absolute;left:0;top:0;right:0;bottom:0;overflow:hidden;pointer-events:none;visibility:hidden;z-index:-1;''>
                                <div style='position:absolute;width:200%;height:200%;left:0; top:0'></div>
                            </div>
                          </div> <canvas id='ML$game_id' width='299' height='200' class='chartjs-render-monitor' style='display: block; width: 299px; height: 200px;'></canvas>       
                          </br>
                          </br>
                        </div>
                      <div class = 'col-lg-3 text-center'>
                        <script src='https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.1.4/Chart.bundle.min.js'></script>
                        <script>
                          $(document).ready(function() {
                              var ctx = $('#S$game_id');
                              var myLineChart = new Chart(ctx, {
                                  type: 'pie',
                                  data: {
                                      labels: ['Home', 'Away'],
                                      datasets: [{
                                          data: [$Shome_amt, $Saway_amt],
                                          backgroundColor: [ 'rgba(100, 255, 0, 1)', 'rgba(255, 0, 0, 1)']
                                      }]
                                  },
                              });
                          });
                        </script> 
                        <div class='chartjs-size-monitor' style='position: absolute; left: 0px; top: 0px; right: 0px; bottom: 0px; overflow: hidden; pointer-events: none; visibility: hidden; z-index: -1;'>
                          <div class='chartjs-size-monitor-expand' style='position:absolute;left:0;top:0;right:0;bottom:0;overflow:hidden;pointer-events:none;visibility:hidden;z-index:-1;'>
                              <div style='position:absolute;width:1000000px;height:1000000px;left:0;top:0'></div>
                          </div>
                          <div class='chartjs-size-monitor-shrink' style='position:absolute;left:0;top:0;right:0;bottom:0;overflow:hidden;pointer-events:none;visibility:hidden;z-index:-1;''>
                              <div style='position:absolute;width:200%;height:200%;left:0; top:0'></div>
                          </div>
                        </div> <canvas id='S$game_id' width='299' height='200' class='chartjs-render-monitor' style='display: block; width: 299px; height: 200px;'></canvas>       
                        </br>
                        </br>
                      </div>
                      <div class = 'col-lg-3 text-center'>
                        <script src='https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.1.4/Chart.bundle.min.js'></script>
                        <script>
                          $(document).ready(function() {
                              var ctx = $('#OU$game_id');
                              var myLineChart = new Chart(ctx, {
                                  type: 'pie',
                                  data: {
                                      labels: ['Over', 'Under'],
                                      datasets: [{
                                          data: [$OUover_amt, $OUunder_amt],
                                          backgroundColor: [ 'rgba(100, 255, 0, 1)', 'rgba(255, 0, 0, 1)']
                                      }]
                                  },
                              });
                          });
                        </script> 
                        <div class='chartjs-size-monitor' style='position: absolute; left: 0px; top: 0px; right: 0px; bottom: 0px; overflow: hidden; pointer-events: none; visibility: hidden; z-index: -1;'>
                          <div class='chartjs-size-monitor-expand' style='position:absolute;left:0;top:0;right:0;bottom:0;overflow:hidden;pointer-events:none;visibility:hidden;z-index:-1;'>
                              <div style='position:absolute;width:1000000px;height:1000000px;left:0;top:0'></div>
                          </div>
                          <div class='chartjs-size-monitor-shrink' style='position:absolute;left:0;top:0;right:0;bottom:0;overflow:hidden;pointer-events:none;visibility:hidden;z-index:-1;''>
                              <div style='position:absolute;width:200%;height:200%;left:0; top:0'></div>
                          </div>
                        </div> <canvas id='OU$game_id' width='299' height='200' class='chartjs-render-monitor' style='display: block; width: 299px; height: 200px;'></canvas>       
                        </br>
                        </br>
                      </div>
                      
                    </div>";
              }
              // $sql = "select type, bet_choice, game_id, sum(amount) as s from 

              // ( select g.game_id, home_team, away_team, amount, type, bet_choice from 
              // (select game_id, home_team, away_team from Games where completed = 0) as g, (select amount, type, bet_choice, game_id from Contracts where paidout = 0) as c
              
              // where g.game_id = c.game_id) as d group by game_id, type, bet_choice order by game_id, type, bet_choice;";
              // $result = $connection->query($sql);
              // if (mysqli_num_rows($result)) {
              //   while($row = $result->fetch_assoc()) { 
              //     // $game = 
              //     // $MLH = 
              //     // $
              //     echo "<div class = 'row'>
              //             <div class = 'col-lg-3 text-center'>
              //                 Game
              //             </div>
              //             <div class = 'col-lg-3 text-center'>
              //                 Moneyline
              //             </div>
              //             <div class = 'col-lg-3 text-center'>
              //                 Spread
              //             </div>
              //             <div class = 'col-lg-3 text-center'>
              //                 Over Under
              //             </div>
              //           </div>";
              //   }
              // }
              
            ?>
        </div>
        <!-- <script src='https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.1.4/Chart.bundle.min.js'></script>
        <script>
            $(document).ready(function() {
                var ctx = $('#chart-line');
                var myLineChart = new Chart(ctx, {
                    type: 'pie',
                    data: {
                        labels: ['Spring', 'Summer', 'Fall', 'Winter'],
                        datasets: [{
                            data: [1200, 1700, 800, 200],
                            backgroundColor: ['rgba(255, 0, 0, 0.5)', 'rgba(100, 255, 0, 0.5)', 'rgba(200, 50, 255, 0.5)', 'rgba(0, 100, 255, 0.5)']
                        }]
                    },
                    options: {
                        title: {
                            display: true,
                            text: 'Weather'
                        }
                    }
                });
            });
        </script> -->
        <!-- <div class="page-content page-container" id="page-content">
            <div class="padding">
                <div class="row">
                    <div class="container-fluid d-flex justify-content-center">
                         -->
                            <!-- <div class="card"> -->
                                <!-- <div class="card-header">Pie chart</div> -->
                                <!-- <div class="card-body" style="height: 420px"> -->
                                    <!-- <div class="chartjs-size-monitor" style="position: absolute; left: 0px; top: 0px; right: 0px; bottom: 0px; overflow: hidden; pointer-events: none; visibility: hidden; z-index: -1;">
                                        <div class="chartjs-size-monitor-expand" style="position:absolute;left:0;top:0;right:0;bottom:0;overflow:hidden;pointer-events:none;visibility:hidden;z-index:-1;">
                                            <div style="position:absolute;width:1000000px;height:1000000px;left:0;top:0"></div>
                                        </div>
                                        <div class="chartjs-size-monitor-shrink" style="position:absolute;left:0;top:0;right:0;bottom:0;overflow:hidden;pointer-events:none;visibility:hidden;z-index:-1;">
                                            <div style="position:absolute;width:200%;height:200%;left:0; top:0"></div>
                                        </div>
                                    </div> <canvas id="chart-line" width="299" height="200" class="chartjs-render-monitor" style="display: block; width: 299px; height: 200px;"></canvas> -->
                                <!-- </div> -->
                            <!-- </div> -->
                        
                    <!-- </div>
                </div>
            </div>
        </div>  -->
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

<!-- 
select count(CASE WHEN res > 0 then 1 ELSE NULL END) as neg, count(CASE WHEN res > 0 then 1 ELSE NULL END) as pos from (select p.game_id, (p.s - n.s) as res from 
( select game_id, CASE WHEN s < 0 OR s IS NULL THEN 0 ELSE s END AS s from 
(Select g.game_id, sum(amount) as s from Contracts c, Games g where previous_owner_id is NULL and c.game_id = g.game_id group by g.game_id) x) 
p, (select game_id, CASE WHEN s < 0 OR s IS NULL THEN 0 ELSE s END AS s from
( Select g.game_id, sum(loss) as s from Contracts c, Games g where c.game_id = g.game_id group by g.game_id) y) n 
where p.game_id = n.game_id) result;


select sum(res) from (select p.game_id, (p.sx - n.sx) as res from 
( select game_id, CASE WHEN s IS NULL THEN 0 ELSE s END AS sx from 
(Select g.game_id, sum(amount) as s from Contracts c, Games g where previous_owner_id is NULL and c.game_id = g.game_id  and g.completed = 1 group by g.game_id) x) 
p, (select game_id, CASE WHEN s IS NULL THEN 0 ELSE s END AS sx from
( Select g.game_id, sum(loss) as s from Contracts c, Games g where c.game_id = g.game_id and g.completed = 1 group by g.game_id) y) n 
where p.game_id = n.game_id) result;

select sum(res) from (select p.game_id, (p.s - n.s) as res from 
(Select g.game_id, sum(amount) as s from Contracts c, Games g where previous_owner_id is NULL and c.game_id = g.game_id  and g.completed = 1 group by g.game_id)
p, 
( Select g.game_id, sum(loss) as s from Contracts c, Games g where c.game_id = g.game_id and g.completed = 1 group by g.game_id)  n 
where p.game_id = n.game_id) result;


select losses, gains, gains - losses as profit from
(select sum(loss) as losses from Contracts where original_purchase_date < "2022-11-25 23:59:02" and original_purchase_date > "2022-11-9 23:59:02") loss
,
(select sum(amount) as gains from Contracts, Games  where Games.game_id = Contracts.game_id and Games.completed = 1 and previous_owner_id is NULL and original_purchase_date < "2022-11-25 23:59:02" and original_purchase_date > "2022-11-9 23:59:02")  prof ;
 -->
