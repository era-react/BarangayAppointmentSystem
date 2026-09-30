<?php

require_once "config.php";

// Email login details are kept in mail_config.php (not in this file)
$mail_settings = require __DIR__ . "/mail_config.php";

require_once __DIR__ . "/PHPMailer-master (1)/PHPMailer-master/src/Exception.php";
require_once __DIR__ . "/PHPMailer-master (1)/PHPMailer-master/src/PHPMailer.php";
require_once __DIR__ . "/PHPMailer-master (1)/PHPMailer-master/src/SMTP.php";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $email = trim($_POST["email"] ?? "");

    if ($email === "") {

        $message = "Please enter your registered email address.";

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $message = "Please enter a valid email address.";

    } else {

        $stmt = $conn->prepare("
    SELECT id, full_name
    FROM users
    WHERE email = ?
    AND status = 'Active'
    AND role IN ('resident', 'staff', 'admin')
    LIMIT 1
");
        $stmt->bind_param("s", $email);
        $stmt->execute();

        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        if ($user) {

            // Generate a secure 6-digit reset code
            $reset_code = (string) random_int(100000, 999999);

            // Store a hashed version of the code
            $reset_token = password_hash($reset_code, PASSWORD_DEFAULT);

            // Code expires after 10 minutes
            $reset_expires = date("Y-m-d H:i:s", time() + 600);

            $update = $conn->prepare("
                UPDATE users
                SET reset_token = ?, reset_expires = ?
                WHERE id = ?
            ");

            $update->bind_param(
                "ssi",
                $reset_token,
                $reset_expires,
                $user["id"]
            );

            $update->execute();

            $mail = new PHPMailer(true);

            try {

                // Gmail SMTP
                $mail->isSMTP();
                $mail->Host = "smtp.gmail.com";
                $mail->SMTPAuth = true;

                $mail->Username = $mail_settings["username"];

    
                $mail->Password = $mail_settings["password"];

                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = 587;

                // Sender
                $mail->setFrom(
                    "rodneyhermita8@gmail.com",
                    "Barangay Appointment Management System"
                );

                // Recipient
                $mail->addAddress(
                    $email,
                    $user["full_name"]
                );

                // Email format
                $mail->isHTML(true);

                $mail->Subject = "Password Reset Code - Barangay Appointment Management System";

                $mail->Body = "
                    <div style='font-family: Arial, sans-serif; line-height: 1.6;'>
                        <h2>Barangay Appointment Management System</h2>

                        <p>Hello " . e($user["full_name"]) . ",</p>

                        <p>
                            We received a request to reset the password
                            for your barangay account.
                        </p>

                        <p>Your password reset code is:</p>

                        <div style='font-size: 32px; font-weight: bold; letter-spacing: 6px;'>
                            " . e($reset_code) . "
                        </div>

                        <p>
                            This code will expire in
                            <strong>10 minutes</strong>.
                        </p>

                        <p>
                            If you did not request a password reset,
                            you can safely ignore this email.
                        </p>

                        <p>
                            Barangay Appointment Management System
                        </p>
                    </div>
                ";

                $mail->AltBody =
                    "Your password reset code is: " .
                    $reset_code .
                    ". This code expires in 10 minutes.";

                $mail->send();

                // Automatically go to the verification page
                header(
                    "Location: verify_reset.php?email=" .
                    urlencode($email)
                );
                exit;

            } catch (Exception $e) {

                // Do not show technical SMTP errors to users
                $message =
                    "The password reset email could not be sent. " .
                    "Please try again later.";
            }

        } else {

            // Keep the message general for security
            $message =
                "If the email belongs to an active account, " .
                "a password reset code will be sent to that email address.";
        }
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

    <title>Forgot Password</title>

    <link
        rel="stylesheet"
        href="assets/style.css"
    >

</head>

<body>

    <nav class="navbar">

    <div class="navbar-content">

        <div class="navbar-title">
            Barangay Appointment Management System
        </div>

        <div>

            <a href="index.php">
                Home
            </a>

            <a href="login.php">
                Login
            </a>

        </div>

    </div>

</nav>


    <main class="page-container">

        <div class="form-card">

            <h1>Forgot Password</h1>

            <p>
              Enter the email address registered to your
              barangay account. A password reset code will
              be sent to your email.
            </p>


            <?php if ($message !== ""): ?>

                <div class="message">
                    <?php echo e($message); ?>
                </div>

            <?php endif; ?>


            <form method="POST">

                <label for="email">
                    Registered Email
                </label>

                <input
                    type="email"
                    id="email"
                    name="email"
                    placeholder="Enter your registered email"
                    required
                >

                <button
                    type="submit"
                    class="btn"
                >
                    Send Reset Code
                </button>

            </form>


            <p style="margin-top: 20px;">

                <a href="login.php">
                    Back to Login
                </a>

            </p>

        </div>

    </main>


    <footer>

        <p>
            Barangay Appointment Management
        </p>

    </footer>

</body>

</html>