<?php

require_once "../config.php";

require_staff();

$role = $_SESSION["role"];
$full_name = $_SESSION["full_name"];

$is_admin = ($role === "admin");

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
        <?php
        echo $is_admin
            ? "Administration Overview"
            : "Operations Overview";
        ?>
        - Barangay Appointment Management System
    </title>

    <link
        rel="stylesheet"
        href="../assets/style.css"
    >

</head>

<body>

<div class="sidebar">

    <div class="sidebar-brand">

        <h2>
            Barangay Appointment
            Management System
        </h2>

        <p>
            <?php
            echo $is_admin
                ? "Administration Panel"
                : "Staff Management Panel";
            ?>
        </p>

    </div>


    <div class="sidebar-section-title">
        Main
    </div>

    <div class="sidebar-menu">

        <a
            href="dashboard.php"
            class="active"
        >
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

    </div>


    <?php if ($is_admin): ?>

        <div class="sidebar-section-title">
            Administration
        </div>

        <div class="sidebar-menu">

            <a href="staff_accounts.php">
                Staff Accounts
            </a>

        </div>

    <?php endif; ?>


    <div class="sidebar-section-title">
        Account
    </div>

    <div class="sidebar-menu">

        <a href="../profile.php">
            Profile
        </a>

        <a href="../change_password.php">
            Change Password
        </a>

        <a
            href="../logout.php"
            class="logout-link"
        >
            Logout
        </a>

    </div>

</div>


<div class="staff-layout">

    <div class="container">


        <!-- TOP RIGHT ACCOUNT -->

        <div class="staff-topbar">

            <a
                href="../profile.php"
                class="staff-account"
                aria-label="Open profile"
            >

                <div class="staff-account-avatar">

                    <?php

                    echo strtoupper(
                        substr(
                            $full_name,
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
                            $full_name
                        );

                        ?>

                    </p>

                    <p class="staff-account-role">

                        <?php

                        echo ucfirst(
                            e($role)
                        );

                        ?>

                    </p>

                </div>

            </a>

        </div>



        <!-- PAGE HEADER -->

        <div class="staff-page-header">

            <h1>

                <?php if ($is_admin): ?>

                    Administration Overview

                <?php else: ?>

                    Operations Overview

                <?php endif; ?>

            </h1>


            <p>

                <?php if ($is_admin): ?>

                    Manage the configuration and daily operations
                    of the barangay appointment system.

                <?php else: ?>

                    Manage resident appointments and barangay
                    service processing.

                <?php endif; ?>

            </p>

        </div>



        <!-- MANAGEMENT SECTION -->

        <h2 class="section-title">
            System Management
        </h2>


        <div class="dashboard-grid">


            <!-- RESIDENT ACCOUNTS -->

            <div class="dashboard-card">

                <h3>
                    Resident Accounts
                </h3>

                <p>
                    Review resident registrations and manage
                    account approval or rejection.
                </p>

                <a
                    href="residents.php"
                    class="button"
                >
                    Manage Residents
                </a>

            </div>



            <!-- SERVICES -->

            <div class="dashboard-card">

                <h3>
                    Services
                </h3>

                <p>
                    Manage the barangay services available
                    for appointment requests.
                </p>

                <a
                    href="service.php"
                    class="button"
                >
                    Manage Services
                </a>

            </div>



            <!-- SCHEDULES -->

            <div class="dashboard-card">

                <h3>
                    Schedules
                </h3>

                <p>
                    Create and manage appointment schedules,
                    available slots, and schedule availability.
                </p>

                <a
                    href="schedule.php"
                    class="button"
                >
                    Manage Schedules
                </a>

            </div>



            <!-- APPOINTMENTS -->

            <div class="dashboard-card">

                <h3>
                    Appointments
                </h3>

                <p>
                    Review appointment requests and manage
                    their current processing status.
                </p>

                <a
                    href="appointments.php"
                    class="button"
                >
                    View Appointments
                </a>

            </div>



            <!-- ASSISTED BOOKING -->

            <div class="dashboard-card">

                <h3>
                    Assisted Booking
                </h3>

                <p>
                    Create an appointment on behalf of a resident
                    who needs assistance from barangay staff.
                </p>

                <a
                    href="book_for_resident.php"
                    class="button"
                >
                    Create Appointment
                </a>

            </div>



            <!-- SERVICE PROCESSING -->

            <div class="dashboard-card">

                <h3>
                    Service Processing
                </h3>

                <p>
                    Process checked-in appointments and record
                    the completion of requested barangay services.
                </p>

                <a
                    href="process_service.php"
                    class="button"
                >
                    Process Services
                </a>

            </div>



            <?php if ($is_admin): ?>

                <!-- STAFF ACCOUNTS -->

                <div class="dashboard-card">

                    <h3>
                        Staff Accounts
                    </h3>

                    <p>
                        Create and manage staff accounts that
                        have access to the management panel.
                    </p>

                    <a
                        href="staff_accounts.php"
                        class="button"
                    >
                        Manage Staff
                    </a>

                </div>

            <?php endif; ?>


        </div>



        <!-- WORKFLOW SECTION -->

        <div class="card">

            <h2>
                Appointment Workflow
            </h2>

            <p>
                The system follows a controlled appointment
                process from resident request through service
                completion and claiming.
            </p>


            <div class="dashboard-workflow">

                <div class="workflow-step">

                    <strong>
                        1. Request
                    </strong>

                    <span>
                        Resident submits an appointment request.
                    </span>

                </div>


                <div class="workflow-arrow">
                    →
                </div>


                <div class="workflow-step">

                    <strong>
                        2. Approval
                    </strong>

                    <span>
                        Staff reviews and approves or rejects
                        the appointment.
                    </span>

                </div>


                <div class="workflow-arrow">
                    →
                </div>


                <div class="workflow-step">

                    <strong>
                        3. Check-In
                    </strong>

                    <span>
                        Staff records the resident's arrival.
                    </span>

                </div>


                <div class="workflow-arrow">
                    →
                </div>


                <div class="workflow-step">

                    <strong>
                        4. Processing
                    </strong>

                    <span>
                        Staff processes the requested barangay service.
                    </span>

                </div>


                <div class="workflow-arrow">
                    →
                </div>


                <div class="workflow-step">

                    <strong>
                        5. Completion
                    </strong>

                    <span>
                        Staff records the service as completed.
                    </span>

                </div>


                <div class="workflow-arrow">
                    →
                </div>


                <div class="workflow-step">

                    <strong>
                        6. Claiming
                    </strong>

                    <span>
                        Resident receives the completed service output.
                    </span>

                </div>

            </div>

        </div>



        <!-- ROLE INFORMATION -->

        <div class="card">

            <h2>
                Current Access
            </h2>

            <p>

                <?php if ($is_admin): ?>

                    You are signed in as an administrator.
                    Administrative functions are available,
                    including staff account management.

                <?php else: ?>

                    You are signed in as staff.
                    Your access is focused on resident accounts,
                    services, schedules, appointments, and
                    service processing.

                <?php endif; ?>

            </p>

        </div>


    </div>


    <!-- FOOTER IS NOW OUTSIDE THE CONTAINER -->

    <footer class="site-footer">

        <p>
            Barangay Appointment Management System
        </p>

        <p>
            <?php
            echo $is_admin
                ? "Administration Panel"
                : "Staff Management Panel";
            ?>
        </p>

    </footer>


</div>


</body>

</html>