<?php

require_once "../config.php";
require_admin();

$message = "";


/*
|--------------------------------------------------------------------------
| Create a New Staff Account
|--------------------------------------------------------------------------
*/

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $full_name = trim($_POST["full_name"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";
    $contact_number = trim($_POST["contact_number"] ?? "");


    if (
        $full_name === "" ||
        $email === "" ||
        $password === ""
    ) {

        $message =
            "Please fill in all required fields.";

    } elseif (
        !filter_var(
            $email,
            FILTER_VALIDATE_EMAIL
        )
    ) {

        $message =
            "Please enter a valid email address.";

    } elseif (
        strlen($password) < 6
    ) {

        $message =
            "Password must be at least 6 characters long.";

    } else {

        /*
        |--------------------------------------------------------------------------
        | Check Existing Email
        |--------------------------------------------------------------------------
        */

        $check = $conn->prepare(
            "SELECT id
             FROM users
             WHERE email = ?
             LIMIT 1"
        );

        $check->bind_param(
            "s",
            $email
        );

        $check->execute();

        $result =
            $check->get_result();


        if ($result->num_rows > 0) {

            $message =
                "That email address is already registered.";

        } else {

            /*
            |--------------------------------------------------------------------------
            | Secure Password Hash
            |--------------------------------------------------------------------------
            */

            $password_hash =
                password_hash(
                    $password,
                    PASSWORD_DEFAULT
                );


            /*
            |--------------------------------------------------------------------------
            | Create Staff Account
            |--------------------------------------------------------------------------
            */

            $stmt = $conn->prepare(
                "INSERT INTO users
                (
                    full_name,
                    email,
                    password_hash,
                    role,
                    status,
                    contact_number
                )
                VALUES
                (?, ?, ?, 'staff', 'Active', ?)"
            );

            $stmt->bind_param(
                "ssss",
                $full_name,
                $email,
                $password_hash,
                $contact_number
            );


            if ($stmt->execute()) {

                $message =
                    "Staff account created successfully.";

            } else {

                $message =
                    "Something went wrong. Please try again.";
            }


            $stmt->close();
        }


        $check->close();
    }
}


/*
|--------------------------------------------------------------------------
| Get All Staff Accounts
|--------------------------------------------------------------------------
*/

$staff_result = $conn->query(
    "SELECT
        id,
        full_name,
        email,
        contact_number,
        status,
        created_at

     FROM users

     WHERE role = 'staff'

     ORDER BY id DESC"
);

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Staff Accounts</title>

    <link
        rel="stylesheet"
        href="../assets/style.css"
    >

    <style>

        /*
        |--------------------------------------------------------------------------
        | Staff Account Form
        |--------------------------------------------------------------------------
        */

        .staff-account-form-card {
            max-width: 760px;
        }


        .staff-account-form-card h2 {
            margin-top: 0;
        }


        .form-help-text {
            margin-top: -4px;
            margin-bottom: 20px;

            color: #68756e;

            font-size: 13px;
            line-height: 1.5;
        }


        /*
        |--------------------------------------------------------------------------
        | Password Field
        |--------------------------------------------------------------------------
        */

        .password-wrapper {
            position: relative;

            width: 100%;
        }


        .password-wrapper input {
            width: 100%;

            box-sizing: border-box;

            padding-right: 52px;
        }


        .password-toggle {
            position: absolute;

            top: 50%;
            right: 10px;

            width: 38px;
            height: 38px;

            margin: 0;

            padding: 0;

            transform: translateY(-50%) !important;

            display: flex;

            align-items: center;
            justify-content: center;

            background: transparent !important;

            color: #68756e !important;

            border: none !important;

            border-radius: 6px;

            cursor: pointer;

            box-shadow: none !important;
        }


        .password-toggle:hover {
            background: #f1f5f3 !important;

            color: #174d35 !important;

            transform:
                translateY(-50%) !important;
        }


        .password-toggle:focus {
            outline: 2px solid #218653;

            outline-offset: 1px;

            transform:
                translateY(-50%) !important;
        }


        /*
        |--------------------------------------------------------------------------
        | Staff Account Table
        |--------------------------------------------------------------------------
        */

        .staff-table-wrapper {
            width: 100%;
        }


        .staff-table-scroll-top {
            width: 100%;

            overflow-x: auto;
            overflow-y: hidden;

            margin-bottom: 6px;
        }


        .staff-table-scroll-top > div {
            height: 1px;
        }


        .staff-table-container {
            overflow-x: auto;
            overflow-y: visible;
        }


        .staff-table {
            width: 100%;

            min-width: 950px;

            border-collapse: collapse;
        }


        .staff-table th,
        .staff-table td {
            padding: 14px 16px;

            vertical-align: middle;

            white-space: nowrap;
        }


        .staff-table th:nth-child(1),
        .staff-table td:nth-child(1) {
            min-width: 180px;
        }


        .staff-table th:nth-child(2),
        .staff-table td:nth-child(2) {
            min-width: 220px;
        }


        .staff-table th:nth-child(3),
        .staff-table td:nth-child(3) {
            min-width: 160px;
        }


        .staff-table th:nth-child(4),
        .staff-table td:nth-child(4) {
            min-width: 120px;
        }


        .staff-table th:nth-child(5),
        .staff-table td:nth-child(5) {
            min-width: 180px;
        }


        /*
        |--------------------------------------------------------------------------
        | Status
        |--------------------------------------------------------------------------
        */

        .staff-status-active {
            display: inline-block;

            padding: 5px 10px;

            background: #e8f5ed;

            color: #17663d;

            border-radius: 20px;

            font-size: 12px;

            font-weight: 700;
        }


        /*
        |--------------------------------------------------------------------------
        | Admin Information
        |--------------------------------------------------------------------------
        */

        .admin-note {
            margin-bottom: 22px;

            padding: 14px 16px;

            background: #f3f8f5;

            border: 1px solid #dce9e1;

            border-left: 4px solid #218653;

            border-radius: 8px;

            color: #53625a;

            font-size: 14px;

            line-height: 1.6;
        }

    </style>

</head>


<body>


<!--
|--------------------------------------------------------------------------
| Fixed Staff Sidebar
|--------------------------------------------------------------------------
-->

<aside class="sidebar" id="staffSidebar">

    <div class="sidebar-brand">

        <h2>
            Barangay Appointment
        </h2>

        <p>
            Management System
        </p>

    </div>


    <nav class="sidebar-menu">


        <div class="sidebar-section-title">
            Main
        </div>


        <a href="dashboard.php">
            Dashboard
        </a>


        <a href="residents.php">
            Resident Accounts
        </a>


        <a href="service.php">
            Services
        </a>


        <a href="schedule.php">
            Schedules
        </a>


        <a href="appointments.php">
            Appointments
        </a>


        <a href="book_for_resident.php">
            Assisted Booking
        </a>


        <a href="process_service.php">
            Service Processing
        </a>


        <?php if ($_SESSION["role"] === "admin"): ?>

            <div class="sidebar-section-title">
                Administration
            </div>


            <a
                href="staff_accounts.php"
                class="active"
            >
                Staff Accounts
            </a>

        <?php endif; ?>


        <div class="sidebar-section-title">
            Account
        </div>


        <a href="../change_password.php">
            Change Password
        </a>


        <a
            href="../logout.php"
            class="logout-link"
        >
            Logout
        </a>


    </nav>

</aside>


<!--
|--------------------------------------------------------------------------
| Main Layout
|--------------------------------------------------------------------------
-->

<div class="staff-layout">


    <main class="container">


        <!-- Account Area -->

        <div class="staff-topbar">

            <div class="staff-account">


                <div class="staff-account-avatar">

                    <?php

                    echo strtoupper(
                        substr(
                            $_SESSION["full_name"],
                            0,
                            1
                        )
                    );

                    ?>

                </div>


                <div class="staff-account-info">

                    <p class="staff-account-name">

                        <?php

                        echo e(
                            $_SESSION["full_name"]
                        );

                        ?>

                    </p>


                    <p class="staff-account-role">

                        <?php

                        echo ucfirst(
                            e(
                                $_SESSION["role"]
                            )
                        );

                        ?>

                    </p>

                </div>


            </div>

        </div>


        <!-- Page Header -->

        <div class="staff-page-header">

            <h1>
                Staff Accounts
            </h1>

            <p>
                Create and manage authorized barangay staff accounts.
            </p>

        </div>


        <!-- Admin Information -->

        <div class="admin-note">

            <strong>
                Administrator Access:
            </strong>

            This page is available only to authorized administrators
            for creating staff accounts and reviewing existing staff records.

        </div>


        <!-- Create Staff Account -->

        <section class="card staff-account-form-card">


            <h2>
                Create Staff Account
            </h2>


            <p class="form-help-text">
                Create an active account for an authorized barangay staff member.
            </p>


            <?php if ($message !== ""): ?>

                <div class="message">

                    <?php echo e($message); ?>

                </div>

            <?php endif; ?>


            <form method="POST">


                <label for="full_name">
                    Full Name *
                </label>


                <input
                    type="text"
                    id="full_name"
                    name="full_name"
                    placeholder="Enter staff full name"
                    required
                >


                <label for="email">
                    Staff Email *
                </label>


                <input
                    type="email"
                    id="email"
                    name="email"
                    placeholder="Enter staff email"
                    required
                >


                <label for="password">
                    Temporary Password *
                </label>


                <div class="password-wrapper">

                    <input
                        type="password"
                        id="password"
                        name="password"
                        minlength="6"
                        placeholder="Enter temporary password"
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
                    Contact Number
                </label>


                <input
                    type="text"
                    id="contact_number"
                    name="contact_number"
                    placeholder="Enter contact number"
                >


                <button
                    type="submit"
                    class="btn"
                >
                    Create Staff Account
                </button>


            </form>


        </section>


        <!-- Existing Staff Accounts -->

        <section class="card">


            <h2>
                Existing Staff Accounts
            </h2>


            <p class="form-help-text">
                Review the staff accounts currently registered in the system.
            </p>


            <div class="staff-table-wrapper">


                <!-- Top Horizontal Scrollbar -->

                <div class="staff-table-scroll-top">

                    <div></div>

                </div>


                <!-- Staff Table -->

                <div class="table-container staff-table-container">

                    <table class="staff-table">


                        <thead>

                            <tr>

                                <th>
                                    Name
                                </th>

                                <th>
                                    Email
                                </th>

                                <th>
                                    Contact Number
                                </th>

                                <th>
                                    Status
                                </th>

                                <th>
                                    Created
                                </th>

                            </tr>

                        </thead>


                        <tbody>


                            <?php if ($staff_result->num_rows > 0): ?>


                                <?php while ($staff = $staff_result->fetch_assoc()): ?>

                                    <tr>

                                        <td>

                                            <?php

                                            echo e(
                                                $staff["full_name"]
                                            );

                                            ?>

                                        </td>


                                        <td>

                                            <?php

                                            echo e(
                                                $staff["email"]
                                            );

                                            ?>

                                        </td>


                                        <td>

                                            <?php

                                            echo e(
                                                $staff["contact_number"]
                                            );

                                            ?>

                                        </td>


                                        <td>

                                            <?php if ($staff["status"] === "Active"): ?>

                                                <span class="staff-status-active">
                                                    Active
                                                </span>

                                            <?php else: ?>

                                                <?php

                                                echo e(
                                                    $staff["status"]
                                                );

                                                ?>

                                            <?php endif; ?>

                                        </td>


                                        <td>

                                            <?php

                                            echo e(
                                                $staff["created_at"]
                                            );

                                            ?>

                                        </td>

                                    </tr>


                                <?php endwhile; ?>


                            <?php else: ?>


                                <tr>

                                    <td colspan="5">

                                        No staff accounts found.

                                    </td>

                                </tr>


                            <?php endif; ?>


                        </tbody>


                    </table>

                </div>

            </div>


        </section>


    </main>


    <footer class="footer">

        <p>
            Barangay Appointment Management System
        </p>

        <p>
            Staff Accounts
        </p>

    </footer>


</div>


<script>


/*
|--------------------------------------------------------------------------
| Password Visibility Toggle
|--------------------------------------------------------------------------
*/

function togglePassword(
    inputId,
    button
) {

    const input =
        document.getElementById(
            inputId
        );


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


/*
|--------------------------------------------------------------------------
| Top and Bottom Table Scroll Synchronization
|--------------------------------------------------------------------------
*/

const staffTopScroll =
    document.querySelector(
        ".staff-table-scroll-top"
    );


const staffTableContainer =
    document.querySelector(
        ".staff-table-container"
    );


const staffTable =
    document.querySelector(
        ".staff-table"
    );


function updateStaffTopScrollWidth() {

    if (
        !staffTopScroll ||
        !staffTable
    ) {
        return;
    }


    const scrollContent =
        staffTopScroll.querySelector(
            "div"
        );


    scrollContent.style.width =
        staffTable.scrollWidth + "px";

}


if (
    staffTopScroll &&
    staffTableContainer
) {

    staffTopScroll.addEventListener(
        "scroll",
        function () {

            staffTableContainer.scrollLeft =
                staffTopScroll.scrollLeft;

        }
    );


    staffTableContainer.addEventListener(
        "scroll",
        function () {

            staffTopScroll.scrollLeft =
                staffTableContainer.scrollLeft;

        }
    );


    window.addEventListener(
        "resize",
        updateStaffTopScrollWidth
    );


    updateStaffTopScrollWidth();

}

</script>


</body>

</html>