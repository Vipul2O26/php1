<?php
include './accessory.html';
include './db.php';

$emailError = $passwordError = "";
$emailFlag = $passwordFlag = true;

// Handle form submit
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // ================= EMAIL VALIDATION =================
    if (empty($_POST['email'])) {
        $emailError = "Email is required";
        $emailFlag = false;
    } elseif (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
        $emailError = "Invalid email format";
        $emailFlag = false;
    } else {
        $email = trim($_POST['email']);
    }

    // ================= PASSWORD VALIDATION =================
    if (empty($_POST['password'])) {
        $passwordError = "Password is required";
        $passwordFlag = false;
    } else {
        $passwordErrors = checkPassword($_POST['password']);

        if (!empty($passwordErrors)) {
            $passwordError = implode("<br>", $passwordErrors);
            $passwordFlag = false;
        } else {
            $password = $_POST['password'];
        }
    }

    // ================= DATABASE CHECK =================
    if ($emailFlag && $passwordFlag) {

        $stmt = $connect->prepare("SELECT Password FROM userData WHERE Email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows === 1) {
            $stmt->bind_result($hashedPassword);
            $stmt->fetch();

            if (password_verify($password, $hashedPassword)) {
                echo "<script>alert('Login successful');</script>";
            } else {
                $passwordError = "Incorrect password";
            }

        } else {
            $emailError = "Email not registered";
        }

        $stmt->close();
    }
}

// ================= PASSWORD FUNCTION =================
function checkPassword($string) {
    $errors = [];

    if (strlen($string) < 8) {
        $errors[] = "Password must be at least 8 characters";
    }
    if (!preg_match("/[a-z]/", $string)) {
        $errors[] = "Must contain at least 1 lowercase letter";
    }
    if (!preg_match("/[A-Z]/", $string)) {
        $errors[] = "Must contain at least 1 uppercase letter";
    }
    if (!preg_match("/[0-9]/", $string)) {
        $errors[] = "Must contain at least 1 digit";
    }

    return $errors;
}
?>

<!-- ================= HTML FORM ================= -->

<div class="container mt-5 d-flex justify-content-center align-items-center">
    <div class="card col-md-6 mx-3 mt-5">
        <div class="card-title">
            <p class="text-center fs-2 text-secondary py-3">Login</p>

            <div class="card-body mx-3">
                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">

                    <div class="mb-3">
                        <label>Email <span class="text-danger">*</span></label>
                        <input type="text" name="email" class="form-control border-info"
                               value="<?php echo $_POST['email'] ?? ''; ?>">
                        <span class="text-danger"><?php echo $emailError; ?></span>
                    </div>

                    <div class="mb-3">
                        <label>Password <span class="text-danger">*</span></label>
                        <input type="password" name="password" id="password"
                               class="form-control border-info">
                        <span class="text-danger"><?php echo $passwordError; ?></span>
                    </div>

                    <div class="mb-3">
                        <input type="checkbox" onclick="togglePassword()"> Show Password
                    </div>

                    <div class="text-center">
                        <input type="submit" value="Login"
                               class="btn btn-info text-light w-100 py-2">
                    </div>

                    <div class="text-center mt-3">
                        <p>Don't have an account?
                            <a href="register.php" class="text-decoration-none text-info">Sign up</a>
                        </p>
                    </div>

                </form>
            </div>
        </div>
    </div>
</div>

<!-- ================= JS ================= -->
<script>
function togglePassword() {
    const pwd = document.getElementById("password");
    pwd.type = pwd.type === "password" ? "text" : "password";
}
</script>