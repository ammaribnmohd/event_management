<?php
session_start();
require_once '../../config/database.php';
require_once '../../includes/functions.php';
require_once '../../includes/validation.php';

$error = '';
$success = '';
$formData = [];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Store form data in session
    $_SESSION['form_data'] = $_POST;

    $username = sanitizeInput($_POST['username']);
    // Convert email to lowercase before sanitizing and saving
    $email = strtolower(sanitizeInput($_POST['email']));
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
     
      // Server-side validation
    $errors = [];
    if (empty($username)) $errors['username'] = 'Username is required';
    if (empty($email)){
         $errors['email'] = 'Email is required';
    }else if(!validateEmail($email)) $errors['email'] = 'Invalid email format';
    if (empty($password)) {
          $errors['password'] = 'Password is required';
    } else if (empty($confirm_password)) {
          $errors['confirm_password'] = 'Confirm password is required';
    }else if ($password !== $confirm_password) {
        $errors['confirm_password'] = 'Passwords do not match';
    }
    

    if (!empty($errors)) {
        $error = implode("<br>",$errors);
        $formData = $_SESSION['form_data']; // retrieve the data to prepopulate

         if (isset($errors['username'])) {
                $formData['username'] = '';
            }
            if (isset($errors['email'])) {
                $formData['email'] = '';
            }
            if (isset($errors['password'])) {
                $formData['password'] = '';
            }
           if (isset($errors['confirm_password'])) {
                $formData['confirm_password'] = '';
            }

         unset($_SESSION['form_data']);
    }else{
            $db = getDBConnection();
            
            // Check if username or email already exists
            $stmt = $db->prepare("SELECT id FROM admins WHERE username = ? OR email = ?");
            $stmt->execute([$username, $email]);
            
            if ($stmt->rowCount() > 0) {
                $error = 'Username or email already exists';
                 unset($_SESSION['form_data']);
            } else {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                
                $stmt = $db->prepare("INSERT INTO admins (username, email, password) VALUES (?, ?, ?)");
                if ($stmt->execute([$username, $email, $hashed_password])) {
                    unset($_SESSION['form_data']); // clear session
                    $success = 'Registration successful. You can now login.';
                } else {
                     unset($_SESSION['form_data']); // clear session
                    $error = 'Registration failed. Please try again.';
                }
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
                    <h3>Admin Registration</h3>
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
                            <label>Username</label>
                            <input type="text" name="username" class="form-control"  value="<?= isset($formData['username']) ? htmlspecialchars($formData['username']) : '' ?>" required>
                             <div class="invalid-feedback"><?= $errors['username'] ?? '' ?></div>
                        </div>
                        <div class="form-group">
                            <label>Email</label>
                            <input type="email" name="email" class="form-control"  value="<?= isset($formData['email']) ? htmlspecialchars($formData['email']) : '' ?>" required>
                              <div class="invalid-feedback"><?= $errors['email'] ?? '' ?></div>
                        </div>
                        <div class="form-group">
                            <label>Password</label>
                            <input type="password" name="password" class="form-control"   value="<?= isset($formData['password']) ? htmlspecialchars($formData['password']) : '' ?>" required>
                              <div class="invalid-feedback"><?= $errors['password'] ?? '' ?></div>
                        </div>
                        <div class="form-group">
                            <label>Confirm Password</label>
                            <input type="password" name="confirm_password" class="form-control"   value="<?= isset($formData['confirm_password']) ? htmlspecialchars($formData['confirm_password']) : '' ?>" required>
                               <div class="invalid-feedback"><?= $errors['confirm_password'] ?? '' ?></div>
                        </div>
                        <button type="submit" class="btn btn-primary mt-2">Register</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../../templates/footer.php'; ?>