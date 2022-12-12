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
        <title>Book Dashboard</title>
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
                    
                      <button type="button" class="btn btn-primary">
                        Total profit
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
                    <div class="col-lg-2 text-center">
                      <button type="button" class="btn btn-success btn-static" style="pointer-events: none;">
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
                    <div class="col-lg-2 text-center">
                      <button type="button" class="btn btn-warning">
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
                      <button type="button" class="btn btn-info">
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



                      <!-- Pull from Here --> 
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

                      echo "<div class=\"chart-container\" style=\"position: relative; width:35vw\"><canvas id=\"myChart\" width=400px></canvas></div>
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
                    <!-- To Here --> 




                    <div class="col-lg-4">
                      Potential profit on transactions
                      <!-- select sum(purchased_price - amount) as LossFromTrades from Contracts where previous_owner_id is not null; -->

                    </div>
                  </div>
                </div>
                <!-- leaderboard -->
                <div class="col-lg-2">
                  <h4><b>Leaderboard</b><h4>
                  <button type="button" class="btn btn-light">
                    <?php
                      $sql = "select name as User, balance as Balance from Users where user_id != 77 order by balance desc limit 10;";
                      $result = $connection->query($sql);
                      $dollar = '$';
                      $ranking = 1;
                      if (mysqli_num_rows($result)) {
                        while($row = $result->fetch_assoc()) { 
                          echo "<tr style='color: #fff;'>
                                  <td>{$ranking}</td>
                                  <td>{$row['User']}</td>
                                  <td>{$dollar}{$row['Balance']}</td>
                                </tr><br />";
                          $ranking++;
                        }
                      }
                    ?>
                  </button>
                </div>
            </div>
            <!-- WHere the money at -->
            <div class = "row">
              <div class = "col-lg-12">
                  Where Is the Money?
              </div>
            </div>
            <div class = "row">
              <div class = "col-lg-3 text-center">
                  Game
              </div>
              <div class = "col-lg-3 text-center">
                  Moneyline
              </div>
              <div class = "col-lg-3 text-center">
                  Spread
              </div>
              <div class = "col-lg-3 text-center">
                  Over Under
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
                    
                  }
                }
                // Moneyline

                // Spread

                // Over Under
              }
              $sql = "select type, bet_choice, game_id, sum(amount) as s from 

              ( select g.game_id, home_team, away_team, amount, type, bet_choice from 
              (select game_id, home_team, away_team from Games where completed = 0) as g, (select amount, type, bet_choice, game_id from Contracts where paidout = 0) as c
              
              where g.game_id = c.game_id) as d group by game_id, type, bet_choice order by game_id, type, bet_choice;";
              $result = $connection->query($sql);
              if (mysqli_num_rows($result)) {
                while($row = $result->fetch_assoc()) { 
                  // $game = 
                  // $MLH = 
                  // $
                  echo "<div class = 'row'>
                          <div class = 'col-lg-3 text-center'>
                              Game
                          </div>
                          <div class = 'col-lg-3 text-center'>
                              Moneyline
                          </div>
                          <div class = 'col-lg-3 text-center'>
                              Spread
                          </div>
                          <div class = 'col-lg-3 text-center'>
                              Over Under
                          </div>
                        </div>";
                }
              }
              
            ?>
        </div>
        <div class="fixed-bottom card bg-light" id="bet-preview" style="display: none">
        </div>
        <script
            src="https://code.jquery.com/jquery-3.6.1.js"
            integrity="sha256-3zlB5s2uwoUzrXK3BT7AX3FyvojsraNFxCc2vC/7pNI="
            crossorigin="anonymous">
            </script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.2/dist/js/bootstrap.min.js" integrity="sha384-IDwe1+LCz02ROU9k972gdyvl+AESN10+x7tBKgc9I5HFtuNz0wWnPclzo6p9vxnk" crossorigin="anonymous"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.9.4/Chart.js"> </script>
    </body>
</html>
