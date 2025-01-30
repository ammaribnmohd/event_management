<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: auth/login.php');
    exit();
}


$orderBy = $_GET['sort'] ?? 'event_date';
$orderDir = $_GET['order'] ?? 'DESC';


$allowedColumns = ['title', 'event_date'];
$allowedDirections = ['ASC', 'DESC'];
if (!in_array($orderBy, $allowedColumns) || !in_array($orderDir, $allowedDirections)) {
    $orderBy = 'event_date';
    $orderDir = 'DESC';
}

$db = getDBConnection();

$stmtEvents = $db->prepare("
    SELECT 
        e.*, 
        COUNT(a.id) as attendee_count 
    FROM events e 
    LEFT JOIN attendees a ON e.id = a.event_id 
    GROUP BY e.id 
    ORDER BY $orderBy $orderDir
");
$stmtEvents->execute();
$events = $stmtEvents->fetchAll(PDO::FETCH_ASSOC);

$stmtAttendees = $db->prepare("
    SELECT a.*, e.title as event_title, e.event_date, e.location 
    FROM attendees a
    LEFT JOIN events e ON a.event_id = e.id
    ORDER BY a.registration_date DESC
");
$stmtAttendees->execute();
$allAttendees = $stmtAttendees->fetchAll(PDO::FETCH_ASSOC);
?>

<?php require_once '../templates/header.php'; ?>

<div class="container mt-5 ">
    <div class="row">
        <div class="col-md-8">
            <h1>Admin Dashboard</h1>
        </div>
        <div class="col-md-4 d-flex align-items-center gap-2 justify-content-md-end flex-wrap">
            <a href="events/create.php" class="btn btn-success">Create New Event</a>
            <button id="toggleView" class="btn btn-info">View All Attendees</button>
            <a href="auth/logout.php" class="btn btn-danger">Logout</a>
        </div>
    </div>

    <!-- Events Section -->
    <div id="eventsSection">
    <h2 class="mb-3">All Events</h2>
    <div class="row align-items-center mb-3">
    <!-- Search Bar -->
    <div class="col-12 col-md-4 mb-2 mb-md-0">
        <input type="text" id="searchEvents" class="form-control" placeholder="Search events by name...">
    </div>

    <!-- Sorting Buttons -->
    <div class="col-12 col-md-8">
        <div class="row g-2">
            <div class="col-6 col-md-auto">
                <div class="dropdown">
                    <button class="btn btn-primary w-100" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
                        Sort By ▼
                    </button>
                    <ul class="dropdown-menu w-100">
                        <li><a class="dropdown-item" href="?sort=title&order=<?= $orderDir ?>">Title</a></li>
                        <li><a class="dropdown-item" href="?sort=event_date&order=<?= $orderDir ?>">Date</a></li>
                    </ul>
                </div>
            </div>

            <!-- Toggle Order Button -->
            <div class="col-6 col-md-auto">
                <a href="?sort=<?= $orderBy ?>&order=<?= $orderDir === 'ASC' ? 'DESC' : 'ASC' ?>" 
                   class="btn btn-primary w-100 text-center">
                    <?= $orderDir === 'ASC' ? 'Descending' : 'Ascending' ?>
                </a>
            </div>
        </div>
    </div>
</div>



        <table class="table table-striped mb-5">
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Date</th>
                    <th>Location</th>
                    <th>Capacity</th>
                    <th>Registered</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($events as $event): ?>
                    <tr>
                        <td><?= htmlspecialchars($event['title']) ?></td>
                        <td><?= $event['event_date'] ?> <?= $event['event_time'] ?></td>
                        <td><?= htmlspecialchars($event['location']) ?></td>
                        <td><?= $event['max_capacity'] ?></td>
                        <td><?= $event['attendee_count'] ?></td>
                        <td>
                            <button class="btn btn-sm btn-warning view-attendees"
                                data-event-id="<?= $event['id'] ?>"
                                data-event-title="<?= htmlspecialchars($event['title']) ?>">
                                Attendees
                            </button>
                            <a href="events/edit.php?id=<?= $event['id'] ?>"
                                class="btn btn-sm btn-primary">Edit</a>
                            <a href="events/reports.php?id=<?= $event['id'] ?>"
                                class="btn btn-sm btn-info">Report</a>
                            <a href="events/delete.php?id=<?= $event['id'] ?>"
                                class="btn btn-sm btn-danger"
                                onclick="return confirm('Are you sure?')">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Attendees Section -->
    <div id="attendeesSection" class="d-none">
        <h2 class="mb-3">All Attendees</h2>
        <div class="col-md-4">
            <input type="text" id="searchAttendees" class="form-control mb-2" placeholder="Search attendees by name...">
        </div>
        <table class="table table-striped table-hover mb-5">
            <thead class="thead-dark">
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Event</th>
                    <th>Event Date</th>
                    <th>Location</th>
                    <th>Registration Date</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($allAttendees as $attendee): ?>
                    <tr>
                        <td><?= htmlspecialchars($attendee['name']) ?></td>
                        <td><?= htmlspecialchars($attendee['email']) ?></td>
                        <td>
                            <?php if ($attendee['event_title']): ?>
                                <a href="events/edit.php?id=<?= $attendee['event_id'] ?>">
                                    <?= htmlspecialchars($attendee['event_title']) ?>
                                </a>
                            <?php else: ?>
                                <span class="text-muted">Event deleted</span>
                            <?php endif; ?>
                        </td>
                        <td><?= $attendee['event_date'] ?? 'N/A' ?></td>
                        <td><?= htmlspecialchars($attendee['location'] ?? 'N/A') ?></td>
                        <td><?= date('M j, Y g:i a', strtotime($attendee['registration_date'])) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Attendees Modal -->
<div class="modal fade" id="attendeesModal" tabindex="-1" aria-labelledby="attendeesModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="attendeesModalLabel">Attendees for <span id="eventTitle"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="loading" class="text-center">
                    <div class="spinner-border" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
                <table class="table table-striped d-none" id="attendeesTable">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Registration Date</th>
                        </tr>
                    </thead>
                    <tbody id="attendeesList">
                    </tbody>
                </table>
                <div id="noAttendees" class="d-none text-center text-muted">
                    No attendees registered for this event yet.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Toggle between views
        const toggleButton = document.getElementById('toggleView');
        const eventsSection = document.getElementById('eventsSection');
        const attendeesSection = document.getElementById('attendeesSection');
        let showingEvents = true;

        toggleButton.addEventListener('click', function() {
            showingEvents = !showingEvents;
            eventsSection.classList.toggle('d-none', !showingEvents);
            attendeesSection.classList.toggle('d-none', showingEvents);
            toggleButton.textContent = showingEvents ? 'View All Attendees' : 'View Events';
        });

        // Dropdown functionality
        const dropdownButton = document.getElementById('dropdownMenuButton');
        if (dropdownButton) {
            dropdownButton.addEventListener('click', function() {
                const dropdownMenu = this.nextElementSibling;
                dropdownMenu.classList.toggle('show');
            });

            document.addEventListener('click', function(e) {
                if (!dropdownButton.contains(e.target)) {
                    const dropdownMenu = dropdownButton.nextElementSibling;
                    dropdownMenu.classList.remove('show');
                }
            });
        }

        // Attendees modal functionality
        const modal = new bootstrap.Modal('#attendeesModal');
        document.querySelectorAll('.view-attendees').forEach(button => {
            button.addEventListener('click', async function() {
                const eventId = this.dataset.eventId;
                const eventTitle = this.dataset.eventTitle;
                const modalBody = document.querySelector('#attendeesModal .modal-body');

                // Reset modal state
                modalBody.querySelector('#attendeesTable').classList.add('d-none');
                modalBody.querySelector('#noAttendees').classList.add('d-none');
                modalBody.querySelector('#loading').classList.remove('d-none');
                modalBody.querySelector('#attendeesList').innerHTML = '';

                document.getElementById('eventTitle').textContent = eventTitle;

                try {
                    const response = await fetch(`../api/get_attendees.php?event_id=${eventId}`);
                    const data = await response.json();

                    if (data.success) {
                        if (data.attendees.length > 0) {
                            data.attendees.forEach(attendee => {
                                const row = document.createElement('tr');
                                row.innerHTML = `
                                <td>${attendee.name}</td>
                                <td>${attendee.email}</td>
                                <td>${new Date(attendee.registration_date).toLocaleString()}</td>
                            `;
                                modalBody.querySelector('#attendeesList').appendChild(row);
                            });
                            modalBody.querySelector('#attendeesTable').classList.remove('d-none');
                        } else {
                            modalBody.querySelector('#noAttendees').classList.remove('d-none');
                        }
                    } else {
                        showError('Error loading attendees: ' + data.message);
                    }
                } catch (error) {
                    showError('Error loading attendees: ' + error.message);
                } finally {
                    modalBody.querySelector('#loading').classList.add('d-none');
                }

                modal.show();
            });
        });

        function showError(message) {
            const alert = document.createElement('div');
            alert.className = 'alert alert-danger alert-dismissible fade show';
            alert.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
            document.querySelector('#attendeesModal .modal-body').prepend(alert);
        }
    });
    document.getElementById('searchEvents').addEventListener('input', function(e) {
        const searchTerm = e.target.value.toLowerCase();
        document.querySelectorAll('#eventsSection tbody tr').forEach(row => {
            const title = row.cells[0].textContent.toLowerCase();
            row.style.display = title.includes(searchTerm) ? '' : 'none';
        });
    });

    document.getElementById('searchAttendees').addEventListener('input', function(e) {
        const searchTerm = e.target.value.toLowerCase();
        document.querySelectorAll('#attendeesSection tbody tr').forEach(row => {
            const name = row.cells[0].textContent.toLowerCase();
            row.style.display = name.includes(searchTerm) ? '' : 'none';
        });
    });

    document.getElementById('toggleView').addEventListener('click', function() {
        document.getElementById('searchEvents').value = '';
        document.getElementById('searchAttendees').value = '';
        document.querySelectorAll('tbody tr').forEach(row => row.style.display = '');
    });
</script>

<?php require_once '../templates/footer.php'; ?>