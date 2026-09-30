<?php

require_once "config.php";

$message = "";
$email = "";

/*
    Get the email from forgot_password.php
*/
if (isset($_GET["email"])) {
    $email = trim($_GET["email"]);
}

/*
    Verify the reset code
*/
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $email = trim($_POST["email"]);
    $reset_code = trim($_POST["reset_code"]);

    if ($email === "" || $reset_code === "") {

        $message = "Please enter your email and reset code.";

    } elseif (!preg_match("/^[0-9]{6}$/", $reset_code)) {

        $message = "Reset code must be exactly 6 digits.";

    } else {

        $stmt = $conn->prepare(
            "SELECT id, reset_token, reset_expires
             FROM users
             WHERE email = ?
             AND role IN ('resident', 'staff', 'admin')
             AND status = 'Active'"
        );

        $stmt->bind_param("s", $email);
        $stmt->execute();

        $result = $stmt->get_result();

        if ($result->num_rows === 1) {

            $user = $result->fetch_assoc();

            /*
                Check if the reset code exists and
                has not expired.
            */
            if (
                empty($user["reset_token"]) ||
                empty($user["reset_expires"]) ||
                strtotime($user["reset_expires"]) < time()
            ) {

                $message = "The reset code is invalid or has expired.";

            } elseif (password_verify($reset_code, $user["reset_token"])) {

                /*
                    The reset code is correct.

                    Store the resident ID temporarily
                    in the session.
                */
                $_SESSION["reset_user_id"] = $user["id"];

                /*
                    Go to the new password page.
                */
                header("Location: reset_password.php");
                exit;

            } else {

                $message = "The reset code is incorrect.";
            }

        } else {

            $message = "The reset code is invalid or has expired.";
        }

        $stmt->close();
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Verify Reset Code</title>

    <link rel="stylesheet" href="assets/style.css">

</head>

<body>

    <nav class="navbar">

        <div class="navbar-content">

            <div class="navbar-title">
                Barangay Appointment Management System
            </div>

            <div>
                <a href="index.php">Home</a>
                <a href="login.php">Login</a>
            </div>

        </div>

    </nav>


    <div class="container">

        <div class="form-card">

            <h1>Verify Reset Code</h1>

            <p>
                Enter the 6-digit reset code generated for your account.
            </p>


            <?php if ($message !== ""): ?>

                <div class="message">
                    <?php echo e($message); ?>
                </div>

            <?php endif; ?>


            <form method="POST" action="verify_reset.php">

                <label for="email">
                    Registered Email
                </label>

                <input
                    type="email"
                    id="email"
                    name="email"
                    value="<?php echo e($email); ?>"
                    required
                >


                <label for="reset_code">
                    6-Digit Reset Code
                </label>

                <input
                    type="text"
                    id="reset_code"
                    name="reset_code"
                    maxlength="6"
                    pattern="[0-9]{6}"
                    inputmode="numeric"
                    placeholder="Enter 6-digit code"
                    required
                >


                <br><br>

                <button type="submit">
                    Verify Code
                </button>

            </form>


            <p style="margin-top: 20px;">

                <a href="forgot_password.php">
                    Generate a New Reset Code
                </a>

            </p>

            <p>

                <a href="login.php">
                    Back to Login
                </a>

            </p>

        </div>

    </div>


    <footer class="footer">

        <p>
            Barangay Appointment Management System
        </p>

    </footer>

</body>

</html>