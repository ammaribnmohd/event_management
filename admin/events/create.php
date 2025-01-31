<?php
session_start();
require_once '../../config/database.php';
require_once '../../includes/functions.php';
require_once '../../includes/validation.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: /event_management/auth/login.php');
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = sanitizeInput($_POST['title']);
    $description = sanitizeInput($_POST['description']);
    $event_date = $_POST['event_date'];
    $event_time = $_POST['event_time'];
    $location = sanitizeInput($_POST['location']);
    $max_capacity = $_POST['max_capacity'];

    // Server-side validation
    $errors = [];

    if (empty($title)) $errors[] = 'Title is required';
    if (empty($description)) $errors[] = 'Description is required';
    if (!validateDate($event_date)) $errors[] = 'Invalid event date';
    if (!validateTime($event_time)) $errors[] = 'Invalid event time';
    if (empty($location)) $errors[] = 'Location is required';
    if (!validateCapacity($max_capacity)) $errors[] = 'Invalid maximum capacity';


    if (empty($errors)) {
        $db = getDBConnection();
        $stmt = $db->prepare("
            INSERT INTO events (title, description, event_date, event_time, location, max_capacity, created_by)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");

        if ($stmt->execute([$title, $description, $event_date, $event_time, $location, (int)$max_capacity, $_SESSION['admin_id']])) {
            $success = 'Event created successfully';
            header('Location: ../dashboard.php'); // Redirect to admin dashboard
            exit();
        } else {
            $error = 'Failed to create event';
        }
    } else {
        $error = implode("<br>", $errors);
    }
}
?>

<?php require_once '../../templates/header.php'; ?>

<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3>Create New Event</h3>
                </div>
                <div class="card-body">
                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?= $error ?></div>
                    <?php endif; ?>
                    <?php if ($success): ?>
                        <div class="alert alert-success"><?= $success ?></div>
                    <?php endif; ?>

                    <form method="POST" class="needs-validation" novalidate>
                        <div class="form-group">
                            <label>Title</label>
                            <input type="text" name="title" class="form-control" required>
                            <div class="invalid-feedback">Please enter a title.</div>
                        </div>
                        <div class="form-group">
                            <label>Description</label>
                            <textarea name="description" class="form-control" rows="4" required></textarea>
                            <div class="invalid-feedback">Please enter a description.</div>
                        </div>
                        <div class="form-group">
                            <label>Date</label>
                            <input type="date" name="event_date" class="form-control" required>
                            <div class="invalid-feedback">Please select a valid date.</div>
                        </div>
                        <div class="form-group">
                            <label>Time</label>
                            <input type="time" name="event_time" class="form-control" required>
                            <div class="invalid-feedback">Please select a valid time.</div>
                        </div>
                        <div class="form-group">
                            <label>Location</label>
                            <input type="text" name="location" class="form-control" required>
                            <div class="invalid-feedback">Please enter a location.</div>
                        </div>
                        <div class="form-group">
                            <label>Maximum Capacity</label>
                            <input type="number" name="max_capacity" class="form-control" required min="1">
                            <div class="invalid-feedback">Please enter a valid capacity.</div>
                        </div>
                        <button type="submit" class="btn btn-primary mt-2">Create Event</button>
                        <a href="../dashboard.php" class="btn btn-secondary mt-2">Cancel</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../../templates/footer.php'; ?>