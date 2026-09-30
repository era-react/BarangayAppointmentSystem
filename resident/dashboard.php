<?php

require_once "../config.php";

require_resident();

$resident_id = $_SESSION["user_id"];

$message = "";


/*
|--------------------------------------------------------------------------
| Cancel Appointment
|--------------------------------------------------------------------------
*/

if (isset($_GET["cancel"])) {

    $appointment_id = (int) $_GET["cancel"];

    if ($appointment_id > 0) {

        $stmt = $conn->prepare(
            "UPDATE appointments
             SET status = 'Cancelled'
             WHERE id = ?
             AND resident_id = ?
             AND status IN ('Pending', 'Approved')"
        );

        $stmt->bind_param(
            "ii",
            $appointment_id,
            $resident_id
        );

        if ($stmt->execute()) {

            if ($stmt->affected_rows > 0) {

                $message =
                    "Appointment cancelled successfully.";

            } else {

                $message =
                    "This appointment cannot be cancelled.";
            }

        } else {

            $message =
                "Failed to cancel appointment.";
        }

        $stmt->close();
    }
}


/*
|--------------------------------------------------------------------------
| Get Available Services
|--------------------------------------------------------------------------
*/

$services = $conn->query(
    "SELECT id, name, description
     FROM services
     WHERE active = 1
     ORDER BY name ASC"
);


/*
|--------------------------------------------------------------------------
| Get Resident Appointments
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare(
    "SELECT
        appointments.id,
        services.name AS service_name,
        schedules.schedule_date,
        schedules.schedule_time,
        appointments.status,
        appointments.notes,
        appointments.processing_notes

     FROM appointments

     INNER JOIN services
        ON appointments.service_id = services.id

     INNER JOIN schedules
        ON appointments.schedule_id = schedules.id

     WHERE appointments.resident_id = ?

     ORDER BY appointments.created_at DESC"
);

$stmt->bind_param(
    "i",
    $resident_id
);

$stmt->execute();

$appointments = $stmt->get_result();

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
        Resident Dashboard -
        Barangay Appointment Management System
    </title>

    <link
        rel="stylesheet"
        href="../assets/style.css"
    >


<style>
/* Resident header: title left, profile right, welcome text below */
.staff-page-header.resident-header-row .resident-header-top {
    display: flex !important;
    flex-direction: row !important;
    align-items: center !important;
    justify-content: space-between !important;
    gap: 20px;
    margin: 0 0 12px 0;
}
.staff-page-header.resident-header-row .resident-header-top h1 {
    margin: 0 !important;
    line-height: 1.2;
}
.staff-page-header.resident-header-row .resident-header-account {
    display: flex !important;
    align-items: center !important;
    margin: 0 !important;
    padding: 0 !important;
    flex-shrink: 0;
}
.staff-page-header.resident-header-row .staff-account {
    display: flex !important;
    align-items: center !important;
    gap: 12px;
    text-decoration: none !important;
}
.staff-page-header.resident-header-row .staff-account-info {
    display: flex !important;
    flex-direction: column !important;
    gap: 4px;
    line-height: 1.2;
}
.staff-page-header.resident-header-row .staff-account-name {
    margin: 0 !important;
    color: #222222 !important;
    font-size: 14px !important;
    font-weight: 700 !important;
}
.staff-page-header.resident-header-row .staff-account-role {
    margin: 0 !important;
    color: #7a847f !important;
    font-size: 12px !important;
    font-weight: 400 !important;
}
@media (max-width: 600px) {
    .staff-page-header.resident-header-row .resident-header-top {
        flex-direction: column !important;
        align-items: flex-start !important;
    }
}
</style>
</head>

<body>


<!-- =========================================================
     RESIDENT SIDEBAR
========================================================= -->

<div class="sidebar">

    <div class="sidebar-brand">

        <h2>
            Barangay Appointment
            Management System
        </h2>

        <p>
            Resident Service Portal
        </p>

    </div>


    <!-- MAIN -->

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

    </div>


    <!-- ACCOUNT -->

    <div class="sidebar-section-title">
        Account
    </div>

    <div class="sidebar-menu">

        <a href="../profile.php">
            My Account
        </a>


        <a href="../change_password.php">
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
    href="../logout.php"
    class="logout-link"
>
    Logout
</a>

    </div>

</div>



<!-- =========================================================
     MAIN AREA
========================================================= -->

<div class="staff-layout">

    <div class="container">




        <!-- =================================================
             PAGE HEADER
        ================================================== -->

        <div class="staff-page-header resident-header-row">

            <div class="resident-header-top">

            <h1>
                Resident Dashboard
            </h1>

            <div class="resident-header-account">

            <a
                href="../profile.php"
                class="staff-account"
                aria-label="Open profile"
            >

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
                        Resident
                    </p>

                </div>

            </a>

        </div>

            </div>

            <p>
                Welcome,
                <strong>
                    <?php
                    echo e(
                        $_SESSION["full_name"]
                    );
                    ?>
                </strong>!
            </p>

            <p>
                Request a barangay service appointment
                and track your appointment status here.
            </p>

        </div>



        <!-- =================================================
             MESSAGE
        ================================================== -->

        <?php if ($message !== ""): ?>

            <div class="message">

                <?php
                echo e($message);
                ?>

            </div>

        <?php endif; ?>



        <!-- =================================================
             AVAILABLE SERVICES
        ================================================== -->

        <section class="section">

            <h2 class="section-title">
                Available Barangay Services
            </h2>


            <div class="service-grid">

                <?php if ($services->num_rows > 0): ?>

                    <?php while (
                        $service =
                        $services->fetch_assoc()
                    ): ?>

                        <div class="service-card">

                            <h3>

                                <?php

                                echo e(
                                    $service["name"]
                                );

                                ?>

                            </h3>


                            <p>

                                <?php

                                echo e(
                                    $service["description"]
                                );

                                ?>

                            </p>


                            <a
                                href="book.php?service_id=<?php echo $service["id"]; ?>"
                                class="button"
                            >
                                Request Appointment
                            </a>

                        </div>

                    <?php endwhile; ?>

                <?php else: ?>

                    <div class="card">

                        <p>
                            No barangay services are currently available.
                        </p>

                    </div>

                <?php endif; ?>

            </div>

        </section>



        <!-- =================================================
             MY APPOINTMENTS
        ================================================== -->

        <section class="section">

            <h2 class="section-title">
                My Appointments
            </h2>


            <?php if ($appointments->num_rows > 0): ?>

               <div
    class="resident-table-scroll-top"
    id="residentTableScrollTop"
>
    <div
        class="resident-table-scroll-top-inner"
        id="residentTableScrollTopInner"
    ></div>
</div>


<div
    class="table-container resident-table-container"
    id="residentTableScrollBottom"
>

    <table class="resident-appointments-table">

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
                                Status
                            </th>

                            <th>
                                Notes
                            </th>

                            <th>
                                Processing Notes
                            </th>

                            <th>
                                Action
                            </th>

                        </tr>


                        <?php while (
                            $appointment =
                            $appointments->fetch_assoc()
                        ): ?>

                            <tr>

                                <td>

                                    <?php

                                    echo e(
                                        $appointment["id"]
                                    );

                                    ?>

                                </td>


                                <td>

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


                                <td>

                                    <?php

                                    $status_class = "";

                                    if (
                                        $appointment["status"]
                                        === "Pending"
                                    ) {

                                        $status_class =
                                            "status-pending";

                                    } elseif (
                                        $appointment["status"]
                                        === "Approved"
                                    ) {

                                        $status_class =
                                            "status-approved";

                                    } elseif (
                                        $appointment["status"]
                                        === "Checked In"
                                    ) {

                                        $status_class =
                                            "status-approved";

                                    } elseif (
                                        $appointment["status"]
                                        === "Completed"
                                    ) {

                                        $status_class =
                                            "status-completed";

                                    } elseif (
                                        $appointment["status"]
                                        === "Claimed"
                                    ) {

                                        $status_class =
                                            "status-completed";

                                    } elseif (
                                        $appointment["status"]
                                        === "Cancelled"
                                    ) {

                                        $status_class =
                                            "status-cancelled";

                                    } elseif (
                                        $appointment["status"]
                                        === "Rejected"
                                    ) {

                                        $status_class =
                                            "status-rejected";
                                    }

                                    ?>


                                    <span
                                        class="status <?php
                                            echo $status_class;
                                        ?>"
                                    >

                                        <?php

                                        echo e(
                                            $appointment["status"]
                                        );

                                        ?>

                                    </span>


                                    <div
                                        class="appointment-status-message"
                                    >

                                        <?php if (
                                            $appointment["status"]
                                            === "Pending"
                                        ): ?>

                                            Awaiting staff approval.
                                            Please wait for approval before going to the barangay office.

                                        <?php elseif (
                                            $appointment["status"]
                                            === "Approved"
                                        ): ?>

                                            Appointment confirmed.
                                            Please come to the barangay office on the scheduled date and time.

                                        <?php elseif (
                                            $appointment["status"]
                                            === "Checked In"
                                        ): ?>

                                            You have been checked in at the barangay office.
                                            Your barangay service is now being prepared or processed.

                                        <?php elseif (
                                            $appointment["status"]
                                            === "Rejected"
                                        ): ?>

                                            Your appointment request was not approved.

                                        <?php elseif (
                                            $appointment["status"]
                                            === "Cancelled"
                                        ): ?>

                                            This appointment has been cancelled.

                                        <?php elseif (
                                            $appointment["status"]
                                            === "Completed"
                                        ): ?>

                                            The barangay service has been processed.
                                            Please wait for the completed service output to be available for claiming.

                                        <?php elseif (
                                            $appointment["status"]
                                            === "Claimed"
                                        ): ?>

                                            You have claimed or received the completed barangay service.

                                        <?php endif; ?>

                                    </div>

                                </td>


                                <td>

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


                                <td>

                                    <?php

                                    if (
                                        $appointment["processing_notes"]
                                        !== null &&
                                        $appointment["processing_notes"]
                                        !== ""
                                    ) {

                                        echo e(
                                            $appointment["processing_notes"]
                                        );

                                    } else {

                                        echo "—";
                                    }

                                    ?>

                                </td>


                                <td>

                                    <?php if (
                                        $appointment["status"]
                                            === "Pending" ||
                                        $appointment["status"]
                                            === "Approved"
                                    ): ?>

                                        <a
                                            href="dashboard.php?cancel=<?php echo $appointment["id"]; ?>"
                                            class="button button-danger"
                                            onclick="return confirm('Are you sure you want to cancel this appointment?');"
                                        >
                                            Cancel Appointment
                                        </a>

                                    <?php else: ?>

                                        No action

                                    <?php endif; ?>

                                </td>

                            </tr>

                        <?php endwhile; ?>

                    </table>

                </div>

            <?php else: ?>

                <div class="card">

                    <p>
                        You do not have any appointments yet.
                    </p>

                    <p>
                        Choose a barangay service above
                        to request an appointment.
                    </p>

                </div>

            <?php endif; ?>

        </section>


        <!-- =================================================
             FOOTER
        ================================================== -->

        <footer class="site-footer">

            <p>
                Barangay Appointment Management System
            </p>

            <p>
                Resident Service Portal
            </p>

        </footer>


    </div>

</div>



<!-- =========================================================
     CONTACT OFFICE JAVASCRIPT
========================================================= -->

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

<script>

/*
|--------------------------------------------------------------------------
| Resident Appointment Table Scroll Synchronization
|--------------------------------------------------------------------------
*/

const residentTableScrollTop =
    document.getElementById(
        "residentTableScrollTop"
    );

const residentTableScrollTopInner =
    document.getElementById(
        "residentTableScrollTopInner"
    );

const residentTableScrollBottom =
    document.getElementById(
        "residentTableScrollBottom"
    );

const residentAppointmentsTable =
    document.querySelector(
        ".resident-appointments-table"
    );


function updateResidentTableScrollWidth() {

    if (
        residentAppointmentsTable &&
        residentTableScrollTopInner
    ) {

        residentTableScrollTopInner.style.width =
            residentAppointmentsTable.scrollWidth + "px";

    }

}


if (
    residentTableScrollTop &&
    residentTableScrollBottom &&
    residentAppointmentsTable
) {

    residentTableScrollTop.addEventListener(
        "scroll",
        function () {

            residentTableScrollBottom.scrollLeft =
                residentTableScrollTop.scrollLeft;

        }
    );


    residentTableScrollBottom.addEventListener(
        "scroll",
        function () {

            residentTableScrollTop.scrollLeft =
                residentTableScrollBottom.scrollLeft;

        }
    );


    window.addEventListener(
        "resize",
        updateResidentTableScrollWidth
    );


    updateResidentTableScrollWidth();

}

</script>

</body>

</html>