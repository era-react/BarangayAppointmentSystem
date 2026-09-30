<?php

require_once "config.php";

require_login();

$user_id = $_SESSION["user_id"];


/*
|--------------------------------------------------------------------------
| Get Logged-In User
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare(
    "SELECT
        full_name,
        email,
        contact_number,
        house_unit_no,
        street,
        purok,
        barangay,
        city_municipality,
        province,
        role,
        status,
        created_at
     FROM users
     WHERE id = ?"
);

$stmt->bind_param(
    "i",
    $user_id
);

$stmt->execute();

$result = $stmt->get_result();

$user = $result->fetch_assoc();

$stmt->close();


if (!$user) {

    die("User account could not be found.");

}


/*
|--------------------------------------------------------------------------
| User Information
|--------------------------------------------------------------------------
*/

$full_name = $user["full_name"];
$email = $user["email"];
$contact_number = $user["contact_number"];

$house_unit_no = $user["house_unit_no"];
$street = $user["street"];
$purok = $user["purok"];
$barangay = $user["barangay"];
$city_municipality = $user["city_municipality"];
$province = $user["province"];

$role = $user["role"];
$status = $user["status"];
$created_at = $user["created_at"];


/*
|--------------------------------------------------------------------------
| Initial
|--------------------------------------------------------------------------
*/

$initial = strtoupper(
    substr(
        $full_name,
        0,
        1
    )
);


/*
|--------------------------------------------------------------------------
| Display Values
|--------------------------------------------------------------------------
*/

$display_role = ucfirst($role);

$display_created_at = date(
    "F d, Y",
    strtotime($created_at)
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

    <title>
        My Profile
    </title>

    <link
        rel="stylesheet"
        href="assets/style.css"
    >

</head>

<body>


<?php if ($role === "staff" || $role === "admin"): ?>


    <!-- =========================================================
         STAFF / ADMIN PROFILE
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


        <?php if ($role === "admin"): ?>

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

            <a
                href="profile.php"
                class="active"
            >
                Profile
            </a>


            <a href="change_password.php">
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


    <!-- =========================================================
         STAFF / ADMIN MAIN CONTENT
         ========================================================= -->

    <div class="staff-layout">

        <main class="container">


            <!-- Top Account Area -->

            <div class="staff-topbar">

                <a
                    href="profile.php"
                    class="staff-account"
                    aria-label="Open profile"
                >

                    <div class="staff-account-avatar">

                        <?php echo e($initial); ?>

                    </div>


                    <div class="staff-account-info">

                        <p class="staff-account-name">

                            <?php echo e($full_name); ?>

                        </p>


                        <p class="staff-account-role">

                            <?php echo e($display_role); ?>

                        </p>

                    </div>

                </a>

            </div>


            <!-- Page Header -->

            <div class="staff-page-header">

                <h1>
                    My Profile
                </h1>

                <p>
                    View your account and personal information.
                </p>

            </div>


            <!-- PERSONAL INFORMATION -->

            <section class="card profile-card">

                <h2>
                    Personal Information
                </h2>


                <div class="profile-grid">


                    <div class="profile-item">

                        <span class="profile-label">
                            Full Name
                        </span>

                        <span class="profile-value">

                            <?php
                            echo e($full_name);
                            ?>

                        </span>

                    </div>


                    <div class="profile-item">

                        <span class="profile-label">
                            Email Address
                        </span>

                        <span class="profile-value">

                            <?php
                            echo e($email);
                            ?>

                        </span>

                    </div>


                    <div class="profile-item">

                        <span class="profile-label">
                            Contact Number
                        </span>

                        <span class="profile-value">

                            <?php

                            echo e(
                                $contact_number
                                ?: "Not provided"
                            );

                            ?>

                        </span>

                    </div>


                </div>

            </section>


            <!-- ACCOUNT INFORMATION -->

            <section class="card profile-card">

                <h2>
                    Account Information
                </h2>


                <div class="profile-grid">


                    <div class="profile-item">

                        <span class="profile-label">
                            Account Role
                        </span>

                        <span class="profile-value">

                            <?php
                            echo e($display_role);
                            ?>

                        </span>

                    </div>


                    <div class="profile-item">

                        <span class="profile-label">
                            Account Status
                        </span>

                        <span class="profile-value">

                            <span class="profile-status">

                                <?php
                                echo e($status);
                                ?>

                            </span>

                        </span>

                    </div>


                    <div class="profile-item">

                        <span class="profile-label">
                            Account Created
                        </span>

                        <span class="profile-value">

                            <?php
                            echo e($display_created_at);
                            ?>

                        </span>

                    </div>


                </div>

            </section>


            <!-- ACCOUNT ACTIONS -->

            <section class="card profile-card">

                <h2>
                    Account Actions
                </h2>


                <p class="profile-description">

                    Manage your account security or return
                    to the staff management dashboard.

                </p>


                <div class="profile-actions">


                    <a
                        href="change_password.php"
                        class="button"
                    >
                        Change Password
                    </a>


                    <a
                        href="staff/dashboard.php"
                        class="button secondary-button"
                    >
                        Back to Dashboard
                    </a>


                    <a
                        href="logout.php"
                        class="button secondary-button"
                    >
                        Logout
                    </a>


                </div>

            </section>


        </main>


        <footer class="footer">

            <p>
                Barangay Appointment Management System
            </p>

            <p>
                Staff Management Panel
            </p>

        </footer>

    </div>


<?php else: ?>


    <!-- =========================================================
         RESIDENT PROFILE WITH SIDEBAR
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

            <a
                href="profile.php"
                class="active"
            >
                My Account
            </a>


            <a href="change_password.php">
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


    <!-- =========================================================
         RESIDENT PROFILE MAIN CONTENT
         ========================================================= -->

    <div class="staff-layout">

        <main class="container">


            <!-- Top Account Area -->

            <div class="staff-topbar">

                <a
                    href="profile.php"
                    class="staff-account"
                    aria-label="Open profile"
                >

                    <div class="staff-account-avatar">

                        <?php
                        echo e($initial);
                        ?>

                    </div>


                    <div class="staff-account-info">

                        <p class="staff-account-name">

                            <?php
                            echo e($full_name);
                            ?>

                        </p>


                        <p class="staff-account-role">

                            Resident

                        </p>

                    </div>

                </a>

            </div>


            <!-- Page Header -->

            <div class="staff-page-header">

                <h1>
                    My Profile
                </h1>

                <p>
                    View your account and personal information.
                </p>

            </div>


            <!-- PERSONAL INFORMATION -->

            <section class="card profile-card">

                <h2>
                    Personal Information
                </h2>


                <div class="profile-grid">


                    <div class="profile-item">

                        <span class="profile-label">
                            Full Name
                        </span>

                        <span class="profile-value">

                            <?php
                            echo e($full_name);
                            ?>

                        </span>

                    </div>


                    <div class="profile-item">

                        <span class="profile-label">
                            Email Address
                        </span>

                        <span class="profile-value">

                            <?php
                            echo e($email);
                            ?>

                        </span>

                    </div>


                    <div class="profile-item">

                        <span class="profile-label">
                            Contact Number
                        </span>

                        <span class="profile-value">

                            <?php

                            echo e(
                                $contact_number
                                ?: "Not provided"
                            );

                            ?>

                        </span>

                    </div>


                </div>

            </section>


            <!-- ADDRESS INFORMATION -->

            <section class="card profile-card">

                <h2>
                    Address Information
                </h2>


                <div class="profile-grid">


                    <div class="profile-item">

                        <span class="profile-label">
                            House / Unit No.
                        </span>

                        <span class="profile-value">

                            <?php

                            echo e(
                                $house_unit_no
                                ?: "Not provided"
                            );

                            ?>

                        </span>

                    </div>


                    <div class="profile-item">

                        <span class="profile-label">
                            Street
                        </span>

                        <span class="profile-value">

                            <?php

                            echo e(
                                $street
                                ?: "Not provided"
                            );

                            ?>

                        </span>

                    </div>


                    <div class="profile-item">

                        <span class="profile-label">
                            Purok / Sitio
                        </span>

                        <span class="profile-value">

                            <?php

                            echo e(
                                $purok
                                ?: "Not provided"
                            );

                            ?>

                        </span>

                    </div>


                    <div class="profile-item">

                        <span class="profile-label">
                            Barangay
                        </span>

                        <span class="profile-value">

                            <?php

                            echo e(
                                $barangay
                                ?: "Not provided"
                            );

                            ?>

                        </span>

                    </div>


                    <div class="profile-item">

                        <span class="profile-label">
                            City / Municipality
                        </span>

                        <span class="profile-value">

                            <?php

                            echo e(
                                $city_municipality
                                ?: "Not provided"
                            );

                            ?>

                        </span>

                    </div>


                    <div class="profile-item">

                        <span class="profile-label">
                            Province
                        </span>

                        <span class="profile-value">

                            <?php

                            echo e(
                                $province
                                ?: "Not provided"
                            );

                            ?>

                        </span>

                    </div>


                </div>

            </section>


            <!-- ACCOUNT INFORMATION -->

            <section class="card profile-card">

                <h2>
                    Account Information
                </h2>


                <div class="profile-grid">


                    <div class="profile-item">

                        <span class="profile-label">
                            Account Role
                        </span>

                        <span class="profile-value">
                            Resident
                        </span>

                    </div>


                    <div class="profile-item">

                        <span class="profile-label">
                            Account Status
                        </span>

                        <span class="profile-value">

                            <span class="profile-status">

                                <?php
                                echo e($status);
                                ?>

                            </span>

                        </span>

                    </div>


                    <div class="profile-item">

                        <span class="profile-label">
                            Account Created
                        </span>

                        <span class="profile-value">

                            <?php
                            echo e($display_created_at);
                            ?>

                        </span>

                    </div>


                </div>

            </section>


            <!-- ACCOUNT ACTIONS -->

            <section class="card profile-card">

                <h2>
                    Account Actions
                </h2>


                <p class="profile-description">

                    Manage your account security or return
                    to the resident dashboard.

                </p>


                <div class="profile-actions">


                    <a
                        href="change_password.php"
                        class="button"
                    >
                        Change Password
                    </a>


                    <a
                        href="resident/dashboard.php"
                        class="button secondary-button"
                    >
                        Back to Dashboard
                    </a>


                    <a
                        href="logout.php"
                        class="button secondary-button"
                    >
                        Logout
                    </a>


                </div>

            </section>


        </main>


        <footer class="footer">

            <p>
                Barangay Appointment Management System
            </p>

            <p>
                Resident Portal
            </p>

        </footer>

    </div>


    <!-- RESIDENT CONTACT SCRIPT -->

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