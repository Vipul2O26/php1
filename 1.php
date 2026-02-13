<?php
include './accessory.html';
include './db.php';

/* ================= FUNCTIONS ================= */

function cleanInput($data) {
    return htmlspecialchars(trim($data));
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

    return $errors;
}

/* ================= VARIABLES ================= */

$firstNameError = $lastNameError = $emailError = $passwordError = $cnfpasswordError = "";
$firstNameFlag = $lastNameFlag = $emailFlag = $passwordFlag = $cnfpasswordFlag = true;

$firstName = $lastName = $email = $password = $cnfPassword = $gender = "";

/* ================= FORM SUBMIT ================= */

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    /* First Name */
    if (empty($_POST['firstName'])) {
        $firstNameError = "First name is required";
        $firstNameFlag = false;
    } elseif (!checkAlphabet($_POST['firstName'])) {
        $firstNameError = "Only alphabets allowed";
        $firstNameFlag = false;
    } else {
        $firstName = cleanInput($_POST['firstName']);
    }

    /* Last Name */
    if (empty($_POST['lastName'])) {
        $lastNameError = "Last name is required";
        $lastNameFlag = false;
    } elseif (!checkAlphabet($_POST['lastName'])) {
        $lastNameError = "Only alphabets allowed";
        $lastNameFlag = false;
    } else {
        $lastName = cleanInput($_POST['lastName']);
    }

    /* Email */
    if (empty($_POST['email'])) {
        $emailError = "Email is required";
        $emailFlag = false;
    } elseif (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
        $emailError = "Invalid email format";
        $emailFlag = false;
    } else {
        $email = cleanInput($_POST['email']);
    }

    /* Password */
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

    /* Confirm Password */
    if (empty($_POST['cnfpassword'])) {
        $cnfpasswordError = "Confirm password required";
        $cnfpasswordFlag = false;
    } elseif ($_POST['cnfpassword'] !== $password) {
        $cnfpasswordError = "Passwords do not match";
        $cnfpasswordFlag = false;
    } else {
        $cnfPassword = $_POST['cnfpassword'];
    }

    /* Gender */
    $gender = $_POST['gender'] ?? '';

    /* ================= FINAL CHECK ================= */

    if (
        $firstNameFlag &&
        $lastNameFlag &&
        $emailFlag &&
        $passwordFlag &&
        $cnfpasswordFlag
    ) {
        try {
            $hashPassword = password_hash($password, PASSWORD_DEFAULT);

            $sql = "INSERT INTO userData (Name, LastName, Email, Password)
                    VALUES (:fname, :lname, :email, :password)";

            $stmt = $connect->prepare($sql);
            $stmt->execute([
                ':fname' => $firstName,
                ':lname' => $lastName,
                ':email' => $email,
                ':password' => $hashPassword
            ]);

            header("Location: login.php");
            exit;

        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                $emailError = "Email already registered";
            } else {
                echo $e->getMessage();
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Register</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>

<div class="container mt-5 d-flex justify-content-center">
<div class="card col-md-8">
<div class="card-body">

<h3 class="text-center text-secondary mb-4">Sign Up</h3>

<form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post">

<div class="row g-3">

<div class="col-md-6">
<label>First Name *</label>
<input type="text" name="firstName" class="form-control" value="<?php echo $firstName; ?>">
<span class="text-danger"><?php echo $firstNameError; ?></span>
</div>

<div class="col-md-6">
<label>Last Name *</label>
<input type="text" name="lastName" class="form-control" value="<?php echo $lastName; ?>">
<span class="text-danger"><?php echo $lastNameError; ?></span>
</div>

<div class="col-12">
<label>Email *</label>
<input type="text" name="email" class="form-control" value="<?php echo $email; ?>">
<span class="text-danger"><?php echo $emailError; ?></span>
</div>

<div class="col-md-6">
<label>Password *</label>
<input type="password" name="password" class="form-control">
<span class="text-danger"><?php echo $passwordError; ?></span>
</div>

<div class="col-md-6">
<label>Confirm Password *</label>
<input type="password" name="cnfpassword" class="form-control">
<span class="text-danger"><?php echo $cnfpasswordError; ?></span>
</div>

<div class="col-12">
<label>Gender *</label><br>
<input type="radio" name="gender" value="male" checked> Male
<input type="radio" name="gender" value="female"> Female
<input type="radio" name="gender" value="other"> Other
</div>

<div class="col-12 text-center">
<button type="submit" class="btn btn-info text-light w-100 py-2">
Register
</button>
</div>

<div class="col-12 text-center mt-2">
<p>Already have an account? <a href="login.php">Sign in</a></p>
</div>

</div>
</form>

</div>
</div>
</div>

</body>
</html>