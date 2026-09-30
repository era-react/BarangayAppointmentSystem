<?php

require_once "../config.php";

require_staff();

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $appointment_id = (int) $_POST["appointment_id"];
    $processing_notes = trim($_POST["processing_notes"]);

    if ($appointment_id <= 0) {

        $message = "Invalid appointment.";

    } else {

        $stmt = $conn->prepare(
            "UPDATE appointments
             SET
                status = 'Completed',
                processing_notes = ?
             WHERE id = ?
             AND status = 'Checked In'"
        );

        $stmt->bind_param(
            "si",
            $processing_notes,
            $appointment_id
        );

        if ($stmt->execute()) {

            if ($stmt->affected_rows > 0) {

                $message =
                    "Appointment marked as Completed successfully.";

            } else {

                $message =
                    "Only Checked In appointments can be marked as Completed.";
            }

        } else {

            $message =
                "Failed to update appointment.";
        }

        $stmt->close();
    }
}


$result = $conn->query(
    "SELECT
        appointments.id,
        appointments.notes,
        appointments.processing_notes,

        users.full_name AS resident_name,
        users.email AS resident_email,
        users.contact_number,

        services.name AS service_name,

        schedules.schedule_date,
        schedules.schedule_time

     FROM appointments

     INNER JOIN users
        ON appointments.resident_id = users.id

     INNER JOIN services
        ON appointments.service_id = services.id

     INNER JOIN schedules
        ON appointments.schedule_id = schedules.id

     WHERE appointments.status = 'Checked In'

     ORDER BY
        schedules.schedule_date ASC,
        schedules.schedule_time ASC"
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

    <title>Service Processing</title>

    <link
        rel="stylesheet"
        href="../assets/style.css"
    >

    <style>

        /*
        |--------------------------------------------------------------------------
        | Service Processing Table
        |--------------------------------------------------------------------------
        */

        .processing-table-wrapper {
            width: 100%;
        }


        .processing-table-scroll-top {
            width: 100%;

            overflow-x: auto;
            overflow-y: hidden;

            margin-bottom: 6px;
        }


        .processing-table-scroll-top > div {
            height: 1px;
        }


        .processing-table-container {
            overflow-x: auto;
            overflow-y: visible;
        }


        .processing-table {
            width: 100%;

            min-width: 1100px;

            border-collapse: collapse;
        }


        .processing-table th,
        .processing-table td {
            vertical-align: middle;

            white-space: nowrap;
        }


        .processing-table .resident-column {
            min-width: 220px;

            white-space: normal;

            line-height: 1.5;
        }


        .processing-table .service-column {
            min-width: 190px;

            white-space: normal;

            line-height: 1.5;
        }


        .processing-table .notes-column {
            min-width: 280px;

            white-space: normal;

            line-height: 1.5;
        }


        .processing-table .action-column {
            min-width: 140px;

            white-space: nowrap;
        }


        .processing-form {
    width: 100%;
    min-width: 260px;
    box-sizing: border-box;
}


.processing-form textarea {
    width: 100%;
    min-width: 0;

    box-sizing: border-box;

    resize: vertical;

    padding: 10px 12px;

    border: 1px solid #cccccc;
    border-radius: 6px;

    font-family: inherit;
    font-size: 14px;
    line-height: 1.5;

    background: #ffffff;
    color: #333333;
}


.processing-form textarea:focus {
    outline: none;
    border-color: #218653;
}


.processing-form button {
    display: block;

    width: 100%;
    box-sizing: border-box;

    margin-top: 8px;

    white-space: nowrap;
}

.processing-table .notes-column {
    min-width: 300px;
}


.processing-table .action-column {
    min-width: 150px;
    width: 150px;

    text-align: center;

    white-space: nowrap;
}


.processing-table .action-column .status {
    display: inline-block;
}


        /*
        |--------------------------------------------------------------------------
        | Guide Cards
        |--------------------------------------------------------------------------
        */

        .processing-guide-grid {
            display: grid;

            grid-template-columns:
                repeat(4, minmax(0, 1fr));

            gap: 16px;
        }


        .processing-guide-card {
            background: #f8faf9;

            border: 1px solid #e1e8e4;

            border-radius: 10px;

            padding: 20px;

            min-height: 150px;

            box-sizing: border-box;
        }


        .processing-guide-number {
            display: inline-flex;

            width: 32px;
            height: 32px;

            align-items: center;
            justify-content: center;

            margin-bottom: 12px;

            border-radius: 50%;

            background: #174d35;

            color: #ffffff;

            font-size: 14px;

            font-weight: 700;
        }


        .processing-guide-card h3 {
            margin: 0 0 8px;

            color: #174d35;

            font-size: 16px;
        }


        .processing-guide-card p {
            margin: 0;

            color: #66736c;

            font-size: 13px;

            line-height: 1.5;
        }


        @media (max-width: 1050px) {

            .processing-guide-grid {
                grid-template-columns:
                    repeat(2, minmax(0, 1fr));
            }

        }


        @media (max-width: 700px) {

            .processing-guide-grid {
                grid-template-columns: 1fr;
            }

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


        <a
            href="process_service.php"
            class="active"
        >
            Service Processing
        </a>


        <?php if ($_SESSION["role"] === "admin"): ?>

            <div class="sidebar-section-title">
                Administration
            </div>


            <a href="staff_accounts.php">
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
| Main Staff Layout
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
                Service Processing
            </h1>

            <p>
                Process checked-in appointments and record information about the completed barangay service.
            </p>

        </div>


        <!-- Message -->

        <?php if ($message !== ""): ?>

            <div class="message">

                <?php echo e($message); ?>

            </div>

        <?php endif; ?>


        <!-- Checked In Appointments -->

        <section class="section">

            <h2 class="section-title">
                Checked In Appointments
            </h2>


            <?php if ($result->num_rows > 0): ?>

                <div class="processing-table-wrapper">


                    <!-- Top Horizontal Scrollbar -->

                    <div class="processing-table-scroll-top">

                        <div></div>

                    </div>


                    <!-- Table -->

                    <div class="table-container processing-table-container">

                        <table class="processing-table">

                            <tr>

                                <th>
                                    ID
                                </th>

                                <th>
                                    Resident
                                </th>

                                <th>
                                    Service
                                </th>

                                <th>
                                    Date
                                </th>

                                <th>
                                    Time
                                </th>

                                <th>
                                    Processing Notes
                                </th>

                                <th>
                                    Status
                                </th>

                            </tr>


                            <?php while ($appointment = $result->fetch_assoc()): ?>

                                <tr>

                                    <td>
                                        <?php echo e($appointment["id"]); ?>
                                    </td>


                                    <td class="resident-column">

                                        <strong>
                                            <?php echo e($appointment["resident_name"]); ?>
                                        </strong>

                                        <br>

                                        <?php echo e($appointment["resident_email"]); ?>

                                        <br>

                                        <?php echo e($appointment["contact_number"]); ?>

                                    </td>


                                    <td class="service-column">

                                        <?php

                                        echo e(
                                            $appointment["service_name"]
                                        );

                                        ?>

                                    </td>


                                    <td>

                                        <?php

                                        echo e(
                                            $appointment["schedule_date"]
                                        );

                                        ?>

                                    </td>


                                    <td>

                                        <?php

                                        echo e(
                                            $appointment["schedule_time"]
                                        );

                                        ?>

                                    </td>


                                    <td class="notes-column">


                                        <form
                                            method="POST"
                                            action=""
                                            class="processing-form"
                                        >

                                            <input
                                                type="hidden"
                                                name="appointment_id"
                                                value="<?php echo e($appointment["id"]); ?>"
                                            >


                                            <textarea
                                                name="processing_notes"
                                                rows="4"
                                                placeholder="Enter information about the actual service processing."
                                            ></textarea>


                                            <button type="submit">
                                                Mark as Completed
                                            </button>

                                        </form>


                                    </td>


                                    <td class="action-column">

                                        <span class="status status-approved">
                                            Checked In
                                        </span>

                                    </td>

                                </tr>

                            <?php endwhile; ?>

                        </table>

                    </div>

                </div>


            <?php else: ?>

                <div class="card">

                    <h3>
                        No Checked In Appointments
                    </h3>

                    <p>
                        There are no checked-in appointments waiting
                        for service processing.
                    </p>

                </div>

            <?php endif; ?>

        </section>


        <!-- Service Processing Guide -->

        <section class="card">

            <h2>
                Service Processing Guide
            </h2>

            <p>
                Follow the service processing workflow after a resident has been checked in.
            </p>


            <div class="processing-guide-grid">


                <div class="processing-guide-card">

                    <div class="processing-guide-number">
                        1
                    </div>

                    <h3>
                        Review Appointment
                    </h3>

                    <p>
                        Confirm the resident, requested service,
                        appointment date, and scheduled time.
                    </p>

                </div>


                <div class="processing-guide-card">

                    <div class="processing-guide-number">
                        2
                    </div>

                    <h3>
                        Process Service
                    </h3>

                    <p>
                        Perform the actual barangay service
                        according to the applicable procedure.
                    </p>

                </div>


                <div class="processing-guide-card">

                    <div class="processing-guide-number">
                        3
                    </div>

                    <h3>
                        Record Information
                    </h3>

                    <p>
                        Enter relevant information about the
                        service processing in the processing notes.
                    </p>

                </div>


                <div class="processing-guide-card">

                    <div class="processing-guide-number">
                        4
                    </div>

                    <h3>
                        Complete Appointment
                    </h3>

                    <p>
                        Mark the appointment as Completed after
                        the actual service has been processed.
                    </p>

                </div>


            </div>

        </section>


    </main>


    <footer class="footer">

        <p>
            Barangay Appointment Management System
        </p>

        <p>
            Service Processing
        </p>

    </footer>


</div>


<script>

/*
|--------------------------------------------------------------------------
| Top and Bottom Horizontal Scroll Synchronization
|--------------------------------------------------------------------------
*/

const processingTopScroll =
    document.querySelector(
        ".processing-table-scroll-top"
    );


const processingTableContainer =
    document.querySelector(
        ".processing-table-container"
    );


const processingTable =
    document.querySelector(
        ".processing-table"
    );


function updateProcessingTopScrollWidth() {

    if (
        !processingTopScroll ||
        !processingTable
    ) {
        return;
    }


    const scrollContent =
        processingTopScroll.querySelector(
            "div"
        );


    scrollContent.style.width =
        processingTable.scrollWidth + "px";
}


if (
    processingTopScroll &&
    processingTableContainer
) {

    processingTopScroll.addEventListener(
        "scroll",
        function () {

            processingTableContainer.scrollLeft =
                processingTopScroll.scrollLeft;

        }
    );


    processingTableContainer.addEventListener(
        "scroll",
        function () {

            processingTopScroll.scrollLeft =
                processingTableContainer.scrollLeft;

        }
    );


    window.addEventListener(
        "resize",
        updateProcessingTopScrollWidth
    );


    updateProcessingTopScrollWidth();

}

</script>


</body>

</html>