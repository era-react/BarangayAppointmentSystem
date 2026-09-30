<?php

require_once "config.php";

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $email = trim($_POST["email"]);
    $password = $_POST["password"];

    if ($email === "" || $password === "") {

        $message =
            "Please enter your email and password.";

    } else {

        $stmt = $conn->prepare(
            "SELECT
                id,
                full_name,
                password_hash,
                role,
                status
             FROM users
             WHERE email = ?"
        );

        $stmt->bind_param(
            "s",
            $email
        );

        $stmt->execute();

        $result = $stmt->get_result();

        if ($result->num_rows === 1) {

            $user = $result->fetch_assoc();

            if (
                password_verify(
                    $password,
                    $user["password_hash"]
                )
            ) {

                if ($user["status"] !== "Active") {

                    $message =
                        "Your account is still waiting for staff approval.";

                } else {

                    // Create a fresh session ID after login (blocks session fixation)
                    session_regenerate_id(true);

                    $_SESSION["user_id"] =
                        $user["id"];

                    $_SESSION["full_name"] =
                        $user["full_name"];

                    $_SESSION["role"] =
                        $user["role"];


                    if (
    $user["role"] === "staff" ||
    $user["role"] === "admin"
) {

    header(
        "Location: staff/dashboard.php"
    );

    exit;

} else {
                        header(
                            "Location: resident/dashboard.php"
                        );

                        exit;
                    }
                }

            } else {

                $message =
                    "Invalid email or password.";
            }

        } else {

            $message =
                "Invalid email or password.";
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

    <title>
        Login
    </title>

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

                <a href="register.php">
                    Register
                </a>

            </div>

        </div>

    </nav>


    <main class="container">


        <section class="form-card">

            <h1>
                Login
            </h1>

            <p>
                Sign in to manage your barangay
                service appointments.
            </p>


            <?php if ($message !== ""): ?>

                <div class="message">

                    <?php echo e($message); ?>

                </div>

            <?php endif; ?>


            <form method="POST">


                <label for="email">
                    Email Address
                </label>

                <input
                    type="email"
                    id="email"
                    name="email"
                    required
                >


                <label for="password">
                    Password
                </label>


                <div class="password-wrapper">

                    <input
                        type="password"
                        id="password"
                        name="password"
                        required
                    >


                    <button
                        type="button"
                        class="password-toggle"
                        onclick="togglePassword('password', this)"
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


                <br>


                <button type="submit">
                    Login
                </button>


            </form>


            <p style="margin-top: 20px;">

                <a href="forgot_password.php">
                    Forgot Password?
                </a>

            </p>


            <br>


            <a
                href="index.php"
                class="button button-secondary"
            >
                Back to Home
            </a>


        </section>


    </main>


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