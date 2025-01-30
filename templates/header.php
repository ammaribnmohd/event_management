<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event Management System</title>
   
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
   
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <link href="/assets/css/styles.css" rel="stylesheet">
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand" href="<?php echo isset($_SESSION['admin_id']) ? '/event_management/admin/dashboard.php' : '/event_management/index.php'; ?>">
            Event Management
        </a>
        <?php if (isset($_SESSION['admin_id'])): ?>
            <div class="navbar-nav">
                <a class="nav-link" href="/event_management/admin/dashboard.php">Dashboard</a>
            </div>
        <?php endif; ?>
    </div>
</nav>
</head>

<body>
</body>