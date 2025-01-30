<?php
session_start();
require_once '../../config/database.php';
require_once '../../includes/functions.php';

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
    $max_capacity = (int)$_POST['max_capacity'];
    
    $db = getDBConnection();
    $stmt = $db->prepare("
        INSERT INTO events (title, description, event_date, event_time, location, max_capacity, created_by)
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");
    
    if ($stmt->execute([$title, $description, $event_date, $event_time, $location, $max_capacity, $_SESSION['admin_id']])) {
        $success = 'Event created successfully';
        header('Location: ../dashboard.php'); // Redirect to admin dashboard
        exit();
    } else {
        $error = 'Failed to create event';
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
                    
                    <form method="POST">
                        <div class="form-group">
                            <label>Title</label>
                            <input type="text" name="title" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>Description</label>
                            <textarea name="description" class="form-control" rows="4" required></textarea>
                        </div>
                        <div class="form-group">
                            <label>Date</label>
                            <input type="date" name="event_date" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>Time</label>
                            <input type="time" name="event_time" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>Location</label>
                            <input type="text" name="location" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>Maximum Capacity</label>
                            <input type="number" name="max_capacity" class="form-control" required>
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