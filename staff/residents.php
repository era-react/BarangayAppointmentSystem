<?php

require_once "../config.php";

require_staff();

$message = "";


/*
|--------------------------------------------------------------------------
| Approve or Reject Resident Account
|--------------------------------------------------------------------------
*/

if (isset($_GET["action"]) && isset($_GET["id"])) {

    $action = $_GET["action"];
    $resident_id = (int) $_GET["id"];

    if ($action === "approve") {

        $new_status = "Active";

    } elseif ($action === "reject") {

        $new_status = "Rejected";

    } else {

        $new_status = "";
    }


    if ($new_status !== "" && $resident_id > 0) {

        $stmt = $conn->prepare(
            "UPDATE users
             SET status = ?
             WHERE id = ?
             AND role = 'resident'"
        );

        $stmt->bind_param(
            "si",
            $new_status,
            $resident_id
        );


        if ($stmt->execute()) {

            $message =
                "Resident account status updated successfully.";

        } else {

            $message =
                "Failed to update resident account.";
        }


        $stmt->close();
    }
}


/*
|--------------------------------------------------------------------------
| Get Resident Accounts
|--------------------------------------------------------------------------
*/

$result = $conn->query(
    "SELECT
        id,
        full_name,
        email,
        contact_number,
        house_unit_no,
        street,
        purok,
        barangay,
        city_municipality,
        province,
        status,
        created_at
     FROM users
     WHERE role = 'resident'
     ORDER BY created_at DESC"
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

    <title>Resident Accounts</title>

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


            <a
                href="residents.php"
                class="active"
            >
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
                    Resident Accounts
                </h1>

                <p>
                    Review resident registrations
                    and manage account approval status.
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
                 RESIDENT ACCOUNTS
            ================================== -->

            <section class="section">


                <h2 class="section-title">
                    Registered Residents
                </h2>


                <?php if ($result->num_rows > 0): ?>


                    <div class="resident-table-wrapper">

    <div class="resident-table-scroll-top">
        <div></div>
    </div>

    <div class="table-container resident-table-container">

        <table>


                            <tr>

                                <th>
                                    ID
                                </th>

                                <th>
                                    Full Name
                                </th>

                                <th>
                                    Email
                                </th>

                                <th>
                                    Contact Number
                                </th>

                                <th>
                                    Address
                                </th>

                                <th>
                                    Status
                                </th>

                                <th>
                                    Action
                                </th>

                            </tr>


                            <?php while ($resident = $result->fetch_assoc()): ?>


                                <tr>


                                    <!-- ID -->

                                    <td>
                                        <?php
                                        echo e(
                                            $resident["id"]
                                        );
                                        ?>
                                    </td>


                                    <!-- Full Name -->

                                    <td>

                                        <strong>
                                            <?php
                                            echo e(
                                                $resident["full_name"]
                                            );
                                            ?>
                                        </strong>

                                    </td>


                                    <!-- Email -->

                                    <td>
                                        <?php
                                        echo e(
                                            $resident["email"]
                                        );
                                        ?>
                                    </td>


                                    <!-- Contact -->

                                    <td>
                                        <?php
                                        echo e(
                                            $resident["contact_number"]
                                        );
                                        ?>
                                    </td>


                                    <!-- Address -->

                                    <td>

                                        <?php
                                        echo e(
                                            $resident["house_unit_no"]
                                        );
                                        ?>


                                        <?php
                                        if (
                                            $resident["street"] !== ""
                                        ):
                                        ?>

                                            ,
                                            <?php
                                            echo e(
                                                $resident["street"]
                                            );
                                            ?>

                                        <?php endif; ?>


                                        <br>


                                        <strong>
                                            Purok / Sitio:
                                        </strong>

                                        <?php
                                        echo e(
                                            $resident["purok"]
                                        );
                                        ?>


                                        <br>


                                        <strong>
                                            Barangay:
                                        </strong>

                                        <?php
                                        echo e(
                                            $resident["barangay"]
                                        );
                                        ?>


                                        <br>


                                        <?php
                                        echo e(
                                            $resident["city_municipality"]
                                        );
                                        ?>


                                        <br>


                                        <?php
                                        echo e(
                                            $resident["province"]
                                        );
                                        ?>

                                    </td>


                                    <!-- Status -->

                                    <td>


                                        <?php
                                        if (
                                            $resident["status"]
                                            === "Pending"
                                        ):
                                        ?>

                                            <span
                                                class="status status-pending"
                                            >
                                                Pending
                                            </span>


                                        <?php
                                        elseif (
                                            $resident["status"]
                                            === "Active"
                                        ):
                                        ?>

                                            <span
                                                class="status status-approved"
                                            >
                                                Active
                                            </span>


                                        <?php
                                        elseif (
                                            $resident["status"]
                                            === "Rejected"
                                        ):
                                        ?>

                                            <span
                                                class="status status-rejected"
                                            >
                                                Rejected
                                            </span>

                                        <?php endif; ?>


                                    </td>


                                    <!-- Action -->

                                    <td>


                                        <?php
                                        if (
                                            $resident["status"]
                                            === "Pending"
                                        ):
                                        ?>


                                            <a
                                                href="residents.php?action=approve&id=<?php echo $resident["id"]; ?>"
                                                class="button"
                                                onclick="return confirm('Are you sure you want to approve this resident account?');"
                                            >
                                                Approve
                                            </a>


                                            <br>
                                            <br>


                                            <a
                                                href="residents.php?action=reject&id=<?php echo $resident["id"]; ?>"
                                                class="button button-danger"
                                                onclick="return confirm('Are you sure you want to reject this resident account?');"
                                            >
                                                Reject
                                            </a>


                                        <?php
                                        elseif (
                                            $resident["status"]
                                            === "Active"
                                        ):
                                        ?>


                                            <span
                                                class="status status-approved"
                                            >
                                                Active Account
                                            </span>


                                        <?php
                                        elseif (
                                            $resident["status"]
                                            === "Rejected"
                                        ):
                                        ?>


                                            <a
                                                href="residents.php?action=approve&id=<?php echo $resident["id"]; ?>"
                                                class="button"
                                                onclick="return confirm('Are you sure you want to approve this resident account again?');"
                                            >
                                                Approve Again
                                            </a>


                                        <?php endif; ?>


                                    </td>


                                </tr>


                            <?php endwhile; ?>


                        </table>

                        </div>

                    </div>


                <?php else: ?>


                    <div class="card">

                        <p>
                            No resident accounts found.
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
                Resident Account Management
            </p>

        </footer>


    </div>

<script>

    const residentTopScroll =
        document.querySelector(
            ".resident-table-scroll-top"
        );

    const residentTableContainer =
        document.querySelector(
            ".resident-table-container"
        );

    const residentTable =
        document.querySelector(
            ".resident-table-container table"
        );


    function updateResidentTopScrollWidth() {

        if (
            !residentTopScroll ||
            !residentTable
        ) {
            return;
        }

        const scrollContent =
            residentTopScroll.querySelector("div");

        scrollContent.style.width =
            residentTable.scrollWidth + "px";
    }


    if (
        residentTopScroll &&
        residentTableContainer
    ) {

        residentTopScroll.addEventListener(
            "scroll",
            function () {

                residentTableContainer.scrollLeft =
                    residentTopScroll.scrollLeft;

            }
        );


        residentTableContainer.addEventListener(
            "scroll",
            function () {

                residentTopScroll.scrollLeft =
                    residentTableContainer.scrollLeft;

            }
        );


        window.addEventListener(
            "resize",
            updateResidentTopScrollWidth
        );


        updateResidentTopScrollWidth();

    }

</script>

</body>

</html>