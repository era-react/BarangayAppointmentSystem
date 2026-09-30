<?php

require_once "config.php";

$message = "";
$message_type = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $full_name = trim($_POST["full_name"]);
    $email = trim($_POST["email"]);
    $password = $_POST["password"];
    $contact_number = trim($_POST["contact_number"]);

    $house_unit_no = trim($_POST["house_unit_no"]);
    $street = trim($_POST["street"]);
    $purok = trim($_POST["purok"]);
    $barangay = trim($_POST["barangay"]);
    $city_municipality = trim($_POST["city_municipality"]);
    $province = trim($_POST["province"]);


    if (
        $full_name === "" ||
        $email === "" ||
        $password === "" ||
        $contact_number === "" ||
        $house_unit_no === "" ||
        $street === "" ||
        $purok === "" ||
        $barangay === "" ||
        $city_municipality === "" ||
        $province === ""
    ) {

        $message = "Please complete all required fields.";
        $message_type = "error";

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $message = "Please enter a valid email address.";
        $message_type = "error";

    } elseif (strlen($password) < 6) {

        $message = "Password must be at least 6 characters.";
        $message_type = "error";

    } else {

        $stmt = $conn->prepare(
            "SELECT id
             FROM users
             WHERE email = ?"
        );

        $stmt->bind_param(
            "s",
            $email
        );

        $stmt->execute();

        $result = $stmt->get_result();

        if ($result->num_rows > 0) {

            $message = "An account with this email already exists.";
            $message_type = "error";

            $stmt->close();

        } else {

            $stmt->close();

            $password_hash = password_hash(
                $password,
                PASSWORD_DEFAULT
            );


            $stmt = $conn->prepare(
                "INSERT INTO users
                (
                    full_name,
                    email,
                    password_hash,
                    role,
                    status,
                    contact_number,
                    house_unit_no,
                    street,
                    purok,
                    barangay,
                    city_municipality,
                    province
                )
                VALUES
                (
                    ?,
                    ?,
                    ?,
                    'resident',
                    'Pending',
                    ?,
                    ?,
                    ?,
                    ?,
                    ?,
                    ?,
                    ?
                )"
            );


            $stmt->bind_param(
                "ssssssssss",
                $full_name,
                $email,
                $password_hash,
                $contact_number,
                $house_unit_no,
                $street,
                $purok,
                $barangay,
                $city_municipality,
                $province
            );


            if ($stmt->execute()) {

                $message =
                    "Registration successful! Your account is waiting for staff approval.";

                $message_type = "success";

            } else {

                $message =
                    "Registration failed. Please try again.";

                $message_type = "error";
            }

            $stmt->close();
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
        Resident Registration
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

                <a href="login.php">
                    Login
                </a>

            </div>

        </div>

    </nav>


    <main class="container">


        <section class="form-card">

            <h1>
                Create Resident Account
            </h1>

            <p>
                Register your account to request and track
                barangay service appointments online.
            </p>


            <?php if ($message !== ""): ?>

                <div class="message">

                    <?php echo e($message); ?>

                </div>

            <?php endif; ?>


            <form method="POST">


                <h2>
                    Personal Information
                </h2>


                <label for="full_name">
                    Full Name *
                </label>

                <input
                    type="text"
                    id="full_name"
                    name="full_name"
                    required
                >


                <label for="email">
                    Email Address *
                </label>

                <input
                    type="email"
                    id="email"
                    name="email"
                    required
                >


                <label for="password">
                    Password *
                </label>

                <div class="password-wrapper">

                    <input
                        type="password"
                        id="password"
                        name="password"
                        minlength="6"
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


                <label for="contact_number">
                    Contact Number *
                </label>

                <input
                    type="text"
                    id="contact_number"
                    name="contact_number"
                    required
                >


                <br>


                <h2>
                    Address Information
                </h2>


                <label for="house_unit_no">
                    House / Unit No. *
                </label>

                <input
                    type="text"
                    id="house_unit_no"
                    name="house_unit_no"
                    required
                >


                <label for="street">
                    Street *
                </label>

                <input
                    type="text"
                    id="street"
                    name="street"
                    required
                >


                <label for="purok">
                    Purok / Sitio *
                </label>

                <input
                    type="text"
                    id="purok"
                    name="purok"
                    placeholder="Example: Tambis, Mangga, Lansones"
                    required
                >


                <label for="barangay">
                    Baranggay *
                </label>

                <input
                    type="text"
                    id="barangay"
                    name="barangay"
                    required
                >


                <label for="city_municipality">
                    City / Municipality *
                </label>

                <input
                    type="text"
                    id="city_municipality"
                    name="city_municipality"
                    required
                >


                <label for="province">
                    Province *
                </label>

                <input
                    type="text"
                    id="province"
                    name="province"
                    required
                >


                <br>


                <button type="submit">
                    Create Account
                </button>


            </form>


            <br>


            <p>
                Already have an account?
                <a href="login.php">
                    Login here
                </a>
            </p>


        </section>


    </main>


    <footer class="footer">

        <p>
            Barangay Appointment Management System
        </p>


    </footer>


    <script>

    function togglePassword(inputId, button) {

        const input = document.getElementById(inputId);

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