<?php

require_once "../config.php";

require_resident();

$resident_id = $_SESSION["user_id"];

$message = "";


/*
|--------------------------------------------------------------------------
| Get Selected Service
|--------------------------------------------------------------------------
*/

$service_id = isset($_GET["service_id"])
    ? (int) $_GET["service_id"]
    : 0;

if ($service_id <= 0) {

    header("Location: dashboard.php");
    exit;
}


/*
|--------------------------------------------------------------------------
| Get Service Information
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare(
    "SELECT id, name, description
     FROM services
     WHERE id = ?
     AND active = 1"
);

$stmt->bind_param(
    "i",
    $service_id
);

$stmt->execute();

$service_result = $stmt->get_result();

if ($service_result->num_rows !== 1) {

    header("Location: dashboard.php");
    exit;
}

$service = $service_result->fetch_assoc();

$stmt->close();


/*
|--------------------------------------------------------------------------
| Submit Appointment Request
|--------------------------------------------------------------------------
*/

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $schedule_id = (int) $_POST["schedule_id"];
    $notes = trim($_POST["notes"]);

    if ($schedule_id <= 0) {

        $message = "Please select an available schedule.";

    } else {

        /*
        | Check if the selected schedule exists,
        | belongs to the selected service,
        | and is still active.
        */

        $check = $conn->prepare(
            "SELECT id, available_slots
             FROM schedules
             WHERE id = ?
             AND service_id = ?
             AND active = 1"
        );

        $check->bind_param(
            "ii",
            $schedule_id,
            $service_id
        );

        $check->execute();

        $schedule_result = $check->get_result();

        if ($schedule_result->num_rows !== 1) {

            $message =
                "The selected schedule is no longer available.";

        } else {

            $schedule = $schedule_result->fetch_assoc();

            /*
            | Count appointments already using this schedule.
            */

            $count_stmt = $conn->prepare(
                "SELECT COUNT(*) AS total
                 FROM appointments
                 WHERE schedule_id = ?
                 AND status IN ('Pending', 'Approved')"
            );

            $count_stmt->bind_param(
                "i",
                $schedule_id
            );

            $count_stmt->execute();

            $count_result = $count_stmt->get_result();

            $count_data = $count_result->fetch_assoc();

            $booked_slots = (int) $count_data["total"];

            $count_stmt->close();


            /*
            | Check if there is still an available slot.
            */

            if ($booked_slots >= (int) $schedule["available_slots"]) {

                $message =
                    "Sorry, this schedule is already full.";

            } else {

                /*
                | Prevent the same resident from
                | booking the same schedule twice.
                */

                $duplicate = $conn->prepare(
                    "SELECT id
                     FROM appointments
                     WHERE resident_id = ?
                     AND schedule_id = ?
                     AND status IN ('Pending', 'Approved')"
                );

                $duplicate->bind_param(
                    "ii",
                    $resident_id,
                    $schedule_id
                );

                $duplicate->execute();

                $duplicate_result = $duplicate->get_result();

                if ($duplicate_result->num_rows > 0) {

                    $message =
                        "You already have an appointment for this schedule.";

                } else {

                    /*
                    | Create the appointment request.
                    */

                    $insert = $conn->prepare(
                        "INSERT INTO appointments
                        (
                            resident_id,
                            service_id,
                            schedule_id,
                            status,
                            notes,
                            created_by
                        )
                        VALUES
                        (?, ?, ?, 'Pending', ?, NULL)"
                    );

                    $insert->bind_param(
                        "iiis",
                        $resident_id,
                        $service_id,
                        $schedule_id,
                        $notes
                    );

                    if ($insert->execute()) {

                        $message =
                            "Appointment request submitted successfully!";

                    } else {

                        $message =
                            "Failed to submit appointment request.";
                    }

                    $insert->close();
                }

                $duplicate->close();
            }
        }

        $check->close();
    }
}


/*
|--------------------------------------------------------------------------
| Get Available Schedules
|--------------------------------------------------------------------------
*/

$schedules = $conn->prepare(
    "SELECT
        schedules.id,
        schedules.schedule_date,
        schedules.schedule_time,
        schedules.available_slots,
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
     WHERE schedules.service_id = ?
     AND schedules.active = 1
     AND TIMESTAMP(
         schedules.schedule_date,
         schedules.schedule_time
     ) > NOW()
     AND (
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
     ) < schedules.available_slots
     ORDER BY
         schedules.schedule_date ASC,
         schedules.schedule_time ASC"
);

$schedules->bind_param(
    "i",
    $service_id
);

$schedules->execute();

$schedule_result = $schedules->get_result();

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Request Appointment</title>

    <link
        rel="stylesheet"
        href="../assets/style.css"
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

                <a href="dashboard.php">
                    Dashboard
                </a>

                <a href="../logout.php">
                    Logout
                </a>

            </div>

        </div>

    </nav>


    <!-- Main Content -->

    <main class="container">

        <!-- Welcome -->

        <section class="dashboard-header">

            <h1>
                Request an Appointment
            </h1>

            <p>
                Welcome,
                <strong>
                    <?php echo e($_SESSION["full_name"]); ?>
                </strong>!
            </p>

            <p>
                Select an available date and time
                for your barangay service appointment.
            </p>

        </section>


        <!-- Selected Service -->

        <section class="card">

            <h2>
                <?php echo e($service["name"]); ?>
            </h2>

            <p>
                <?php echo e($service["description"]); ?>
            </p>

        </section>


        <!-- Message -->

        <?php if ($message !== ""): ?>

            <div class="message">

                <?php echo e($message); ?>

            </div>

        <?php endif; ?>


        <!-- Appointment Form -->

        <section class="form-card">

            <h2>
                Select an Available Schedule
            </h2>

            <?php if ($schedule_result->num_rows > 0): ?>

                <form method="POST" action="">

                    <?php while ($schedule = $schedule_result->fetch_assoc()): ?>

                        <?php

                        $available =
                            (int) $schedule["available_slots"]
                            - (int) $schedule["booked_slots"];

                        ?>

                        <?php if ($available > 0): ?>

                            <label
                                style="
                                    display: block;
                                    background: #f5f8f6;
                                    padding: 16px;
                                    margin-bottom: 12px;
                                    border: 1px solid #d5ddd8;
                                    border-radius: 8px;
                                    cursor: pointer;
                                "
                            >

                                <input
                                    type="radio"
                                    name="schedule_id"
                                    value="<?php echo $schedule["id"]; ?>"
                                    required
                                    style="
                                        width: auto;
                                        margin-right: 10px;
                                    "
                                >

                                <?php
                                echo e($schedule["schedule_date"]);
                                ?>

                                &nbsp; — &nbsp;

                                <?php
                                echo e($schedule["schedule_time"]);
                                ?>

                                &nbsp; — &nbsp;

                                <strong>
                                    <?php echo $available; ?>
                                    slot(s) available
                                </strong>

                            </label>

                        <?php endif; ?>

                    <?php endwhile; ?>


                    <label for="notes">
                        Additional Notes
                    </label>

                    <textarea
                        id="notes"
                        name="notes"
                        rows="5"
                        placeholder="Optional: Enter any additional information you want the barangay staff to know."
                    ></textarea>


                    <br>


                    <button
                        type="submit"
                    >
                        Submit Appointment Request
                    </button>

                </form>

            <?php else: ?>

                <div class="message">

                    No available schedules for this service.

                </div>

            <?php endif; ?>

        </section>


        <!-- Back Button -->

        <section class="card">

            <h2>
                Need to make another request?
            </h2>

            <p>
                You can return to your Resident Dashboard
                to select another barangay service.
            </p>

            <a
                href="dashboard.php"
                class="button button-secondary"
            >
                Back to Resident Dashboard
            </a>

        </section>

    </main>


    <!-- Footer -->

    <footer class="footer">

        <p>
            Barangay Appointment Management System
        </p>

        <p>
            Senior-friendly online appointment system
            for barangay services
        </p>

    </footer>

</body>

</html>