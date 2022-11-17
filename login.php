<?php

$is_invalid = false;

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    
  $servername = "localhost";
  $username = "hplante";
  $password = "pwpwpwpw";
  $database = "hplante";

  $connection = new mysqli($servername, $username, $password, $database);

  // connect to db
  if ($connection->connect_error) {
      die("Connection failed: " . $connection->connect_error);
  }

    $sql = sprintf("SELECT * FROM Users WHERE email = '%s'", $connection->real_escape_string($_POST["email"]));
    
    $result = $connection->query($sql);
    
    $user = $result->fetch_assoc();
    
    if ($user) {            
      session_start();
      
      session_regenerate_id();
      
      $_SESSION["user_id"] = $user["user_id"];
      
      header("Location: index.php");
      exit;
    }
    $is_invalid = true;
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
    <meta charset="UTF-8">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-Zenh87qX5JnK2Jl0vWa8Ck2rdkQ2Bzep5IDxbcnCeuOxjzrPF/et3URy9Bv1WTRi" crossorigin="anonymous">
    <link rel="stylesheet" href="login.css" type="text/css">
</head>
<body>

    <div class="bg-image">

        <div class="container">

            <h1><br><br>Log In<br><br></h1>
            
            <?php if ($is_invalid): ?>
                <em>Invalid login</em>
            <?php endif; ?>
            
            <div class="container">
                <div class="row">
                <div class="col-sm-9 col-md-7 col-lg-5 mx-auto">
                    <div class="card border-0 shadow rounded-3 my-5">
                    <div class="card-body p-4 p-sm-5">
                        <h2 class="card-title text-center mb-5 fw-light fs-5" style="color:#000;">Log In</h2>
                        <form method="post">
                        <div class="form-floating mb-3">
                            <input type="email" class="form-control" id="floatingInput" name="email" value="<?= htmlspecialchars($_POST["email"] ?? "") ?>">
                            <label for="floatingInput" style="font-size: 15px; color: #000;">Email</label>
                        </div>
                        <div class="form-floating mb-3">
                            <input type="password" class="form-control" id="floatingPassword" name="password">
                            <label for="floatingPassword" style="font-size: 15px; color: #000;">Password</label>
                        </div>
                        <div class="d-grid">
                            <button class="btn btn-primary btn-login text-uppercase fw-bold" type="submit">Log In</button>
                        </div>
                        </form>
                    </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>