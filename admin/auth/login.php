<?php
session_start();
require_once '../../config/database.php';
require_once '../../includes/functions.php';

$error = '';
$formData = [];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Store form data in session
    $_SESSION['form_data'] = $_POST;

    $username = sanitizeInput($_POST['username']);
    $password = $_POST['password'];
    
    $errors = [];
    if(empty($username)) $errors['username'] = "Username is required";
    if(empty($password)) $errors['password'] = "Password is required";

    if (!empty($errors)) {
         $error = implode("<br>", $errors);
         $formData = $_SESSION['form_data']; // retrieve the data to prepopulate
          if (isset($errors['username'])) {
                $formData['username'] = '';
            }
            if (isset($errors['password'])) {
                $formData['password'] = '';
            }
        unset($_SESSION['form_data']); // clear form data on validation error
    } else {
        $db = getDBConnection();
        $stmt = $db->prepare("SELECT id, username, password FROM admins WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['admin_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            unset($_SESSION['form_data']); // clear form data
            header('Location: ../dashboard.php');
            exit();
        } else {
             $error = 'Invalid username or password';
             unset($_SESSION['form_data']); // clear form data on incorrect credential
             $formData = [];
        }
    }
}

// Get previous errors from the session if set
$errors = $_SESSION['errors'] ?? [];
unset($_SESSION['errors']);
?>

<?php require_once '../../templates/header.php'; ?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3>Admin Login</h3>
                </div>
                <div class="card-body">
                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?= $error ?></div>
                    <?php endif; ?>
                    
                    <form method="POST" class="needs-validation" novalidate>
                        <div class="form-group">
                            <label>Username</label>
                            <input type="text" name="username" class="form-control" value="<?= isset($formData['username']) ? htmlspecialchars($formData['username']) : '' ?>" required>
                            <div class="invalid-feedback"><?= $errors['username'] ?? '' ?></div>
                        </div>
                        <div class="form-group">
                            <label>Password</label>
                            <input type="password" name="password" class="form-control" value="<?= isset($formData['password']) ? htmlspecialchars($formData['password']) : '' ?>" required>
                            <div class="invalid-feedback"><?= $errors['password'] ?? '' ?></div>
                        </div>
                        <button type="submit" class="btn btn-primary mt-2">Login</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../../templates/footer.php'; ?>