
<?php include 'includes/session.php'; ?>

<?php
  if(isset($_POST['signup'])){
    $firstname = $_POST['firstname'];
    $lastname = $_POST['lastname'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $repassword = $_POST['repassword'];

    if (!empty($firstname) && !empty($lastname) && !empty($email) && !empty($password) && !empty($repassword)) {
    
      $_SESSION['firstname'] = $firstname;
      $_SESSION['lastname'] = $lastname;
      $_SESSION['email'] = $email;


      if($password != $repassword){
        $_SESSION['error'] = 'Passwords did not match';
      } else {
        $conn = $pdo->open();

        $stmt = $conn->prepare("SELECT COUNT(*) AS numrows FROM users WHERE email=:email");
        $stmt->execute(['email'=>$email]);
        $row = $stmt->fetch();
        if($row['numrows'] > 0){
          $_SESSION['error'] = 'Email already taken';
        } else {
          $now = date('Y-m-d');
          $password = password_hash($password, PASSWORD_DEFAULT);

          //generate code
          $set='123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
          $code=substr(str_shuffle($set), 0, 12);

          try{
            $stmt = $conn->prepare("INSERT INTO users (status, email, password, firstname, lastname, activate_code, created_on) VALUES (:status, :email, :password, :firstname, :lastname, :code, :now)");
            $stmt->execute(['status'=>1, 'email'=>$email, 'password'=>$password, 'firstname'=>$firstname, 'lastname'=>$lastname, 'code'=>$code, 'now'=>$now]);
            $userid = $conn->lastInsertId();
            echo "<script> alert('Your registration was successful, proceed to login your dash board now!'); window.location.href='login.php'; </script>";
          }
          catch(PDOException $e){
            $_SESSION['error'] = $e->getMessage();
          }

          $pdo->close();

        }

      }
    } else {
      $_SESSION['error'] = 'Fill up signup form first';
    }
  }

?>

<?php
  if(isset($_SESSION['user'])){
    header('location: cart_view.php');
  }
   $now = time();
   $_SESSION['captcha'] = $now;

  if(isset($_SESSION['captcha'])){
    $now = time();
    if($now >= $_SESSION['captcha']){
      unset($_SESSION['captcha']);
    }
  }

?>
<?php include 'includes/header.php'; ?>
<body class="hold-transition register-page">
<div class="register-box">
  	<?php
      if(isset($_SESSION['error'])){
        echo "
          <div class='callout callout-danger text-center'>
            <p>".$_SESSION['error']."</p> 
          </div>
        ";
        unset($_SESSION['error']);
      }

      if(isset($_SESSION['success'])){
        echo "
          <div class='callout callout-success text-center'>
            <p>".$_SESSION['success']."</p> 
          </div>
        ";
        unset($_SESSION['success']);
      }
    ?>
  	<div class="register-box-body">
    	<p class="login-box-msg">Register a new membership</p>

    	<form action="signup.php" method="POST">
          <div class="form-group has-feedback">
            <input type="text" class="form-control" name="firstname" placeholder="Firstname" value="<?php echo (isset($_SESSION['firstname'])) ? $_SESSION['firstname'] : '' ?>" required>
            <span class="glyphicon glyphicon-user form-control-feedback"></span>
          </div>
          <div class="form-group has-feedback">
            <input type="text" class="form-control" name="lastname" placeholder="Lastname" value="<?php echo (isset($_SESSION['lastname'])) ? $_SESSION['lastname'] : '' ?>"  required>
            <span class="glyphicon glyphicon-user form-control-feedback"></span>
          </div>
      		<div class="form-group has-feedback">
        		<input type="email" class="form-control" name="email" placeholder="Email" value="<?php echo (isset($_SESSION['email'])) ? $_SESSION['email'] : '' ?>" required>
        		<span class="glyphicon glyphicon-envelope form-control-feedback"></span>
      		</div>
          <div class="form-group has-feedback">
            <input type="password" class="form-control" name="password" placeholder="Password" required>
            <span class="glyphicon glyphicon-lock form-control-feedback"></span>
          </div>
          <div class="form-group has-feedback">
            <input type="password" class="form-control" name="repassword" placeholder="Retype password" required>
            <span class="glyphicon glyphicon-log-in form-control-feedback"></span>
          </div>
          <hr>
      		<div class="row">
    			<div class="col-xs-4">
          			<button type="submit" class="btn btn-primary btn-block btn-flat" name="signup"><i class="fa fa-pencil"></i> Sign Up</button>
        		</div>
      		</div>
    	</form>
      <br>
      <a href="login.php">I already have a membership</a><br>
      <a href="index.php"><i class="fa fa-home"></i> Home</a>
  	</div>
</div>
	
<?php include 'includes/scripts.php' ?>
</body>
</html>