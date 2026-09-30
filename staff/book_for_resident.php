<?php

require_once "../config.php";

require_staff();

$message = "";


/*
|--------------------------------------------------------------------------
| Book Appointment for Resident
|--------------------------------------------------------------------------
*/

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $resident_id = (int) $_POST["resident_id"];
    $service_id = (int) $_POST["service_id"];
    $schedule_id = (int) $_POST["schedule_id"];
    $notes = trim($_POST["notes"]);

    if (
        $resident_id <= 0 ||
        $service_id <= 0 ||
        $schedule_id <= 0
    ) {

        $message = "Please complete all required fields.";

    } else {

        /*
        |--------------------------------------------------------------------------
        | Check Resident
        |--------------------------------------------------------------------------
        */

        $resident_check = $conn->prepare(
            "SELECT id
             FROM users
             WHERE id = ?
             AND role = 'resident'
             AND status = 'Active'"
        );

        $resident_check->bind_param(
            "i",
            $resident_id
        );

        $resident_check->execute();

        $resident_result = $resident_check->get_result();

        if ($resident_result->num_rows !== 1) {

            $message =
                "The selected resident is not an active resident.";

        } else {

            /*
            |--------------------------------------------------------------------------
            | Check Schedule
            |--------------------------------------------------------------------------
            */

            $schedule_check = $conn->prepare(
                "SELECT id, available_slots
                 FROM schedules
                 WHERE id = ?
                 AND service_id = ?
                 AND active = 1"
            );

            $schedule_check->bind_param(
                "ii",
                $schedule_id,
                $service_id
            );

            $schedule_check->execute();

            $schedule_result = $schedule_check->get_result();

            if ($schedule_result->num_rows !== 1) {

                $message =
                    "The selected schedule is no longer available.";

            } else {

                $schedule = $schedule_result->fetch_assoc();


                /*
                |--------------------------------------------------------------------------
                | Count Booked Slots
                |--------------------------------------------------------------------------
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
                |--------------------------------------------------------------------------
                | Check Available Slots
                |--------------------------------------------------------------------------
                */

                if (
                    $booked_slots >=
                    (int) $schedule["available_slots"]
                ) {

                    $message =
                        "Sorry, this schedule is already full.";

                } else {

                    /*
                    |--------------------------------------------------------------------------
                    | Check Duplicate Appointment
                    |--------------------------------------------------------------------------
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

                    $duplicate_result =
                        $duplicate->get_result();

                    if ($duplicate_result->num_rows > 0) {

                        $message =
                            "This resident already has an appointment for this schedule.";

                    } else {

                        /*
                        |--------------------------------------------------------------------------
                        | Insert Appointment
                        |--------------------------------------------------------------------------
                        */

                        $created_by = $_SESSION["user_id"];

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
                            (?, ?, ?, 'Pending', ?, ?)"
                        );

                        $insert->bind_param(
                            "iiisi",
                            $resident_id,
                            $service_id,
                            $schedule_id,
                            $notes,
                            $created_by
                        );

                        if ($insert->execute()) {

                            $message =
                                "Appointment for the resident was submitted successfully.";

                        } else {

                            $message =
                                "Failed to create the appointment.";
                        }

                        $insert->close();
                    }

                    $duplicate->close();
                }
            }

            $schedule_check->close();
        }

        $resident_check->close();
    }
}


/*
|--------------------------------------------------------------------------
| Get Active Residents
|--------------------------------------------------------------------------
*/

$residents = $conn->query(
    "SELECT id, full_name, email
     FROM users
     WHERE role = 'resident'
     AND status = 'Active'
     ORDER BY full_name ASC"
);


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
| Get Active Schedules
|--------------------------------------------------------------------------
*/

$schedule_data = [];

$schedules = $conn->query(
    "SELECT
        schedules.id,
        schedules.service_id,
        services.name AS service_name,
        schedules.schedule_date,
        schedules.schedule_time,
        schedules.available_slots,

        (
            SELECT COUNT(*)
            FROM appointments
            WHERE appointments.schedule_id = schedules.id
            AND appointments.status IN ('Pending', 'Approved')
        ) AS booked_slots

     FROM schedules

     INNER JOIN services
        ON schedules.service_id = services.id

     WHERE schedules.active = 1

     ORDER BY
        schedules.schedule_date ASC,
        schedules.schedule_time ASC"
);


while ($schedule = $schedules->fetch_assoc()) {

    if (
        (int) $schedule["booked_slots"] <
        (int) $schedule["available_slots"]
    ) {

        $schedule_data[] = $schedule;
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

    <title>Assisted Booking</title>

    <link
        rel="stylesheet"
        href="../assets/style.css"
    >

    <style>

        /*
        |--------------------------------------------------------------------------
        | Assisted Booking Form
        |--------------------------------------------------------------------------
        */

        .assisted-booking-card {
            max-width: 850px;
        }


        .assisted-booking-note {
            margin-bottom: 24px;
            padding: 16px 18px;

            background: #f3f8f5;
            border: 1px solid #dce9e1;
            border-left: 4px solid #218653;

            border-radius: 8px;

            color: #53625a;

            font-size: 14px;
            line-height: 1.6;
        }


        /*
        |--------------------------------------------------------------------------
        | Resident Search Dropdown
        |--------------------------------------------------------------------------
        */

        .resident-select-wrapper {
            position: relative;

            width: 100%;
            height: 48px;

            margin: 0;
            padding: 0;

            box-sizing: border-box;

            min-width: 0;
            max-width: 100%;
        }


        .resident-select-button {
            display: flex;

            position: relative;

            width: 100%;
            height: 48px;

            min-width: 0;
            max-width: 100%;

            min-height: 48px;
            max-height: 48px;

            flex: 0 0 48px;

            margin: 0;
            padding: 12px 15px;

            box-sizing: border-box;

            appearance: none;
            -webkit-appearance: none;

            transform: none !important;
            scale: 1 !important;

            background: #ffffff !important;
            color: #333333 !important;

            border: 1px solid #cccccc;
            border-radius: 5px;

            font-family: inherit;
            font-size: 16px;
            font-weight: normal;
            line-height: normal;

            text-align: left;

            cursor: pointer;

            align-items: center;
            justify-content: space-between;

            overflow: hidden;

            transition: none !important;
        }


        .resident-select-button:hover {
            width: 100% !important;
            height: 48px !important;

            min-height: 48px !important;
            max-height: 48px !important;

            padding: 12px 15px !important;

            margin: 0 !important;

            transform: none !important;
            scale: 1 !important;

            background: #ffffff !important;
            color: #333333 !important;

            border-color: #999999;
        }


        .resident-select-button:focus {
            width: 100% !important;
            height: 48px !important;

            min-height: 48px !important;
            max-height: 48px !important;

            padding: 12px 15px !important;

            margin: 0 !important;

            transform: none !important;
            scale: 1 !important;

            background: #ffffff !important;
            color: #333333 !important;

            outline: none;

            border-color: #888888;
        }


        .resident-select-button:active {
            width: 100% !important;
            height: 48px !important;

            min-height: 48px !important;
            max-height: 48px !important;

            padding: 12px 15px !important;

            margin: 0 !important;

            transform: none !important;
            scale: 1 !important;

            background: #ffffff !important;
            color: #333333 !important;
        }


        /*
        |--------------------------------------------------------------------------
        | Selected Resident
        |--------------------------------------------------------------------------
        */

        #selectedResident {
            display: block;

            min-width: 0;
            max-width: calc(100% - 30px);

            font-weight: normal;

            white-space: nowrap;

            overflow: hidden;

            text-overflow: ellipsis;
        }


        #residentArrow {
            flex-shrink: 0;

            margin-left: 10px;

            font-size: 16px;

            line-height: 1;
        }


        /*
        |--------------------------------------------------------------------------
        | Dropdown Menu
        |--------------------------------------------------------------------------
        */

        .resident-select-menu {
            display: none;

            position: absolute;

            top: 52px;
            left: 0;

            width: 100%;
            max-width: 100%;
            min-width: 0;

            height: auto;

            margin: 0;
            padding: 0;

            box-sizing: border-box;

            background: #ffffff;

            border: 1px solid #cccccc;

            border-radius: 5px;

            box-shadow:
                0 4px 10px
                rgba(0, 0, 0, 0.12);

            z-index: 9999;

            overflow: hidden;
        }


        .resident-select-menu.show {
            display: block;
        }


        /*
        |--------------------------------------------------------------------------
        | Search Box
        |--------------------------------------------------------------------------
        */

        .resident-search-box {
            display: block;

            width: 100%;

            margin: 0;
            padding: 10px;

            box-sizing: border-box;

            background: #ffffff;

            border-bottom: 1px solid #dddddd;
        }


        .resident-search-box input {
            display: block;

            width: 100%;
            max-width: 100%;
            height: 44px;

            margin: 0;
            padding: 10px 12px;

            box-sizing: border-box;

            background: #ffffff;

            color: #333333;

            border: 1px solid #cccccc;

            border-radius: 4px;

            font-family: inherit;
            font-size: 16px;

            font-weight: normal;

            transition: none !important;
        }


        .resident-search-box input:focus {
            outline: none;

            border-color: #888888;

            background: #ffffff;
        }


        /*
        |--------------------------------------------------------------------------
        | Resident Results
        |--------------------------------------------------------------------------
        */

        .resident-results {
            display: block;

            width: 100%;

            max-width: 100%;

            max-height: 250px;

            margin: 0;
            padding: 0;

            box-sizing: border-box;

            overflow-y: auto;
            overflow-x: hidden;

            background: #ffffff;
        }


        .resident-item {
            display: block;

            width: 100%;
            max-width: 100%;

            margin: 0;
            padding: 12px 15px;

            box-sizing: border-box;

            background: #ffffff;

            color: #333333;

            border: none;

            border-bottom: 1px solid #eeeeee;

            text-align: left;

            cursor: pointer;

            font-family: inherit;
            font-size: 16px;

            font-weight: normal;

            overflow: hidden;

            transform: none !important;
        }


        .resident-item:last-child {
            border-bottom: none;
        }


        .resident-item:hover {
            width: 100% !important;

            margin: 0 !important;

            padding: 12px 15px !important;

            transform: none !important;

            background: #f5f5f5;

            color: #333333;
        }


        .resident-item-name {
            display: block;

            width: 100%;

            font-weight: normal;

            color: #333333;

            white-space: nowrap;

            overflow: hidden;

            text-overflow: ellipsis;
        }


        .resident-item-email {
            display: block;

            width: 100%;

            margin-top: 3px;

            font-size: 14px;

            font-weight: normal;

            color: #777777;

            white-space: nowrap;

            overflow: hidden;

            text-overflow: ellipsis;
        }


        .resident-no-result {
            width: 100%;

            padding: 15px;

            box-sizing: border-box;

            text-align: center;

            color: #777777;

            background: #ffffff;
        }


        /*
        |--------------------------------------------------------------------------
        | Workflow Cards
        |--------------------------------------------------------------------------
        */

        .workflow-grid {
            display: grid;

            grid-template-columns:
                repeat(4, minmax(0, 1fr));

            gap: 16px;
        }


        .workflow-card {
            background: #f8faf9;

            border: 1px solid #e1e8e4;

            border-radius: 10px;

            padding: 20px;

            min-height: 150px;

            box-sizing: border-box;
        }


        .workflow-card-number {
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


        .workflow-card h3 {
            margin: 0 0 8px;

            color: #174d35;

            font-size: 16px;
        }


        .workflow-card p {
            margin: 0;

            color: #66736c;

            font-size: 13px;

            line-height: 1.5;
        }


        @media (max-width: 1050px) {

            .workflow-grid {
                grid-template-columns:
                    repeat(2, minmax(0, 1fr));
            }

        }


        @media (max-width: 700px) {

            .workflow-grid {
                grid-template-columns: 1fr;
            }

            .assisted-booking-card {
                max-width: 100%;
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


        <a
            href="book_for_resident.php"
            class="active"
        >
            Assisted Booking
        </a>


        <a href="process_service.php">
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
                Assisted Booking
            </h1>

            <p>
                Create an appointment on behalf of an active resident at the barangay office.
            </p>

        </div>


        <!-- Information Note -->

        <div class="assisted-booking-note">

            <strong>
                Assisted Booking:
            </strong>

            This feature allows authorized barangay staff to create an appointment
            for residents who need assistance using the online appointment system.

        </div>


        <?php if ($message !== ""): ?>

            <div class="message">

                <?php echo e($message); ?>

            </div>

        <?php endif; ?>


        <!-- Booking Form -->

        <section class="card assisted-booking-card">

            <h2>
                Resident Appointment Details
            </h2>


            <form method="POST">


                <!-- Resident -->

                <label>
                    Select a Resident
                </label>


                <div class="resident-select-wrapper">

                    <button
                        type="button"
                        class="resident-select-button"
                        id="residentSelectButton"
                    >

                        <span id="selectedResident">
                            Select a resident
                        </span>

                        <span id="residentArrow">
                            ▼
                        </span>

                    </button>


                    <div
                        class="resident-select-menu"
                        id="residentSelectMenu"
                    >

                        <div class="resident-search-box">

                            <input
                                type="text"
                                id="residentSearch"
                                placeholder="Search resident..."
                                autocomplete="off"
                            >

                        </div>


                        <div
                            class="resident-results"
                            id="residentResults"
                        >

                            <?php while ($resident = $residents->fetch_assoc()): ?>

                                <button
                                    type="button"
                                    class="resident-item"
                                    data-id="<?php echo $resident["id"]; ?>"
                                    data-name="<?php echo e($resident["full_name"]); ?>"
                                    data-email="<?php echo e($resident["email"]); ?>"
                                >

                                    <span class="resident-item-name">

                                        <?php

                                        echo e(
                                            $resident["full_name"]
                                        );

                                        ?>

                                    </span>


                                    <span class="resident-item-email">

                                        <?php

                                        echo e(
                                            $resident["email"]
                                        );

                                        ?>

                                    </span>

                                </button>

                            <?php endwhile; ?>


                            <div
                                class="resident-no-result"
                                id="residentNoResult"
                                style="display: none;"
                            >

                                No matching resident found.

                            </div>

                        </div>

                    </div>

                </div>


                <input
                    type="hidden"
                    id="resident_id"
                    name="resident_id"
                    value=""
                    required
                >


                <!-- Service -->

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


                <!-- Schedule -->

                <label for="schedule_id">
                    Available Schedule
                </label>


                <select
                    id="schedule_id"
                    name="schedule_id"
                    required
                >

                    <option value="">
                        Select a service first
                    </option>


                    <?php foreach ($schedule_data as $schedule): ?>

                        <option
                            value="<?php echo $schedule["id"]; ?>"
                            data-service="<?php echo $schedule["service_id"]; ?>"
                        >

                            <?php

                            echo e(
                                $schedule["service_name"]
                            );

                            ?>

                            -

                            <?php

                            echo e(
                                $schedule["schedule_date"]
                            );

                            ?>

                            -

                            <?php

                            echo e(
                                $schedule["schedule_time"]
                            );

                            ?>

                            -

                            <?php

                            echo e(
                                $schedule["available_slots"]
                            );

                            ?>

                            slot(s)

                        </option>

                    <?php endforeach; ?>

                </select>


                <!-- Notes -->

                <label for="notes">
                    Additional Notes
                </label>


                <textarea
                    id="notes"
                    name="notes"
                    rows="5"
                    placeholder="Optional: Enter any additional information."
                ></textarea>


                <br>


                <button type="submit">
                    Book Appointment for Resident
                </button>


            </form>

        </section>


        <!-- Workflow -->

        <section class="card">

            <h2>
                Assisted Booking Process
            </h2>

            <p>
                Follow these steps when creating an appointment on behalf of a resident.
            </p>


            <div class="workflow-grid">


                <div class="workflow-card">

                    <div class="workflow-card-number">
                        1
                    </div>

                    <h3>
                        Select Resident
                    </h3>

                    <p>
                        Search for and select the active resident who needs assistance.
                    </p>

                </div>


                <div class="workflow-card">

                    <div class="workflow-card-number">
                        2
                    </div>

                    <h3>
                        Select Service
                    </h3>

                    <p>
                        Choose the barangay service requested by the resident.
                    </p>

                </div>


                <div class="workflow-card">

                    <div class="workflow-card-number">
                        3
                    </div>

                    <h3>
                        Select Schedule
                    </h3>

                    <p>
                        Choose an available appointment date and time.
                    </p>

                </div>


                <div class="workflow-card">

                    <div class="workflow-card-number">
                        4
                    </div>

                    <h3>
                        Submit Appointment
                    </h3>

                    <p>
                        Submit the request and keep the appointment associated with the selected resident.
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
            Assisted Booking
        </p>

    </footer>


</div>


<script>


/*
|--------------------------------------------------------------------------
| Resident Search Dropdown
|--------------------------------------------------------------------------
*/

const residentSelectButton =
    document.getElementById(
        "residentSelectButton"
    );


const residentSelectMenu =
    document.getElementById(
        "residentSelectMenu"
    );


const residentSearch =
    document.getElementById(
        "residentSearch"
    );


const residentItems =
    Array.from(
        document.querySelectorAll(
            ".resident-item"
        )
    );


const residentNoResult =
    document.getElementById(
        "residentNoResult"
    );


const residentResults =
    document.getElementById(
        "residentResults"
    );


const residentId =
    document.getElementById(
        "resident_id"
    );


const selectedResident =
    document.getElementById(
        "selectedResident"
    );


const residentArrow =
    document.getElementById(
        "residentArrow"
    );


/*
|--------------------------------------------------------------------------
| Open and Close Resident Dropdown
|--------------------------------------------------------------------------
*/

residentSelectButton.addEventListener(
    "click",
    function () {

        const isOpen =
            residentSelectMenu.classList.contains(
                "show"
            );


        if (isOpen) {

            residentSelectMenu.classList.remove(
                "show"
            );

            residentArrow.textContent =
                "▼";

        } else {

            residentSelectMenu.classList.add(
                "show"
            );

            residentArrow.textContent =
                "▲";


            setTimeout(
                function () {

                    residentSearch.focus();

                },
                50
            );

        }

    }
);


/*
|--------------------------------------------------------------------------
| Search Residents
|--------------------------------------------------------------------------
*/

residentSearch.addEventListener(
    "input",
    function () {

        const search =
            this.value
            .toLowerCase()
            .trim();


        if (search === "") {

            residentItems.forEach(
                function (item) {

                    item.style.display =
                        "block";

                }
            );

            residentNoResult.style.display =
                "none";

            return;
        }


        const startsWithMatches = [];

        const containsMatches = [];


        residentItems.forEach(
            function (item) {

                const name =
                    item.dataset.name
                    .toLowerCase();


                if (
                    name.startsWith(search)
                ) {

                    startsWithMatches.push(
                        item
                    );

                } else if (
                    name.includes(search)
                ) {

                    containsMatches.push(
                        item
                    );

                } else {

                    item.style.display =
                        "none";

                }

            }
        );


        startsWithMatches.forEach(
            function (item) {

                item.style.display =
                    "block";

                residentResults.appendChild(
                    item
                );

            }
        );


        containsMatches.forEach(
            function (item) {

                item.style.display =
                    "block";

                residentResults.appendChild(
                    item
                );

            }
        );


        if (
            startsWithMatches.length === 0 &&
            containsMatches.length === 0
        ) {

            residentNoResult.style.display =
                "block";

        } else {

            residentNoResult.style.display =
                "none";

        }

    }
);


/*
|--------------------------------------------------------------------------
| Select Resident
|--------------------------------------------------------------------------
*/

residentItems.forEach(
    function (item) {

        item.addEventListener(
            "click",
            function () {

                residentId.value =
                    this.dataset.id;


                selectedResident.textContent =
                    this.dataset.name;


                residentSelectMenu.classList.remove(
                    "show"
                );


                residentArrow.textContent =
                    "▼";


                residentSearch.value = "";


                residentItems.forEach(
                    function (resident) {

                        resident.style.display =
                            "block";

                    }
                );


                residentNoResult.style.display =
                    "none";

            }
        );

    }
);


/*
|--------------------------------------------------------------------------
| Close Dropdown When Clicking Outside
|--------------------------------------------------------------------------
*/

document.addEventListener(
    "click",
    function (event) {

        if (
            !residentSelectButton.contains(
                event.target
            ) &&
            !residentSelectMenu.contains(
                event.target
            )
        ) {

            residentSelectMenu.classList.remove(
                "show"
            );

            residentArrow.textContent =
                "▼";

        }

    }
);


/*
|--------------------------------------------------------------------------
| Service and Schedule Filtering
|--------------------------------------------------------------------------
*/

const serviceSelect =
    document.getElementById(
        "service_id"
    );


const scheduleSelect =
    document.getElementById(
        "schedule_id"
    );


const allSchedules =
    Array.from(
        scheduleSelect.options
    );


serviceSelect.addEventListener(
    "change",
    function () {

        const selectedService =
            this.value;


        scheduleSelect.innerHTML = "";


        if (selectedService === "") {

            const option =
                document.createElement(
                    "option"
                );

            option.value = "";

            option.textContent =
                "Select a service first";

            scheduleSelect.appendChild(
                option
            );

            return;
        }


        const defaultOption =
            document.createElement(
                "option"
            );

        defaultOption.value = "";

        defaultOption.textContent =
            "Select an available schedule";

        scheduleSelect.appendChild(
            defaultOption
        );


        allSchedules.forEach(
            function (option) {

                if (
                    option.dataset.service ===
                    selectedService
                ) {

                    scheduleSelect.appendChild(
                        option.cloneNode(true)
                    );

                }

            }
        );

    }
);


/*
|--------------------------------------------------------------------------
| Validate Resident Selection
|--------------------------------------------------------------------------
*/

document.querySelector("form").addEventListener(
    "submit",
    function (event) {

        if (residentId.value === "") {

            event.preventDefault();

            alert(
                "Please select a resident."
            );

            residentSelectMenu.classList.add(
                "show"
            );

            residentArrow.textContent =
                "▲";

            residentSearch.focus();

        }

    }
);

</script>


</body>

</html>