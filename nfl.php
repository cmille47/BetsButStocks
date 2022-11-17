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
        <link rel="stylesheet" href="nfl.css" type="text/css">
        <title>NFL Bets</title>
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
              </div>
            </div>
          </nav>

  <div class="bg-image">
    <div class="container" style="padding-bottom:175px;">
        <h1 style="color: #fff; padding-top:5rem">NFL Bets</h1>
        <table class="table">
            <thead>
                <tr>
                    <th style="color: #fff;">Teams</th>
                    <th style="color: #fff;">Moneyline</th>
                    <th style="color: #fff;">Spread</th>
                    <th style="color: #fff;">Over/Under</th>
            </thead>
            <tbody>
              <?php
                    // read from database table
                    // $sql = "select distinct home_team, away_team, ml_home_odds, ml_away_odds, s_home_line, s_away_line, s_home_odds, s_away_odds, ou_total, ou_over_odds, ou_under_odds from (select game_id, ml_home_odds, ml_away_odds, s_home_odds, s_away_odds, s_home_line, s_away_line, ou_total, ou_over_odds, ou_under_odds from Bets where collect_date >= all(select collect_date from Bets)) s, Games where Games.game_id = s.game_id and Games.game_date > NOW() and completed = 0 and sport like 'nfl';"; 
                    $sql = "Select distinct home_team, away_team, ml_home_odds, ml_away_odds, s_home_line, s_away_line, s_home_odds, s_away_odds, ou_total, ou_over_odds, ou_under_odds from
                        (Select max(collect_date) as mdate, f.game_id from (Select Bets.game_id from 
                        (Select game_id from Games where completed = 0 and current_timestamp < game_date) g, Bets
                        Where Bets.game_id = g.game_id) f, Bets where Bets.game_id = f.game_id group by f.game_id) ff, Bets, Games where Bets.game_id = ff.game_id and Bets.game_id = Games.game_id and mdate = collect_date;";
                    
                    $result = $connection->query($sql);

                    //read data from each row
                    while($row = $result->fetch_assoc()) {
                        $team_id = "". str_replace(" ", "-", $row['home_team']) . "%" . str_replace(" ", "-", $row['away_team']);
                        if (intval($row['ml_home_odds']) > 0) { $row['ml_home_odds'] = "+". $row['ml_home_odds']; }
                        if (intval($row['ml_away_odds']) > 0) { $row['ml_away_odds'] = "+". $row['ml_away_odds']; }
                        if (intval($row['s_home_odds']) > 0) { $row['s_home_odds'] = "+". $row['s_home_odds']; }
                        if (intval($row['s_away_odds']) > 0) { $row['s_away_odds'] = "+". $row['s_away_odds']; }
                        if (intval($row['s_home_line']) > 0) { $row['s_home_line'] = "+". $row['s_home_line']; }
                        if (intval($row['s_away_line']) > 0) { $row['s_away_line'] = "+". $row['s_away_line']; }
                        if (intval($row['ou_over_odds']) > 0) { $row['ou_over_odds'] = "+". $row['ou_over_odds']; }
                        if (intval($row['ou_under_odds']) > 0) { $row['ou_under_odds'] = "+". $row['ou_under_odds']; }
                        echo "<tr>
                            <td>
                                <ul class=\"list-group\">
                                <li class=\"list-group-item\" style=\"margin-top: 7px; text-align: center\" id=".$team_id.">". "<b>" . $row['home_team'] . "</b>" ."</li>
                                <li class=\"list-group-item\" style=\"margin-top: 7px; text-align: center\" id=".$team_id.">". "<b>" . $row['away_team'] . "</b>" ."</li>
                                </ul>
                            </td>
                            <td>
                                <ul class=\"list-group\">
                                    <button class=\"btn\" id=".$team_id." onclick=\"homeML(this)\"><li class=\"list-group-item\">"." (" . $row['ml_home_odds'] . ")" . "</li></button>
                                    <button class=\"btn\" id=".$team_id." onclick=\"awayML(this)\"><li class=\"list-group-item\">"." (" . $row['ml_away_odds'] . ")" . "</li></button>
                                </ul>
                            </td>
                            <td>
                                <ul class=\"list-group\">
                                <button class=\"btn\" id=".$team_id." onclick=\"homeSpread(this)\"><li class=\"list-group-item\">".$row['s_home_line'] . " (" . $row['s_home_odds'] . ")" . "</li></button>
                                <button class=\"btn\" id=".$team_id." onclick=\"awaySpread(this)\"><li class=\"list-group-item\">".$row['s_away_line'] . " (" . $row['s_away_odds'] . ")" . "</li></button>
                                </ul>
                            </td>
                            <td>
                                <ul class=\"list-group\">
                                <button class=\"btn\" id=".$team_id." onclick=\"Over(this)\"><li class=\"list-group-item\">"."Over ". $row['ou_total'] . " (" . $row['ou_over_odds'] . ")" . "</li></button>
                                <button class=\"btn\" id=".$team_id." onclick=\"Under(this)\"><li class=\"list-group-item\">"."Under ". $row['ou_total'] . " (" . $row['ou_under_odds'] . ")" . "</li></button>
                                </ul>
                            </td>
                        </tr>";
                    }

                ?>
            </tbody>
        </table>
      </div>
      <div class="fixed-bottom card bg-light" id="bet-preview" style="display: none; margin:5px;">
      </div>
  </div>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-OERcA2EqjJCMA+/3y+gxIOqMEjwtxJY7qPCqsdltbNJuaOe923+mo//f6V8Qbsw3" crossorigin="anonymous"></script>
        <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js" integrity="sha384-oBqDVmMz9ATKxIep9tiCxS/Z9fNfEXiDAYTujMAeBAsjFuCZSmKbSSUnQlmh/jp3" crossorigin="anonymous"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.2/dist/js/bootstrap.min.js" integrity="sha384-IDwe1+LCz02ROU9k972gdyvl+AESN10+x7tBKgc9I5HFtuNz0wWnPclzo6p9vxnk" crossorigin="anonymous"></script>
        <script type="text/javascript" src="bet_placer.js"></script>
      </body>
</html>
