<?php
require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../includes/validation.php';

$error = '';
$success = '';
$event = null;

if (!isset($_GET['id'])) {
    header('Location: ../index.php');
    exit();
}

$db = getDBConnection();

// Fetch event details
$stmt = $db->prepare("SELECT * FROM events WHERE id = ?");
$stmt->execute([$_GET['id']]);
$event = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$event) {
    header('Location: ../index.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    header('Content-Type: application/json');

    if (isEventFull($_GET['id'])) {
        echo json_encode(['success' => false, 'message' => 'Sorry, this event is already at full capacity.']);
        exit();
    } else {
        $name = sanitizeInput($_POST['name']);
         // Convert email to lowercase before sanitizing and saving
        $email = strtolower(sanitizeInput($_POST['email']));
        $phone = sanitizeInput($_POST['phone']);

         // Server-side validation
        $errors = [];
        if (empty($name)) $errors[] = 'Name is required';
        if (empty($email)) {
            $errors[] = 'Email is required';
        } else if (!validateEmail($email)) {
            $errors[] = 'Invalid email format';
        }
        if (empty($phone)) {
            $errors[] = 'Phone is required';
        }else if (!validatePhone($phone)) {
            $errors[] = 'Invalid phone number format';
        }


         if (empty($errors)) {
            $stmt = $db->prepare("
                INSERT INTO attendees (event_id, name, email, phone)
                VALUES (?, ?, ?, ?)
            ");

            if ($stmt->execute([$_GET['id'], $name, $email, $phone])) {
                echo json_encode(['success' => true, 'message' => 'Registration successful!']);
                exit();
            } else {
                echo json_encode(['success' => false, 'message' => 'Registration failed. Please try again.']);
                exit();
            }
        }else{
            echo json_encode(['success' => false, 'message' => implode("<br>", $errors)]);
            exit();
        }
    }
}


require_once '../templates/header.php';
?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3>Register for <?= htmlspecialchars($event['title']) ?></h3>
                </div>
                <div class="card-body">
                    <div id="message"></div>

                    <!-- Registration Form -->
                    <?php if (!isEventFull($_GET['id'])): ?>
                        <form id="registrationForm" method="POST" class="needs-validation" novalidate>
                            <div class="form-group">
                                <label>Name</label>
                                <input type="text" name="name" class="form-control" required>
                                <div class="invalid-feedback">Please enter your name.</div>
                            </div>
                            <div class="form-group">
                                <label>Email</label>
                                <input type="email" name="email" class="form-control" required>
                                <div class="invalid-feedback">Please enter a valid email address.</div>
                            </div>
                            <div class="form-group">
                                <label>Phone</label>
                                <input type="tel" name="phone" class="form-control" required>
                                <div class="invalid-feedback">Please enter a valid phone number.</div>
                            </div>
                            <button type="submit" class="btn btn-primary mt-2">Register</button>
                            <a href="../index.php" class="btn btn-secondary mt-2">Cancel</a>
                        </form>
                    <?php else: ?>
                        <div class="alert alert-warning">
                            This event is already at full capacity.
                        </div>
                        <a href="../index.php" class="btn btn-primary">Back to Events</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    $(document).ready(function() {
        $('#registrationForm').on('submit', function(e) {
            e.preventDefault();

            var formData = $(this).serialize();

            $.ajax({
                url: 'register.php?id=<?= $_GET['id'] ?>',
                type: 'POST',
                data: formData,
                dataType: 'json',
                success: function(result) {
                    if (result.success) {
                        var successHtml = '<div class="alert alert-success">' + result.message + '</div>' +
                            '<a href="../index.php" class="btn btn-primary">Back to Events</a>';

                        $('#message').html(successHtml);
                        $('#registrationForm').hide();
                    } else {
                        $('#message').html('<div class="alert alert-danger">' + result.message + '</div>');
                    }
                },
                error: function() {
                    $('#message').html('<div class="alert alert-danger">An error occurred. Please try again.</div>');
                }
            });
        });
    });
</script>
<?php require_once '../templates/footer.php'; ?>