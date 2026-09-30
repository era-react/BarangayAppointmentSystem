<?php

require_once "../config.php";

require_staff();

$message = "";


/*
|--------------------------------------------------------------------------
| Update Appointment Status
|--------------------------------------------------------------------------
*/

if (isset($_GET["action"]) && isset($_GET["id"])) {

    $action = $_GET["action"];
    $appointment_id = (int) $_GET["id"];

    $new_status = "";
    $allowed_current_status = "";


    /*
    |--------------------------------------------------------------------------
    | Pending Appointment Actions
    |--------------------------------------------------------------------------
    */

    if ($action === "approve") {

        $new_status = "Approved";

        $allowed_current_status = "Pending";

    } elseif ($action === "reject") {

        $new_status = "Rejected";

        $allowed_current_status = "Pending";
    }


    /*
    |--------------------------------------------------------------------------
    | Check In Approved Appointment
    |--------------------------------------------------------------------------
    */

    if (
        $action === "checkin" &&
        $appointment_id > 0
    ) {

        $stmt = $conn->prepare(
            "UPDATE appointments
             SET
                status = 'Checked In',
                checked_in_at = NOW()
             WHERE id = ?
             AND status = 'Approved'"
        );

        $stmt->bind_param(
            "i",
            $appointment_id
        );

        if ($stmt->execute()) {

            if ($stmt->affected_rows > 0) {

                $message =
                    "Resident checked in successfully.";

            } else {

                $message =
                    "The appointment could not be checked in because it is no longer Approved.";
            }

        } else {

            $message =
                "Failed to check in the resident.";
        }

        $stmt->close();
    }


    /*
    |--------------------------------------------------------------------------
    | Mark Completed Appointment as Claimed
    |--------------------------------------------------------------------------
    */

    if (
        $action === "claim" &&
        $appointment_id > 0
    ) {

        $stmt = $conn->prepare(
            "UPDATE appointments
             SET
                status = 'Claimed',
                claimed_at = NOW()
             WHERE id = ?
             AND status = 'Completed'"
        );

        $stmt->bind_param(
            "i",
            $appointment_id
        );

        if ($stmt->execute()) {

            if ($stmt->affected_rows > 0) {

                $message =
                    "Appointment marked as Claimed successfully.";

            } else {

                $message =
                    "The appointment could not be marked as Claimed.";
            }

        } else {

            $message =
                "Failed to mark the appointment as Claimed.";
        }

        $stmt->close();
    }


    /*
    |--------------------------------------------------------------------------
    | Update Pending Appointment Status
    |--------------------------------------------------------------------------
    */

    if (
        $new_status !== "" &&
        $appointment_id > 0 &&
        $allowed_current_status !== ""
    ) {

        $stmt = $conn->prepare(
            "UPDATE appointments
             SET status = ?
             WHERE id = ?
             AND status = ?"
        );

        $stmt->bind_param(
            "sis",
            $new_status,
            $appointment_id,
            $allowed_current_status
        );

        if ($stmt->execute()) {

            if ($stmt->affected_rows > 0) {

                $message =
                    "Appointment status updated successfully.";

            } else {

                $message =
                    "The appointment could not be updated because its current status has changed.";
            }

        } else {

            $message =
                "Failed to update appointment status.";
        }

        $stmt->close();
    }
}


/*
|--------------------------------------------------------------------------
| Get Appointments
|--------------------------------------------------------------------------
*/

$result = $conn->query(
    "SELECT
        appointments.id,
        appointments.status,
        appointments.notes,
        appointments.created_at,

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

     ORDER BY appointments.created_at DESC"
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

    <title>Appointments</title>

    <link
        rel="stylesheet"
        href="../assets/style.css"
    >

    <style>

        .appointment-table th,
        .appointment-table td {
            vertical-align: middle;
            white-space: nowrap;
        }

        .appointment-table .resident-column {
            min-width: 210px;
            white-space: normal;
            line-height: 1.5;
        }

        .appointment-table .service-column {
            min-width: 190px;
            white-space: normal;
        }

        .appointment-table .notes-column {
            min-width: 180px;
            max-width: 260px;
            white-space: normal;
            line-height: 1.5;
        }

        .appointment-table .action-column {
            min-width: 130px;
            white-space: nowrap;
        }

        .appointment-actions {
            display: flex;
            gap: 6px;
            flex-wrap: wrap;
        }

    </style>

</head>


<body>


    <!-- =========================================
         SIDEBAR
    ========================================== -->

    <aside
        class="sidebar"
        id="staffSidebar"
    >

        <div class="sidebar-brand">

            <h2>
                Barangay Appointment
            </h2>

            <p>
                Management System
            </p>

        </div>


        <nav class="sidebar-menu">


            <!-- MAIN -->

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


            <a
                href="appointments.php"
                class="active"
            >
                Appointments
            </a>


            <a href="book_for_resident.php">
                Assisted Booking
            </a>


            <a href="process_service.php">
                Service Processing
            </a>


            <!-- ADMINISTRATION -->

            <?php if ($_SESSION["role"] === "admin"): ?>

                <div class="sidebar-section-title">
                    Administration
                </div>


                <a href="staff_accounts.php">
                    Staff Accounts
                </a>

            <?php endif; ?>


            <!-- ACCOUNT -->

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


    <!-- =========================================
         MAIN LAYOUT
    ========================================== -->

    <div class="staff-layout">


        <main class="container">


            <!-- =================================
                 TOP ACCOUNT AREA
            ================================== -->

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
                                e($_SESSION["role"])
                            );

                            ?>

                        </p>

                    </div>


                </div>

            </div>


            <!-- =================================
                 PAGE HEADER
            ================================== -->

            <div class="staff-page-header">

                <h1>
                    Appointment Requests
                </h1>

                <p>
                    Review resident appointment requests
                    and manage their appointment status.
                </p>

            </div>


            <!-- =================================
                 MESSAGE
            ================================== -->

            <?php if ($message !== ""): ?>

                <div class="message">

                    <?php echo e($message); ?>

                </div>

            <?php endif; ?>


            <!-- =================================
                 APPOINTMENT TABLE
            ================================== -->

            <section class="section">


                <h2 class="section-title">
                    All Appointment Requests
                </h2>


                <div class="appointment-table-wrapper">

    <div class="appointment-table-scroll-top">
        <div></div>
    </div>

    <div class="table-container appointment-table-container">

        <table class="appointment-table">

                        <thead>

                            <tr>

                                <th>
                                    ID
                                </th>

                                <th>
                                    Resident
                                </th>

                                <th>
                                    Contact
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
                                    Status
                                </th>

                                <th>
                                    Notes
                                </th>

                                <th>
                                    Action
                                </th>

                            </tr>

                        </thead>


                        <tbody>


                            <?php if ($result->num_rows > 0): ?>


                                <?php while ($appointment = $result->fetch_assoc()): ?>


                                    <tr>


                                        <!-- ID -->

                                        <td>

                                            <?php

                                            echo e(
                                                $appointment["id"]
                                            );

                                            ?>

                                        </td>


                                        <!-- RESIDENT -->

                                        <td class="resident-column">

                                            <strong>

                                                <?php

                                                echo e(
                                                    $appointment["resident_name"]
                                                );

                                                ?>

                                            </strong>


                                            <br>


                                            <?php

                                            echo e(
                                                $appointment["resident_email"]
                                            );

                                            ?>

                                        </td>


                                        <!-- CONTACT -->

                                        <td>

                                            <?php

                                            echo e(
                                                $appointment["contact_number"]
                                            );

                                            ?>

                                        </td>


                                        <!-- SERVICE -->

                                        <td class="service-column">

                                            <?php

                                            echo e(
                                                $appointment["service_name"]
                                            );

                                            ?>

                                        </td>


                                        <!-- DATE -->

                                        <td>

                                            <?php

                                            echo date(
                                                "M d, Y",
                                                strtotime(
                                                    $appointment["schedule_date"]
                                                )
                                            );

                                            ?>

                                        </td>


                                        <!-- TIME -->

                                        <td>

                                            <?php

                                            echo date(
                                                "g:i A",
                                                strtotime(
                                                    $appointment["schedule_time"]
                                                )
                                            );

                                            ?>

                                        </td>


                                        <!-- STATUS -->

                                        <td>


                                            <?php

                                            $status_class = "";


                                            if (
                                                $appointment["status"] === "Pending"
                                            ) {

                                                $status_class =
                                                    "status-pending";

                                            } elseif (
                                                $appointment["status"] === "Approved"
                                            ) {

                                                $status_class =
                                                    "status-approved";

                                            } elseif (
                                                $appointment["status"] === "Checked In"
                                            ) {

                                                $status_class =
                                                    "status-approved";

                                            } elseif (
                                                $appointment["status"] === "Completed"
                                            ) {

                                                $status_class =
                                                    "status-completed";

                                            } elseif (
                                                $appointment["status"] === "Claimed"
                                            ) {

                                                $status_class =
                                                    "status-completed";

                                            } elseif (
                                                $appointment["status"] === "Cancelled"
                                            ) {

                                                $status_class =
                                                    "status-cancelled";

                                            } elseif (
                                                $appointment["status"] === "Rejected"
                                            ) {

                                                $status_class =
                                                    "status-rejected";
                                            }

                                            ?>


                                            <span
                                                class="status <?php echo $status_class; ?>"
                                            >

                                                <?php

                                                echo e(
                                                    $appointment["status"]
                                                );

                                                ?>

                                            </span>


                                        </td>


                                        <!-- NOTES -->

                                        <td class="notes-column">


                                            <?php

                                            if (
                                                $appointment["notes"] !== null &&
                                                $appointment["notes"] !== ""
                                            ) {

                                                echo e(
                                                    $appointment["notes"]
                                                );

                                            } else {

                                                echo "—";
                                            }

                                            ?>


                                        </td>


                                        <!-- ACTION -->

                                        <td class="action-column">


                                            <?php if (
                                                $appointment["status"] === "Pending"
                                            ): ?>


                                                <div class="appointment-actions">


                                                    <a
                                                        href="appointments.php?action=approve&id=<?php echo $appointment["id"]; ?>"
                                                        class="action-button approve-button"
                                                    >
                                                        Approve
                                                    </a>


                                                    <a
                                                        href="appointments.php?action=reject&id=<?php echo $appointment["id"]; ?>"
                                                        class="action-button reject-button"
                                                    >
                                                        Reject
                                                    </a>


                                                </div>


                                            <?php elseif (
                                                $appointment["status"] === "Approved"
                                            ): ?>


                                                <a
                                                    href="appointments.php?action=checkin&id=<?php echo $appointment["id"]; ?>"
                                                    class="action-button process-button"
                                                >
                                                    Check In
                                                </a>


                                            <?php elseif (
                                                $appointment["status"] === "Checked In"
                                            ): ?>


                                                <a
                                                    href="process_service.php?id=<?php echo $appointment["id"]; ?>"
                                                    class="action-button process-button"
                                                >
                                                    Process Service
                                                </a>


                                            <?php elseif (
                                                $appointment["status"] === "Completed"
                                            ): ?>


                                                <a
                                                    href="appointments.php?action=claim&id=<?php echo $appointment["id"]; ?>"
                                                    class="action-button process-button"
                                                >
                                                    Mark as Claimed
                                                </a>


                                            <?php else: ?>


                                                No action


                                            <?php endif; ?>


                                        </td>


                                    </tr>


                                <?php endwhile; ?>


                            <?php else: ?>


                                <tr>

                                    <td colspan="9">

                                        No appointment requests found.

                                    </td>

                                </tr>


                            <?php endif; ?>


                        </tbody>


                    </table>

                 </div>

               </div>

            </section>


            <!-- =================================
                 STATUS GUIDE
            ================================== -->

            <section class="card">


                <h2>
                    Appointment Status Guide
                </h2>


                <div class="dashboard-grid">


                    <div class="dashboard-card">

                        <h3>
                            Pending
                        </h3>

                        <p>
                            The resident has submitted an
                            appointment request. Review the
                            request and choose Approve or Reject.
                        </p>

                    </div>


                    <div class="dashboard-card">

                        <h3>
                            Approved
                        </h3>

                        <p>
                            The appointment date and time are
                            confirmed. The resident may come to
                            the barangay office at the scheduled
                            date and time.
                        </p>

                    </div>


                    <div class="dashboard-card">

                        <h3>
                            Checked In
                        </h3>

                        <p>
                            The resident has arrived at the
                            barangay office and has been checked in.
                            Staff may now process the actual
                            barangay service.
                        </p>

                    </div>


                    <div class="dashboard-card">

                        <h3>
                            Process Service
                        </h3>

                        <p>
                            Perform the actual barangay service
                            according to the normal barangay
                            procedure, then record the relevant
                            processing information.
                        </p>

                    </div>


                    <div class="dashboard-card">

                        <h3>
                            Completed
                        </h3>

                        <p>
                            The actual barangay service has been
                            processed. The resident may claim or
                            receive the completed service output.
                        </p>

                    </div>


                    <div class="dashboard-card">

                        <h3>
                            Claimed
                        </h3>

                        <p>
                            The resident has received or claimed
                            the completed barangay service.
                        </p>

                    </div>


                </div>


            </section>


        </main>


        <!-- =====================================
             FOOTER
        ====================================== -->

        <footer class="footer">

            <p>
                Barangay Appointment Management System
            </p>

            <p>
                Staff Appointment Management
            </p>

        </footer>


    </div>


    <script>

        const topScroll =
            document.querySelector(
                ".appointment-table-scroll-top"
            );

        const tableContainer =
            document.querySelector(
                ".appointment-table-container"
            );

        const table =
            document.querySelector(
                ".appointment-table"
            );


        function updateTopScrollWidth() {

            if (!topScroll || !table) {
                return;
            }

            const scrollContent =
                topScroll.querySelector("div");

            scrollContent.style.width =
                table.scrollWidth + "px";
        }


        if (topScroll && tableContainer) {

            topScroll.addEventListener(
                "scroll",
                function () {

                    tableContainer.scrollLeft =
                        topScroll.scrollLeft;

                }
            );


            tableContainer.addEventListener(
                "scroll",
                function () {

                    topScroll.scrollLeft =
                        tableContainer.scrollLeft;

                }
            );


            window.addEventListener(
                "resize",
                updateTopScrollWidth
            );


            updateTopScrollWidth();

        }

    </script>

</body>

</html>