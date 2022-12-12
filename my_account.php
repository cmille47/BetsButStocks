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
    $balance = $user['balance'];
    $balance_str = "Current Balance: $" . $balance;
} else {
  $balance_str = "";
}
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-Zenh87qX5JnK2Jl0vWa8Ck2rdkQ2Bzep5IDxbcnCeuOxjzrPF/et3URy9Bv1WTRi" crossorigin="anonymous">
        <link rel="stylesheet" href="my_account.css" type="text/css">
        <title>Home</title>
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
    
    <?php if (isset($user)): ?>
      <div class="bg-image2">
        <div class="container">



            <h1><br><br>Hello, <?= htmlspecialchars($user["name"]) ?></h1>
            <br>
            <h3> 
                Current Balance: $<?= htmlspecialchars($user["balance"]) ?>
            </h3>
            <br>
            <button type="button" class="btn btn-light"><a href="logout.php"><h5>Log out</h5></a></button>


            
        </div>
      </div>
        






    <?php else: ?>
      <div class="bg-image1">
        <div class="container">
            <h1><br><br><br>Create an Account or Log In Before Continuing</h1><br>
            <button type="button" class="btn btn-light"><a href="create_account.html"><h5>Create an Account</h5></a></button>
            <button type="button" class="btn btn-light"><a href="login.php"><h5>Log in</h5></a></button>
        </div>
      </div>
    <?php endif; ?>
    
</body>
</html>