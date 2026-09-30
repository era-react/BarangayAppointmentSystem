<?php
require_once "config.php";
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Barangay Appointment Management System</title>

    <link
        rel="stylesheet"
        href="assets/style.css"
    >

</head>

<body>

    <!-- Navigation -->

    <nav class="navbar">

        <div class="navbar-content">

            <div class="navbar-title">
                Barangay Appointment Management System
            </div>

            <div>

                <a href="login.php">
                    Login
                </a>

                <a href="register.php">
                    Register
                </a>

            </div>

        </div>

    </nav>


    <!-- Main Content -->

    <main class="container">

        <section class="hero">

            <h1>
                Welcome to the Barangay Appointment Management System
            </h1>

            <p>
                Request an appointment for barangay services
                without having to visit the barangay office
                just to make an appointment.
            </p>

            <p>
                Residents can view available schedules,
                request appointments, and track their
                appointment status online.
            </p>

            <br>

            <a
                href="register.php"
                class="button"
            >
                Create Resident Account
            </a>

            &nbsp;

            <a
                href="login.php"
                class="button button-secondary"
            >
                Login
            </a>

        </section>


        <!-- Services -->

        <!-- Services -->

<section class="section">

    <h2 class="section-title">
        Available Barangay Services
    </h2>

    <div class="service-grid">

        <div class="service-card">

            <h3>
                Barangay Clearance
            </h3>

            <p>
                Request an appointment for
                Barangay Clearance processing.
            </p>

        </div>


        <div class="service-card">

            <h3>
                Barangay Business Clearance
            </h3>

            <p>
                Request an appointment for
                barangay business clearance processing.
            </p>

        </div>


        <div class="service-card">

            <h3>
                Certificate of Indigency
            </h3>

            <p>
                Request an appointment for
                Certificate of Indigency processing.
            </p>

        </div>


        <div class="service-card">

            <h3>
                Certificate of Residency
            </h3>

            <p>
                Request an appointment for
                Certificate of Residency processing.
            </p>

        </div>


        <div class="service-card">

            <h3>
                Death Certification
            </h3>

            <p>
                Request an appointment for
                Death Certification processing.
            </p>

        </div>


        <div class="service-card">

            <h3>
                Barangay Certification of Common Law
            </h3>

            <p>
                Request an appointment for
                Barangay Certification of Common Law processing.
            </p>

        </div>

    </div>

</section>


        <!-- How It Works -->

        <section class="card">

            <h2>
                How It Works
            </h2>

            <div class="dashboard-grid">

                <div class="dashboard-card">

                    <h3>
                        1. Register
                    </h3>

                    <p>
                        Create your resident account
                        and wait for staff approval.
                    </p>

                </div>


                <div class="dashboard-card">

                    <h3>
                        2. Request Appointment
                    </h3>

                    <p>
                        Select a barangay service
                        and an available schedule.
                    </p>

                </div>


                <div class="dashboard-card">

                    <h3>
                        3. Track Status
                    </h3>

                    <p>
                        Check the current status of your appointment online.
                    </p>

                </div>

            </div>

        </section>

    </main>


    <!-- Footer -->

    <footer class="footer">

        <p>
            Barangay Appointment Management System
        </p>

    </footer>

</body>

</html>