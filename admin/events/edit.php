<?php
session_start();
require_once '../../config/database.php';
require_once '../../includes/functions.php';
require_once '../../includes/validation.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: ../auth/login.php');
    exit();
}

$error = '';
$success = '';
$event = null;

if (!isset($_GET['id'])) {
    header('Location: ../dashboard.php');
    exit();
}

$db = getDBConnection();

// Fetch event details
if ($_SERVER['REQUEST_METHOD'] == 'GET') {
     // Clear previous form data from session
     unset($_SESSION['form_data']);
    unset($_SESSION['errors']);

    $stmt = $db->prepare("SELECT * FROM events WHERE id = ?");
    $stmt->execute([$_GET['id']]);
    $event = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$event) {
        header('Location: ../dashboard.php');
        exit();
    }

    // Format the time from db to make sure it is in HH:MM format
     if(isset($event['event_time'])) {
        $timeObj = DateTime::createFromFormat('H:i:s', $event['event_time']);
        $event['event_time'] = $timeObj ? $timeObj->format('H:i') : '';
    }
}

// Update event
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Store form data in session
    $_SESSION['form_data'] = $_POST;

    $title = sanitizeInput($_POST['title']);
    $description = sanitizeInput($_POST['description']);
    $event_date = $_POST['event_date'];
    $event_time = $_POST['event_time'];
    $location = sanitizeInput($_POST['location']);
    $max_capacity = $_POST['max_capacity'];

     // Server-side validation
    $errors = [];

    if (empty($title)) $errors['title'] = 'Title is required';
    if (empty($description)) $errors['description'] = 'Description is required';
    if (!validateDate($event_date)) $errors['event_date'] = 'Invalid event date';
     if (empty($event_time)){
        $errors['event_time'] = 'Event time is required';
    }
    else if (!validateTime($event_time)) $errors['event_time'] = 'Invalid event time';
    if (empty($location)) $errors['location'] = 'Location is required';
    if (!validateCapacity($max_capacity)) $errors['max_capacity'] = 'Invalid maximum capacity';

    $_SESSION['errors'] = $errors;

     if (empty($errors)) {
         //Clear form data session
        unset($_SESSION['form_data']);
        unset($_SESSION['errors']);
        $stmt = $db->prepare("
            UPDATE events 
            SET title = ?, description = ?, event_date = ?, 
                event_time = ?, location = ?, max_capacity = ?
            WHERE id = ?
        ");

        if ($stmt->execute([$title, $description, $event_date, $event_time, 
                           $location, (int)$max_capacity, $_GET['id']])) {
            $success = 'Event updated successfully';
            header('Location: ../dashboard.php');
            exit();
             // Refresh event data
            $stmt = $db->prepare("SELECT * FROM events WHERE id = ?");
            $stmt->execute([$_GET['id']]);
            $event = $stmt->fetch(PDO::FETCH_ASSOC);

            if(isset($event['event_time'])) {
               $timeObj = DateTime::createFromFormat('H:i:s', $event['event_time']);
               $event['event_time'] = $timeObj ? $timeObj->format('H:i') : '';
           }
        } else {
            $error = 'Failed to update event';
        }
    } else {
       $error = implode("<br>", $errors);
    }
}

// Retrieve form data from session
$formData = $_SESSION['form_data'] ?? [];
$errors = $_SESSION['errors'] ?? [];
?>

<?php require_once '../../templates/header.php'; ?>

<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3>Edit Event</h3>
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
                            <input type="text" name="title" class="form-control"
                                   value="<?= isset($formData['title']) ? htmlspecialchars($formData['title']) : (isset($event['title']) ? htmlspecialchars($event['title']) : '') ?>" required>
                             <div class="invalid-feedback"><?= $errors['title'] ?? '' ?></div>
                        </div>
                        <div class="form-group">
                            <label>Description</label>
                            <textarea name="description" class="form-control" rows="4" required><?= 
                                isset($formData['description']) ? htmlspecialchars($formData['description']) : (isset($event['description']) ? htmlspecialchars($event['description']) : '')
                            ?></textarea>
                            <div class="invalid-feedback"><?= $errors['description'] ?? '' ?></div>
                        </div>
                        <div class="form-group">
                            <label>Date</label>
                            <input type="date" name="event_date" class="form-control"
                                   value="<?= isset($formData['event_date']) ? $formData['event_date'] :(isset($event['event_date']) ? $event['event_date'] : '') ?>" required>
                               <div class="invalid-feedback"><?= $errors['event_date'] ?? '' ?></div>
                        </div>
                        <div class="form-group">
                            <label>Time</label>
                            <input type="time" name="event_time" class="form-control"
                                   value="<?= isset($formData['event_time']) ? $formData['event_time'] : (isset($event['event_time']) ? $event['event_time'] : '') ?>" required>
                             <div class="invalid-feedback"><?= $errors['event_time'] ?? '' ?></div>
                        </div>
                        <div class="form-group">
                            <label>Location</label>
                            <input type="text" name="location" class="form-control"
                                   value="<?= isset($formData['location']) ? htmlspecialchars($formData['location']) :(isset($event['location']) ? htmlspecialchars($event['location']) : '') ?>" required>
                                <div class="invalid-feedback"><?= $errors['location'] ?? '' ?></div>
                        </div>
                        <div class="form-group">
                            <label>Maximum Capacity</label>
                            <input type="number" name="max_capacity" class="form-control"
                                   value="<?= isset($formData['max_capacity']) ? $formData['max_capacity'] :(isset($event['max_capacity']) ? $event['max_capacity'] : '') ?>" required min="1">
                               <div class="invalid-feedback"><?= $errors['max_capacity'] ?? '' ?></div>
                        </div>
                        <button type="submit" class="btn btn-primary mt-2">Update Event</button>
                        <a href="../dashboard.php" class="btn btn-secondary mt-2">Cancel</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../../templates/footer.php'; ?>