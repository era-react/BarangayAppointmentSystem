<?php

require_once "../config.php";

require_staff();

$message = "";


/*
|--------------------------------------------------------------------------
| Add New Service
|--------------------------------------------------------------------------
*/

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $name = trim($_POST["name"]);
    $description = trim($_POST["description"]);

    if ($name === "") {

        $message = "Please enter a service name.";

    } else {

        $stmt = $conn->prepare(
            "INSERT INTO services (name, description)
             VALUES (?, ?)"
        );

        $stmt->bind_param(
            "ss",
            $name,
            $description
        );

        if ($stmt->execute()) {

            $message = "Service added successfully.";

        } else {

            $message = "Failed to add service.";
        }

        $stmt->close();
    }
}


/*
|--------------------------------------------------------------------------
| Activate / Deactivate Service
|--------------------------------------------------------------------------
*/

if (isset($_GET["toggle"])) {

    $service_id = (int) $_GET["toggle"];

    $stmt = $conn->prepare(
        "UPDATE services
         SET active = IF(active = 1, 0, 1)
         WHERE id = ?"
    );

    $stmt->bind_param(
        "i",
        $service_id
    );

    $stmt->execute();

    $stmt->close();

    header("Location: service.php");
    exit;
}


/*
|--------------------------------------------------------------------------
| Get All Services
|--------------------------------------------------------------------------
*/

$result = $conn->query(
    "SELECT id, name, description, active
     FROM services
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

    <title>Services</title>

    <link
        rel="stylesheet"
        href="../assets/style.css"
    >

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


            <a
                href="service.php"
                class="active"
            >
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


        <!-- =====================================
             MAIN CONTENT
        ====================================== -->

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
                    Barangay Services
                </h1>

                <p>
                    Add and manage the services available
                    for resident appointments.
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
                 ADD SERVICE
            ================================== -->

            <section class="form-card">

                <h2>
                    Add New Service
                </h2>


                <form
                    method="POST"
                    action=""
                >


                    <label for="name">
                        Service Name *
                    </label>


                    <input
                        type="text"
                        id="name"
                        name="name"
                        placeholder="Example: Barangay Clearance"
                        required
                    >


                    <label for="description">
                        Description
                    </label>


                    <textarea
                        id="description"
                        name="description"
                        rows="5"
                        placeholder="Enter a short description of the service."
                    ></textarea>


                    <br>


                    <button type="submit">
                        Add Service
                    </button>


                </form>

            </section>


            <!-- =================================
                 EXISTING SERVICES
            ================================== -->

            <section class="section">


                <h2 class="section-title">
                    Available Services
                </h2>


                <?php if ($result->num_rows > 0): ?>


                    <div class="table-container">

                        <table>


                            <tr>

                                <th>
                                    ID
                                </th>

                                <th>
                                    Service Name
                                </th>

                                <th>
                                    Description
                                </th>

                                <th>
                                    Status
                                </th>

                                <th>
                                    Action
                                </th>

                            </tr>


                            <?php while ($service = $result->fetch_assoc()): ?>


                                <tr>


                                    <!-- ID -->

                                    <td>
                                        <?php
                                        echo e(
                                            $service["id"]
                                        );
                                        ?>
                                    </td>


                                    <!-- Service Name -->

                                    <td>

                                        <strong>
                                            <?php
                                            echo e(
                                                $service["name"]
                                            );
                                            ?>
                                        </strong>

                                    </td>


                                    <!-- Description -->

                                    <td>
                                        <?php
                                        echo e(
                                            $service["description"]
                                        );
                                        ?>
                                    </td>


                                    <!-- Status -->

                                    <td>


                                        <?php
                                        if (
                                            $service["active"] == 1
                                        ):
                                        ?>

                                            <span
                                                class="status status-approved"
                                            >
                                                Active
                                            </span>


                                        <?php else: ?>


                                            <span
                                                class="status status-cancelled"
                                            >
                                                Inactive
                                            </span>


                                        <?php endif; ?>


                                    </td>


                                    <!-- Action -->

                                    <td>


                                        <?php
                                        if (
                                            $service["active"] == 1
                                        ):
                                        ?>


                                            <a
                                                href="service.php?toggle=<?php echo $service["id"]; ?>"
                                                class="button button-danger"
                                                onclick="return confirm('Are you sure you want to deactivate this service?');"
                                            >
                                                Deactivate
                                            </a>


                                        <?php else: ?>


                                            <a
                                                href="service.php?toggle=<?php echo $service["id"]; ?>"
                                                class="button"
                                                onclick="return confirm('Are you sure you want to activate this service?');"
                                            >
                                                Activate
                                            </a>


                                        <?php endif; ?>


                                    </td>


                                </tr>


                            <?php endwhile; ?>


                        </table>

                    </div>


                <?php else: ?>


                    <div class="card">

                        <p>
                            No services have been added yet.
                        </p>

                    </div>


                <?php endif; ?>


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
                Barangay Service Management
            </p>

        </footer>


    </div>


</body>

</html>