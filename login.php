 <?php

    session_start();

    ini_set('display_errors', 1);
    ini_set('display_startup_errors',1);
    error_reporting(E_ALL);
    
    include './db.php';
    include './link.php';

    echo "<pre>";
    print_r($_POST);
    echo "</pre>";

    // if( isset($_POST['role'] ) ) {

    // }

    $emailError = $passwordError = null;
    $emailFlag= $passwordFlag = $remember_meFlag = true;

    $email = $password = $rememberme = null;

    if($_SERVER["REQUEST_METHOD"] === "POST") {
         // email
        if ( empty ( $_POST['email'] ) ) {
            $emailError = "email is required";
            $emailFlag = false;
        } elseif ( !filter_var($_POST['email'] , FILTER_VALIDATE_EMAIL ) ) {
            $emailError = "email must be in valid format";
            $emailFlag = false;
        } else {
            $email = trim($_POST['email']);
        }
    
        //pasword
        if ( empty ( $_POST['password'] ) ) {
            $passwordError = "password is required";
            $passwordFlag = false;
        } else{
            $passwordErrors = checkPassword( $_POST['password'] );
            $passwordFlag = false;

            if( empty( $errors ) ) {
                //print_r($passwordErrors);
                foreach( $passwordErrors as $passwordError ) {
                    $passwordError;
                    $passwordFlag = false;
                }
            } else {
                $password = $_POST['password'];
            }
        }

        // remember me 

        if ( empty ( $_POST['rememberMe'] ) ) {
            $remember_meFlag = false;
        } else {
            $remember_meFlag = true;
        }


        
        // check user 
        if( ( !$emailFlag ) && ( !$passwordFlag) ) {
            $alert = "<div class='alert alert-danger mt-5' role='alert' id='login'>email and password are required</div>";
        } else {
            try {
                $stmt = $connect->prepare("SELECT * FROM users WHERE Email = ?");
                $stmt->execute([$_POST['email']]);
                $user = $stmt->fetch();

                // session
                if($user) {
                    $_SESSION['firstname'] = $user['firstname'];
                    $_SESSION['lastname'] = $user['lastname'];
                    $_SESSION['email'] = $user['email'];
                    $_SESSION['role'] = $user['role'];
                }

                // cookies

                //print_r($user);
                $notification = null;
                $alert = null;

                if($user && password_verify($_POST['password'],$user['password'])) {
                    $_SESSION['notification'] = " <div class='alert alert-success mt-5' role='alert' id='login'>login successfully</div>";
                    // header("Location: Dashboard.php");

                    //echo "halt";
                    if($remember_meFlag && !empty($user['email']) ) {
                        setcookie( "user_email", $user['email'], time() + 4 , "./check_cookie.php" );
                    } else {
                        if ( $user['role'] == "admin" ) {
                        header("Location: admin_dashboard.php");
                        exit();
                        } else {
                            header("Location: user_dashboard.php");
                            exit();
                        }
                    }
                     

                } else { 
                    $alert = "<div class='alert alert-danger mt-5' role='alert' id='emailmsg'>email or password is incorrect</div>";
                }
            } catch ( PDOException $e ) {
                $sqlStateCode = $e->getCode();
                    echo "$sqlStateCode";
                    
                    if( $sqlStateCode == "42000" ) {
                        echo "may be syntax error";
                    }
                    if( $sqlStateCode == "23000" ) {
                        echo "email must be unique";
                }
            }
        }
    }

    function checkAlphabet($string) {
        return ctype_alpha($string);
    }
    function checkPassword($password) {
        $errors = [];
        if (strlen($password) < 8) {
            $errors[] = "Password must be at least 8 characters";
        }
        if (!preg_match("/[a-z]/", $password)) {
            $errors[] = "Password must contain at least one lowercase letter";
        }
        if (!preg_match("/[A-Z]/", $password)) {
            $errors[] = "Password must contain at least one uppercase letter";
        }
        if (!preg_match("/[0-9]/", $password)) {
            $errors[] = "Password must contain at least one digit";
        }
        //echo $password;
        //print_r($errors);
        return $errors;
    }
?>


<body style="background-color: rgba(49, 167, 203);">

<div class="container">
   <?php  if(!empty($alert)) {
    echo $alert;
   } ?>
</div>
    
     <div class="container mt-5 d-flex justify-content-center align-items-center">
         <div class="card col-md-6 mx-3 mt-5">
            <div class="card-title">
                <p class="text-center fs-2 text-secondary py-3">Login</p>

                <div class="card-body mx-3">
                    <form action="<?php htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                        <div class="row g-3">
                            <div class="col-12">
                                <label for="email" class="form-label">Email:</label>
                                <span class="text-danger">*</span>
                                <input type="text" name="email" id="email" class="form-control border-info" value="<?php echo $email; ?>" placeholder="enter email" required>
                                <span class="text-danger"> <?php echo $emailError  ?></span>
                            </div>

                            <div class="col-12">
                                <label for="password" class="form-label">Password:</label>
                                <span class="text-danger">*</span>
                                <input type="password" name="password" id="password" class="form-control border-info" value="<?php echo $password; ?>" placeholder="enter password" required>
                                <img src="./assest/hide.png" height="20px" alt="hide" id="togglepassword" class="toggleicon">
                                <span class="text-danger"> <?php echo $passwordError  ?></span>
                            </div>

                            <div class="col-12">
                                <input type="checkbox" name="rememberMe" id="rememberMe" class="form-check-input border-info" value="rememberMe">
                                <label for="checkbox" class="form-check-label"> Remember me</label>
                            </div>

                            <div class="col-12">
                                <div class="text-center">
                                    <input type="submit" value="Login" class="btn btn-info text-light w-100 py-3">
                                </div>
                            </div>
                            
                            <div class="col-12">
                                <div class="text-center">
                                    <p>Don't have an account? <a href="register.php" class="text-decoration-none text-info">Sign up</a></p>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>

 <script>
      $(document).ready(function () {
        $("#togglepassword").click(function () {
          let input = $("#password");
          console.log(input);

          if (input.attr("type") === "password") {
            input.attr("type", "text");
            $(this).attr("src","./assest/view.png");
          } else {
            input.attr("type", "password");
            $(this).attr("src","./assest/hide.png");
          }
        });

         setTimeout(() => {
            $("#login").fadeOut("slow");
        }, 2000);

        setTimeout(() => {
            $("#emailmsg").fadeOut("slow");
        }, 2000);
        
      });
</script>