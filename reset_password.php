<?php

require_once "config.php";

$message = "";


/*
    The resident must first pass the reset-code verification.
*/
if (!isset($_SESSION["reset_user_id"])) {

    header("Location: forgot_password.php");
    exit;
}

$reset_user_id = $_SESSION["reset_user_id"];


/*
    Process the new password.
*/
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $new_password = $_POST["new_password"];
    $confirm_password = $_POST["confirm_password"];


    if ($new_password === "" || $confirm_password === "") {

        $message = "Please enter your new password.";

    } elseif ($new_password !== $confirm_password) {

        $message = "The passwords do not match.";

    } elseif (strlen($new_password) < 6) {

        $message = "Password must be at least 6 characters long.";

    } else {

        /*
            Create a secure password hash.
        */
        $password_hash = password_hash(
            $new_password,
            PASSWORD_DEFAULT
        );


        $stmt = $conn->prepare(
            "UPDATE users
             SET password_hash = ?,
                 reset_token = NULL,
                 reset_expires = NULL
             WHERE id = ?"
        );

        $stmt->bind_param(
            "si",
            $password_hash,
            $reset_user_id
        );


        if ($stmt->execute()) {

            /*
                Remove the temporary reset session.
            */
            unset($_SESSION["reset_user_id"]);


            
            header("Location: login.php?reset=success");
            exit;

        } else {

            $message = "Something went wrong. Please try again.";
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

    <title>Reset Password</title>

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

            <h1>Reset Password</h1>

            <p>
                Create a new password for your barangay account.
            </p>


            <?php if ($message !== ""): ?>

                <div class="message">
                    <?php echo e($message); ?>
                </div>

            <?php endif; ?>


            <form method="POST" action="reset_password.php">


                <label for="new_password">
                    New Password
                </label>


                <div class="password-wrapper">

                    <input
                        type="password"
                        id="new_password"
                        name="new_password"
                        minlength="6"
                        placeholder="Enter new password"
                        required
                    >


                    <button
                        type="button"
                        class="password-toggle"
                        onclick="togglePassword('new_password', this)"
                        aria-label="Show password"
                    >

                        <svg
                            width="22"
                            height="22"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                        >

                            <path d="M3 3l18 18"></path>

                            <path
                                d="M10.58 10.58a2 2 0 0 0 2.83 2.83"
                            ></path>

                            <path
                                d="M9.88 4.24A9.9 9.9 0 0 1 12 4c5 0 9.27 3.11 11 8a18.5 18.5 0 0 1-2.16 3.19"
                            ></path>

                            <path
                                d="M6.61 6.61A18.5 18.5 0 0 0 1 12c1.73 4.89 6 8 11 8a9.9 9.9 0 0 0 2.12-.24"
                            ></path>

                        </svg>

                    </button>

                </div>


                <label for="confirm_password">
                    Confirm New Password
                </label>


                <div class="password-wrapper">

                    <input
                        type="password"
                        id="confirm_password"
                        name="confirm_password"
                        minlength="6"
                        placeholder="Re-enter new password"
                        required
                    >


                    <button
                        type="button"
                        class="password-toggle"
                        onclick="togglePassword('confirm_password', this)"
                        aria-label="Show password"
                    >

                        <svg
                            width="22"
                            height="22"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                        >

                            <path d="M3 3l18 18"></path>

                            <path
                                d="M10.58 10.58a2 2 0 0 0 2.83 2.83"
                            ></path>

                            <path
                                d="M9.88 4.24A9.9 9.9 0 0 1 12 4c5 0 9.27 3.11 11 8a18.5 18.5 0 0 1-2.16 3.19"
                            ></path>

                            <path
                                d="M6.61 6.61A18.5 18.5 0 0 0 1 12c1.73 4.89 6 8 11 8a9.9 9.9 0 0 0 2.12-.24"
                            ></path>

                        </svg>

                    </button>

                </div>


                <br><br>


                <button type="submit">
                    Reset Password
                </button>

            </form>


            <p style="margin-top: 20px;">

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


    <script>

    function togglePassword(inputId, button) {

        const input =
            document.getElementById(inputId);


        if (input.type === "password") {

            input.type = "text";


            button.innerHTML = `
                <svg
                    width="22"
                    height="22"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                >

                    <path
                        d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12z"
                    ></path>

                    <circle
                        cx="12"
                        cy="12"
                        r="3"
                    ></circle>

                </svg>
            `;


            button.setAttribute(
                "aria-label",
                "Hide password"
            );


        } else {

            input.type = "password";


            button.innerHTML = `
                <svg
                    width="22"
                    height="22"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                >

                    <path d="M3 3l18 18"></path>

                    <path
                        d="M10.58 10.58a2 2 0 0 0 2.83 2.83"
                    ></path>

                    <path
                        d="M9.88 4.24A9.9 9.9 0 0 1 12 4c5 0 9.27 3.11 11 8a18.5 18.5 0 0 1-2.16 3.19"
                    ></path>

                    <path
                        d="M6.61 6.61A18.5 18.5 0 0 0 1 12c1.73 4.89 6 8 11 8a9.9 9.9 0 0 0 2.12-.24"
                    ></path>

                </svg>
            `;


            button.setAttribute(
                "aria-label",
                "Show password"
            );

        }

    }

    </script>


</body>

</html>