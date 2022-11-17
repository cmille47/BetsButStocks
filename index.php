<?php

session_start();

if (isset($_SESSION["user_id"])) {
    
    $servername = "localhost";
    $username = "hplante";
    $password = "pwpwpwpw";
    $database = "hplante";

    $connection = new mysqli($servername, $username, $password, $database);
    
    // need to fix this line
    $sql = "SELECT * FROM Users WHERE user_id = {$_SESSION["user_id"]}";
            
    $result = $connection->query($sql);
    
    $user = $result->fetch_assoc();
}
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-Zenh87qX5JnK2Jl0vWa8Ck2rdkQ2Bzep5IDxbcnCeuOxjzrPF/et3URy9Bv1WTRi" crossorigin="anonymous">
        <link rel="stylesheet" href="index.css" type="text/css">
        <title>Home</title>
    </head>
    <body>

    <div class="bg-image">

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
    
    <?php if (isset($user)): ?>
        <div class="container">
            <h1 style="font-size:300%;"><br>Hello, <?= htmlspecialchars($user["name"]) ?>. Welcome to BetsButStocks!</h1>
            <h2>Get started on the first ever sports betting marketplace:</h2>
            <h5><br><ul>
              <li>Place a bet on the <a href="nfl.php">Bet NFL</a> tab</li>
              <li>Check out user listed bets for better odds than the book on the <a href="listings.php">Bets For Sale</a> tab</li>
              <li>List one of your existing bets on the <a href="listings.php">Bets For Sale</a> tab</li>
              <li>Follow your current bets on the <a href="show_user_bets.php">My Bets</a> tab</li>
              <li>Keep track of your current balance and account information on the <a href="my_account.php">My Account</a> tab</li>
            </ul></h5>
            <br><br><br><br>
            <h3> 
              By creating your account with BetsButStocks you are given a balance of $1000.<br>
              You may place bets of any integer amount <i>as long as you have the funds</i>.<br>
              The person who makes the most money at the end of the trial period will win a prize!
            </h3>
            <button type="button" class="btn btn-light"><a href="logout.php"><h5>Log out</h5></a></button><br>
        
            <h2><br><br>Leaderboard</h2>
            <table class="table">
              <thead>
                <tr>
                  <th style="color: #fff;">Rank</th>
                  <th style="color: #fff;">User</th>
                  <th style="color: #fff;">Balance</th>
              </thead>
              <tbody>
                <?php
                  $sql = "select name as User, balance as Balance from Users where user_id != 77 order by balance desc;";
                  $result = $connection->query($sql);
                  $dollar = '$';
                  $ranking = 1;
                  if (mysqli_num_rows($result)) {
                    while($row = $result->fetch_assoc()) { 
                      echo "<tr style='color: #fff;'>
                              <td>{$ranking}</td>
                              <td>{$row['User']}</td>
                              <td>{$dollar}{$row['Balance']}</td>
                            </tr>";
                      $ranking++;
                    }
                  }
                ?>
              </tbody>
            </table>
        


        </div>
        
    <?php else: ?>
        <div class="container">
            <h1 style="font-size: 500%";><br><br>Welcome to BetsButStocks</h1>
            <button type="button" class="btn btn-light"><a href="create_account.html"><h5>Create an Account</h5></a></button>
            <button type="button" class="btn btn-light"><a href="login.php"><h5>Log in</h5></a></button>
        </div>
        
    <?php endif; ?>
  
  </div>
    
</body>
</html>
