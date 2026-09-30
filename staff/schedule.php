<?php

require_once "../config.php";

require_staff();

$message = "";


/*
|--------------------------------------------------------------------------
| Add New Schedule
|--------------------------------------------------------------------------
*/

if (isset($_GET["success"])) {
    $message = "Schedule added successfully.";
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $service_id = (int) $_POST["service_id"];
    $schedule_date = $_POST["schedule_date"];
    $schedule_time = $_POST["schedule_time"];
    $available_slots = (int) $_POST["available_slots"];

    if (
        $service_id <= 0 ||
        $schedule_date === "" ||
        $schedule_time === "" ||
        $available_slots < 1 ||
        $available_slots > 50
    ) {

        $message =
            "Please enter between 1 and 50 appointment slots.";

    } else {

        /*
        |--------------------------------------------------------------------------
        | Check if Schedule Date and Time Are in the Past
        |--------------------------------------------------------------------------
        */

        $selected_datetime = strtotime(
            $schedule_date . " " . $schedule_time
        );

        if ($selected_datetime <= time()) {

            $message =
                "The schedule date and time must be in the future.";

        } else {

            /*
            |--------------------------------------------------------------------------
            | Check for Duplicate Schedule
            |--------------------------------------------------------------------------
            */

            $check_stmt = $conn->prepare(
                "SELECT id
                 FROM schedules
                 WHERE service_id = ?
                 AND schedule_date = ?
                 AND schedule_time = ?
                 LIMIT 1"
            );

            $check_stmt->bind_param(
                "iss",
                $service_id,
                $schedule_date,
                $schedule_time
            );

            $check_stmt->execute();

            $check_result = $check_stmt->get_result();

            if ($check_result->num_rows > 0) {

                $message =
                    "A schedule for this service, date, and time already exists.";

                $check_stmt->close();

            } else {

                $check_stmt->close();

                /*
                |--------------------------------------------------------------------------
                | Insert New Schedule
                |--------------------------------------------------------------------------
                */

                $stmt = $conn->prepare(
                    "INSERT INTO schedules
                    (
                        service_id,
                        schedule_date,
                        schedule_time,
                        available_slots
                    )
                    VALUES (?, ?, ?, ?)"
                );

                $stmt->bind_param(
                    "issi",
                    $service_id,
                    $schedule_date,
                    $schedule_time,
                    $available_slots
                );

                if ($stmt->execute()) {

                    header("Location: schedule.php?success=1");
                    exit;

                } else {

                    $message =
                        "Failed to add schedule.";
                }

                $stmt->close();
            }
        }
    }
}


/*
|--------------------------------------------------------------------------
| Activate / Deactivate Schedule
|--------------------------------------------------------------------------
*/

if (isset($_GET["toggle"])) {

    $schedule_id = (int) $_GET["toggle"];

    $stmt = $conn->prepare(
        "UPDATE schedules
         SET active = IF(active = 1, 0, 1)
         WHERE id = ?"
    );

    $stmt->bind_param(
        "i",
        $schedule_id
    );

    $stmt->execute();

    $stmt->close();

    header("Location: schedule.php");
    exit;
}


/*
|--------------------------------------------------------------------------
| Get Active Services
|--------------------------------------------------------------------------
*/

$services = $conn->query(
    "SELECT id, name
     FROM services
     WHERE active = 1
     ORDER BY name ASC"
);


/*
|--------------------------------------------------------------------------
| Get All Schedules
|--------------------------------------------------------------------------
*/

$schedules = $conn->query(
    "SELECT
        schedules.id,
        schedules.schedule_date,
        schedules.schedule_time,
        schedules.available_slots,
        schedules.active,
        services.name AS service_name,

        (
            SELECT COUNT(*)
            FROM appointments
            WHERE appointments.schedule_id = schedules.id
            AND appointments.status IN (
                'Pending',
                'Approved',
                'Checked In',
                'Completed',
                'Claimed'
            )
        ) AS booked_slots

     FROM schedules

     INNER JOIN services
        ON schedules.service_id = services.id

     ORDER BY
        schedules.schedule_date ASC,
        schedules.schedule_time ASC,
        services.name ASC"
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

    <title>Schedules</title>

    <link
        rel="stylesheet"
        href="../assets/style.css"
    >

    <style>

        .schedule-table th,
        .schedule-table td {
            vertical-align: middle;
            white-space: nowrap;
        }

        .schedule-table .service-column {
            white-space: normal;
            min-width: 220px;
        }

        .slot-count {
            font-weight: 600;
            text-align: center;
        }

        .remaining-available {
            font-weight: 600;
        }

        .remaining-full {
            font-weight: 700;
        }

        .schedule-date {
            white-space: nowrap;
        }

        .schedule-time {
            white-space: nowrap;
        }

        .action-cell {
            white-space: nowrap;
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


            <a
                href="schedule.php"
                class="active"
            >
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
                    Appointment Schedules
                </h1>

                <p>
                    Create and manage appointment dates,
                    times, and available appointment slots.
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
                 ADD SCHEDULE
            ================================== -->

            <section class="form-card">

                <h2>
                    Add New Schedule
                </h2>


                <form method="POST">


                    <label for="service_id">
                        Barangay Service
                    </label>


                    <select
                        id="service_id"
                        name="service_id"
                        required
                    >

                        <option
                            value=""
                            selected
                            disabled
                            hidden
                        >
                            Select a service
                        </option>


                        <?php while ($service = $services->fetch_assoc()): ?>

                            <option
                                value="<?php echo $service["id"]; ?>"
                            >

                                <?php
                                echo e(
                                    $service["name"]
                                );
                                ?>

                            </option>

                        <?php endwhile; ?>


                    </select>


                    <label for="schedule_date">
                        Appointment Date
                    </label>


                    <input
                        type="date"
                        id="schedule_date"
                        name="schedule_date"
                        required
                    >


                    <label for="schedule_time">
                        Appointment Time
                    </label>


                    <input
                        type="time"
                        id="schedule_time"
                        name="schedule_time"
                        required
                    >


                    <label for="available_slots">
                        Total Appointment Slots
                    </label>


                    <input
                        type="number"
                        id="available_slots"
                        name="available_slots"
                        min="1"
                        max="50"
                        value="1"
                        required
                    >


                    <small>
                        Enter the maximum number of appointments
                        allowed for this schedule.
                    </small>


                    <br>


                    <button type="submit">
                        Add Schedule
                    </button>


                </form>

            </section>


            <!-- =================================
                 EXISTING SCHEDULES
            ================================== -->

            <section class="section">


                <h2 class="section-title">
                    Existing Schedules
                </h2>


                <p>
                    Review appointment capacity, current bookings,
                    remaining slots, schedule status, and availability.
                </p>


                <div class="table-container">

                    <table class="schedule-table">


                        <thead>

                            <tr>

                                <th>
                                    ID
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
                                    Total Slots
                                </th>

                                <th>
                                    Booked
                                </th>

                                <th>
                                    Remaining
                                </th>

                                <th>
                                    Schedule Status
                                </th>

                                <th>
                                    Availability
                                </th>

                                <th>
                                    Action
                                </th>

                            </tr>

                        </thead>


                        <tbody>


                            <?php if ($schedules->num_rows > 0): ?>


                                <?php while ($schedule = $schedules->fetch_assoc()): ?>


                                    <?php

                                    $total_slots =
                                        (int) $schedule["available_slots"];

                                    $booked_slots =
                                        (int) $schedule["booked_slots"];

                                    $remaining_slots =
                                        max(
                                            0,
                                            $total_slots - $booked_slots
                                        );

                                    $schedule_datetime = strtotime(
                                        $schedule["schedule_date"] . " " .
                                        $schedule["schedule_time"]
                                    );

                                    $is_expired =
                                        $schedule_datetime <= time();

                                    ?>


                                    <tr>


                                        <!-- ID -->

                                        <td>

                                            <?php
                                            echo e(
                                                $schedule["id"]
                                            );
                                            ?>

                                        </td>


                                        <!-- SERVICE -->

                                        <td class="service-column">

                                            <?php

                                            echo e(
                                                $schedule["service_name"]
                                            );

                                            ?>

                                        </td>


                                        <!-- DATE -->

                                        <td class="schedule-date">

                                            <?php

                                            echo date(
                                                "M d, Y",
                                                strtotime(
                                                    $schedule["schedule_date"]
                                                )
                                            );

                                            ?>

                                        </td>


                                        <!-- TIME -->

                                        <td class="schedule-time">

                                            <?php

                                            echo date(
                                                "g:i A",
                                                strtotime(
                                                    $schedule["schedule_time"]
                                                )
                                            );

                                            ?>

                                        </td>


                                        <!-- TOTAL SLOTS -->

                                        <td class="slot-count">

                                            <?php
                                            echo $total_slots;
                                            ?>

                                        </td>


                                        <!-- BOOKED -->

                                        <td class="slot-count">

                                            <?php
                                            echo $booked_slots;
                                            ?>

                                        </td>


                                        <!-- REMAINING -->

                                        <td class="slot-count">


                                            <?php if ($remaining_slots > 0): ?>

                                                <span class="remaining-available">

                                                    <?php
                                                    echo $remaining_slots;
                                                    ?>

                                                </span>


                                            <?php else: ?>


                                                <span class="remaining-full">
                                                    0
                                                </span>


                                            <?php endif; ?>


                                        </td>


                                        <!-- SCHEDULE STATUS -->

                                        <td>


                                            <?php if (
                                                $schedule["active"] == 1 &&
                                                $is_expired
                                            ): ?>


                                                <span class="status status-cancelled">
                                                    Expired
                                                </span>


                                            <?php elseif (
                                                $schedule["active"] == 1
                                            ): ?>


                                                <span class="status status-approved">
                                                    Active
                                                </span>


                                            <?php else: ?>


                                                <span class="status status-cancelled">
                                                    Inactive
                                                </span>


                                            <?php endif; ?>


                                        </td>


                                        <!-- AVAILABILITY -->

                                        <td>


                                            <?php if ($is_expired): ?>


                                                <span class="status status-cancelled">
                                                    Expired
                                                </span>


                                            <?php elseif (
                                                $remaining_slots > 0
                                            ): ?>


                                                <span class="status status-approved">
                                                    Available
                                                </span>


                                            <?php else: ?>


                                                <span class="status status-cancelled">
                                                    Full
                                                </span>


                                            <?php endif; ?>


                                        </td>


                                        <!-- ACTION -->

                                        <td class="action-cell">


                                            <?php if (
                                                $schedule["active"] == 1
                                            ): ?>


                                                <a
                                                    href="schedule.php?toggle=<?php echo $schedule["id"]; ?>"
                                                    class="button button-danger"
                                                >
                                                    Deactivate
                                                </a>


                                            <?php else: ?>


                                                <a
                                                    href="schedule.php?toggle=<?php echo $schedule["id"]; ?>"
                                                    class="button"
                                                >
                                                    Activate
                                                </a>


                                            <?php endif; ?>


                                        </td>


                                    </tr>


                                <?php endwhile; ?>


                            <?php else: ?>


                                <tr>

                                    <td colspan="10">

                                        No schedules have been created yet.

                                    </td>

                                </tr>


                            <?php endif; ?>


                        </tbody>


                    </table>

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
                Staff Schedule Management
            </p>

        </footer>


    </div>


</body>

</html>