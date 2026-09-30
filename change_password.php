<?php

require_once "config.php";

require_login();

$message = "";
$message_type = "";


/*
|--------------------------------------------------------------------------
| Change Password
|--------------------------------------------------------------------------
*/

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $current_password = $_POST["current_password"];
    $new_password = $_POST["new_password"];
    $confirm_password = $_POST["confirm_password"];


    if (
        $current_password === "" ||
        $new_password === "" ||
        $confirm_password === ""
    ) {

        $message =
            "Please complete all password fields.";

        $message_type =
            "error";


    } elseif (
        $new_password !==
        $confirm_password
    ) {

        $message =
            "New passwords do not match.";

        $message_type =
            "error";


    } elseif (
        strlen($new_password) < 6
    ) {

        $message =
            "New password must be at least 6 characters.";

        $message_type =
            "error";


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

        $result =
            $stmt->get_result();

        $user =
            $result->fetch_assoc();

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

                $message_type =
                    "success";

            } else {

                $message =
                    "Failed to change password.";

                $message_type =
                    "error";
            }


            $stmt->close();


        } else {

            $message =
                "Current password is incorrect.";

            $message_type =
                "error";
        }

    }

}


/*
|--------------------------------------------------------------------------
| Current User Role
|--------------------------------------------------------------------------
*/

$current_role =
    $_SESSION["role"];

$is_resident =
    ($current_role === "resident");

$is_staff =
    ($current_role === "staff");

$is_admin =
    ($current_role === "admin");


/*
|--------------------------------------------------------------------------
| Page URLs
|--------------------------------------------------------------------------
*/

if ($is_resident) {

    $dashboard_url =
        "resident/dashboard.php";

    $profile_url =
        "profile.php";

    $logout_url =
        "logout.php";

} else {

    $dashboard_url =
        "staff/dashboard.php";

    $profile_url =
        "profile.php";

    $logout_url =
        "logout.php";

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
        href="assets/style.css"
    >

    <style>

        /* =========================================
           CHANGE PASSWORD
        ========================================= */

        .change-password-card {
            max-width: 620px;
            margin: 0 auto;
        }

        .change-password-card h2 {
            color: #174d35;
            margin-top: 0;
            margin-bottom: 8px;
        }

        .change-password-card > p {
            color: #68756e;
            font-size: 14px;
            line-height: 1.6;
            margin-bottom: 24px;
        }

        .change-password-form {
            width: 100%;
        }

        .change-password-form label {
            display: block;
            margin-top: 18px;
            margin-bottom: 7px;
            font-weight: 600;
            color: #35413b;
        }

        .change-password-form label:first-child {
            margin-top: 0;
        }

        .change-password-form .password-wrapper {
            width: 100%;
        }

        .change-password-form
        .password-wrapper input {
            width: 100%;
            box-sizing: border-box;
        }

        .change-password-actions {
            display: flex;
            gap: 10px;
            align-items: center;
            flex-wrap: wrap;
            margin-top: 24px;
        }

        .change-password-note {
            margin-top: 22px;
            padding: 14px 16px;
            background: #f4f8f5;
            border: 1px solid #dfe9e2;
            border-radius: 8px;
            color: #5f6b65;
            font-size: 13px;
            line-height: 1.6;
        }


        /* =========================================
           CHANGE PASSWORD PAGE HEADER
        ========================================= */

        .change-password-page-header {
            padding: 4px 0 14px;
            margin-bottom: 28px;
            border-bottom: 1px solid #dfe7e2;
        }

        .change-password-page-header h1 {
            margin: 0 0 5px;
            color: #174d35;
            font-size: 30px;
            font-weight: 700;
        }

        .change-password-page-header p {
            margin: 0;
            color: #68756e;
            font-size: 15px;
            line-height: 1.4;
        }


        /* =========================================
           RESIDENT CONTACT IN SIDEBAR
        ========================================= */

        .resident-sidebar-contact {
            margin: 2px 9px 8px;
            padding: 14px;
            background: #123c29;
            border: 1px solid rgba(255, 255, 255, 0.14);
            border-radius: 8px;
        }

        .resident-sidebar-contact-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
        }

        .resident-sidebar-contact-header strong {
            color: #ffffff;
            font-size: 12px;
            line-height: 1.3;
        }

        .resident-sidebar-contact-close {
            width: 28px;
            height: 28px;
            padding: 0;
            border: none;
            background: transparent;
            color: #ffffff;
            font-size: 20px;
            line-height: 1;
            cursor: pointer;
            box-shadow: none;
        }

        .resident-sidebar-contact-close:hover {
            background: rgba(255, 255, 255, 0.10);
            color: #ffffff;
            transform: none;
            box-shadow: none;
        }

        .resident-sidebar-contact > p {
            margin: 10px 0 12px;
            color: #d7e8de;
            font-size: 11px;
            line-height: 1.5;
        }

        .resident-sidebar-contact-item {
            padding: 9px 0;
            border-top: 1px solid rgba(255, 255, 255, 0.10);
        }

        .resident-sidebar-contact-item span {
            display: block;
            margin-bottom: 3px;
            color: #a9c6b6;
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
        }

        .resident-sidebar-contact-item strong {
            display: block;
            color: #ffffff;
            font-size: 11px;
            line-height: 1.4;
            overflow-wrap: anywhere;
        }

    </style>

</head>


<body>


<?php if ($is_resident): ?>


    <!-- =========================================================
         RESIDENT SIDEBAR
         ========================================================= -->

    <aside class="sidebar">

        <div class="sidebar-brand">

            <h2>
                Barangay Appointment
            </h2>

            <p>
                Management System
            </p>

        </div>


        <div class="sidebar-section-title">
            Main
        </div>


        <div class="sidebar-menu">

            <a href="resident/dashboard.php">
                Dashboard
            </a>

        </div>


        <div class="sidebar-section-title">
            Account
        </div>


        <div class="sidebar-menu">

            <a href="profile.php">
                My Account
            </a>


            <a
                href="change_password.php"
                class="active"
            >
                Change Password
            </a>


            <button
                type="button"
                class="sidebar-action-button"
                id="contactOfficeButton"
                aria-expanded="false"
                aria-controls="residentSidebarContact"
            >
                Contact Office
            </button>


            <div
                class="resident-sidebar-contact"
                id="residentSidebarContact"
                hidden
            >

                <div class="resident-sidebar-contact-header">

                    <strong>
                        Contact Barangay Office
                    </strong>


                    <button
                        type="button"
                        class="resident-sidebar-contact-close"
                        id="closeContactOffice"
                        aria-label="Close contact information"
                    >
                        ×
                    </button>

                </div>


                <p>
                    For questions about your appointment or barangay service,
                    please contact the barangay office.
                </p>


                <div class="resident-sidebar-contact-item">

                    <span>
                        Phone
                    </span>

                    <strong>
                        0917 123 4567
                    </strong>

                </div>


                <div class="resident-sidebar-contact-item">

                    <span>
                        Email
                    </span>

                    <strong>
                        barangayoffice@example.com
                    </strong>

                </div>


                <div class="resident-sidebar-contact-item">

                    <span>
                        Office Hours
                    </span>

                    <strong>
                        Monday–Friday, 8:00 AM–5:00 PM
                    </strong>

                </div>


                <div class="resident-sidebar-contact-item">

                    <span>
                        Address
                    </span>

                    <strong>
                        Barangay Office, Sample Barangay, Cebu City, Cebu
                    </strong>

                </div>

            </div>


            <a
                href="logout.php"
                class="logout-link"
            >
                Logout
            </a>

        </div>

    </aside>


<?php else: ?>


    <!-- =========================================================
         STAFF / ADMIN SIDEBAR
         ========================================================= -->

    <aside class="sidebar">

        <div class="sidebar-brand">

            <h2>
                Barangay Appointment
            </h2>

            <p>
                Management System
            </p>

        </div>


        <div class="sidebar-section-title">
            Main
        </div>


        <div class="sidebar-menu">

            <a href="staff/dashboard.php">
                Dashboard
            </a>

            <a href="staff/residents.php">
                Resident Accounts
            </a>

            <a href="staff/service.php">
                Services
            </a>

            <a href="staff/schedule.php">
                Schedules
            </a>

            <a href="staff/appointments.php">
                Appointments
            </a>

            <a href="staff/book_for_resident.php">
                Assisted Booking
            </a>

            <a href="staff/process_service.php">
                Service Processing
            </a>

        </div>


        <?php if ($is_admin): ?>

            <div class="sidebar-section-title">
                Administration
            </div>


            <div class="sidebar-menu">

                <a href="staff/staff_accounts.php">
                    Staff Accounts
                </a>

            </div>

        <?php endif; ?>


        <div class="sidebar-section-title">
            Account
        </div>


        <div class="sidebar-menu">

            <a href="profile.php">
                Profile
            </a>


            <a
                href="change_password.php"
                class="active"
            >
                Change Password
            </a>


            <a
                href="logout.php"
                class="logout-link"
            >
                Logout
            </a>

        </div>

    </aside>


<?php endif; ?>



<!-- =========================================================
     MAIN CONTENT
========================================================= -->

<div class="staff-layout">

    <main class="container">


        <!-- TOP RIGHT ACCOUNT -->

        <div class="staff-topbar">

            <a
                href="profile.php"
                class="staff-account"
                aria-label="Open profile"
            >

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

            </a>

        </div>



        <!-- PAGE HEADER -->

        <div class="change-password-page-header">

            <h1>
                Change Password
            </h1>

            <p>
                Update your account password and keep your account secure.
            </p>

        </div>



        <!-- PASSWORD FORM -->

        <section class="card change-password-card">

            <h2>
                Account Security
            </h2>

            <p>
                Enter your current password and create a new password
                for your account.
            </p>


            <?php if ($message !== ""): ?>

                <div class="message">

                    <?php
                    echo e($message);
                    ?>

                </div>

            <?php endif; ?>


            <form
                method="POST"
                class="change-password-form"
            >


                <!-- CURRENT PASSWORD -->

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

                            <path
                                d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12z"
                            ></path>

                            <circle
                                cx="12"
                                cy="12"
                                r="3"
                            ></circle>

                        </svg>

                    </button>

                </div>



                <!-- NEW PASSWORD -->

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

                            <path
                                d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12z"
                            ></path>

                            <circle
                                cx="12"
                                cy="12"
                                r="3"
                            ></circle>

                        </svg>

                    </button>

                </div>



                <!-- CONFIRM PASSWORD -->

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

                            <path
                                d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12z"
                            ></path>

                            <circle
                                cx="12"
                                cy="12"
                                r="3"
                            ></circle>

                        </svg>

                    </button>

                </div>



                <div class="change-password-actions">

                    <button type="submit">
                        Change Password
                    </button>


                    <a
                        href="<?php echo e($dashboard_url); ?>"
                        class="button button-secondary"
                    >
                        Back to Dashboard
                    </a>

                </div>


            </form>


            <div class="change-password-note">

                Passwords must contain at least 6 characters.

            </div>


        </section>


    </main>


    <!-- FOOTER -->

    <footer class="site-footer">

        <p>
            Barangay Appointment Management System
        </p>

        <p>

            <?php if ($is_resident): ?>

                Resident Portal

            <?php else: ?>

                Account Security

            <?php endif; ?>

        </p>

    </footer>

</div>



<!-- =========================================================
     PASSWORD TOGGLE
========================================================= -->

<script>

function togglePassword(
    inputId,
    button
) {

    const input =
        document.getElementById(
            inputId
        );


    if (
        input.type === "password"
    ) {

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
                    d="M3 3l18 18"
                ></path>

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
            "Show password"
        );

    }

}

</script>



<?php if ($is_resident): ?>

<script>

    const contactOfficeButton =
        document.getElementById(
            "contactOfficeButton"
        );

    const residentSidebarContact =
        document.getElementById(
            "residentSidebarContact"
        );

    const closeContactOffice =
        document.getElementById(
            "closeContactOffice"
        );


    contactOfficeButton.addEventListener(
        "click",
        function () {

            const isOpen =
                !residentSidebarContact.hasAttribute(
                    "hidden"
                );


            if (isOpen) {

                residentSidebarContact.setAttribute(
                    "hidden",
                    ""
                );

                contactOfficeButton.setAttribute(
                    "aria-expanded",
                    "false"
                );

            } else {

                residentSidebarContact.removeAttribute(
                    "hidden"
                );

                contactOfficeButton.setAttribute(
                    "aria-expanded",
                    "true"
                );

            }

        }
    );


    closeContactOffice.addEventListener(
        "click",
        function () {

            residentSidebarContact.setAttribute(
                "hidden",
                ""
            );

            contactOfficeButton.setAttribute(
                "aria-expanded",
                "false"
            );

        }
    );

</script>

<?php endif; ?>


</body>

</html>