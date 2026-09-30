<?php

require_once "../config.php";

require_resident();

$message = "";
$message_type = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $current_password = $_POST["current_password"];
    $new_password = $_POST["new_password"];
    $confirm_password = $_POST["confirm_password"];


    if (
        $current_password === "" ||
        $new_password === "" ||
        $confirm_password === ""
    ) {

        $message = "Please complete all password fields.";
        $message_type = "error";


    } elseif ($new_password !== $confirm_password) {

        $message = "New passwords do not match.";
        $message_type = "error";


    } elseif (strlen($new_password) < 6) {

        $message = "New password must be at least 6 characters.";
        $message_type = "error";


    } else {

        $stmt = $conn->prepare(
            "SELECT password_hash
             FROM users
             WHERE id = ?"
        );

        $stmt->bind_param(
            "i",
            $_SESSION["user_id"]
        );

        $stmt->execute();

        $result = $stmt->get_result();

        $user = $result->fetch_assoc();

        $stmt->close();


        if (
            $user &&
            password_verify(
                $current_password,
                $user["password_hash"]
            )
        ) {

            $new_password_hash =
                password_hash(
                    $new_password,
                    PASSWORD_DEFAULT
                );


            $stmt = $conn->prepare(
                "UPDATE users
                 SET password_hash = ?
                 WHERE id = ?"
            );

            $stmt->bind_param(
                "si",
                $new_password_hash,
                $_SESSION["user_id"]
            );


            if ($stmt->execute()) {

                $message =
                    "Password changed successfully.";

                $message_type = "success";

            } else {

                $message =
                    "Failed to change password.";

                $message_type = "error";
            }


            $stmt->close();


        } else {

            $message =
                "Current password is incorrect.";

            $message_type = "error";
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

    <title>
        Change Password
    </title>

    <link
        rel="stylesheet"
        href="../assets/style.css"
    >

</head>


<body>


    <nav class="navbar">

        <div class="navbar-content">

            <div class="navbar-title">
                Barangay Appointment Management System
            </div>

            <div>

                <a href="dashboard.php">
                    Dashboard
                </a>

                <a href="../logout.php">
                    Logout
                </a>

            </div>

        </div>

    </nav>


    <main class="container">


        <section class="form-card">

            <h1>
                Change Password
            </h1>

            <p>
                Update your account password securely.
            </p>


            <?php if ($message !== ""): ?>

                <div class="message">

                    <?php echo e($message); ?>

                </div>

            <?php endif; ?>


            <form method="POST">


                <!-- Current Password -->

                <label for="current_password">
                    Current Password
                </label>


                <div class="password-wrapper">

                    <input
                        type="password"
                        id="current_password"
                        name="current_password"
                        required
                    >


                    <button
                        type="button"
                        class="password-toggle"
                        onclick="togglePassword('current_password', this)"
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


                <!-- New Password -->

                <label for="new_password">
                    New Password
                </label>


                <div class="password-wrapper">

                    <input
                        type="password"
                        id="new_password"
                        name="new_password"
                        minlength="6"
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


                <!-- Confirm New Password -->

                <label for="confirm_password">
                    Confirm New Password
                </label>


                <div class="password-wrapper">

                    <input
                        type="password"
                        id="confirm_password"
                        name="confirm_password"
                        minlength="6"
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


                <br>


                <button type="submit">
                    Change Password
                </button>


            </form>


            <br>


            <a
                href="dashboard.php"
                class="button button-secondary"
            >
                Back to Dashboard
            </a>


        </section>


    </main>


    <footer class="footer">

        <p>
            Barangay Appointment Management System
        </p>

        <p>
            Account Security
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
                        d="M6.61 6.61A18.5 18.5 0 0 0 1 12c1.73 0 10 7 11 8a9.9 9.9 0 0 0 2.12-.24"
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