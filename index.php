<?php
require_once 'config/database.php';
require_once 'includes/functions.php';
require_once 'templates/header.php';

// Get and validate sorting parameters
$orderBy = $_GET['sort'] ?? 'event_date';
$orderDir = $_GET['order'] ?? 'DESC';

// Validate sorting parameters
$allowedColumns = ['title', 'event_date'];
$allowedDirections = ['ASC', 'DESC'];
if (!in_array($orderBy, $allowedColumns) || !in_array($orderDir, $allowedDirections)) {
    $orderBy = 'event_date';
    $orderDir = 'DESC';
}

?>

<div class="container mt-5">
    <div class="row mb-4">
        <div class="col-md-8">
            <h1>Upcoming Events</h1>
        </div>
        <div class="col-md-4 text-right">
            <a href="admin/auth/login.php" class="btn btn-primary">Admin Login</a>
            <a href="admin/auth/register.php" class="btn btn-secondary">Admin Register</a>
        </div>
    </div>

    <div class="d-flex gap-2 mb-3 align-items-center">
    <div class="col-md-4">
        <input type="text" id="searchEvents" class="form-control" placeholder="Search events by name...">
    </div>
    <div class="dropdown">
        <button class="btn btn-primary" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
            Sort By ▼
        </button>
        <ul class="dropdown-menu">
            <li><a class="dropdown-item" href="?sort=title&order=<?= $orderDir ?>">Title</a></li>
            <li><a class="dropdown-item" href="?sort=event_date&order=<?= $orderDir ?>">Date</a></li>
        </ul>
    </div>
    <a href="?sort=<?= $orderBy ?>&order=<?= $orderDir === 'ASC' ? 'DESC' : 'ASC' ?>" class="btn btn-primary">
        <?= $orderDir === 'ASC' ? 'Descending' : 'Ascending' ?>
    </a>
</div>

    <div class="row">
        <?php
        $db = getDBConnection();
        $stmt = $db->prepare("
            SELECT 
                e.*, 
                COUNT(a.id) as attendee_count 
            FROM events e 
            LEFT JOIN attendees a ON e.id = a.event_id 
            GROUP BY e.id 
            ORDER BY $orderBy $orderDir
        ");
        $stmt->execute();
        
        while ($event = $stmt->fetch(PDO::FETCH_ASSOC)): 
        ?>
            <div class="col-md-4 mb-4 event-card">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title event-title"><?= htmlspecialchars($event['title']) ?></h5>
                        <p class="card-text"><?= htmlspecialchars($event['description']) ?></p>
                        <p>
                            <strong>Date:</strong> <?= $event['event_date'] ?><br>
                            <strong>Time:</strong> <?= $event['event_time'] ?><br>
                            <strong>Location:</strong> <?= htmlspecialchars($event['location']) ?>
                        </p>
                        <?php if (!isEventFull($event['id'])): ?>
                            <a href="events/register.php?id=<?= $event['id'] ?>" 
                               class="btn btn-primary">Register</a>
                        <?php else: ?>
                            <button class="btn btn-secondary" disabled>Event Member Limit is filled</button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize dropdowns
    var dropdownButton = document.getElementById('dropdownMenuButton');
    if(dropdownButton) {
        dropdownButton.addEventListener('click', function() {
            var dropdownMenu = this.nextElementSibling;
            dropdownMenu.classList.toggle('show');
        });

        // Close dropdown when clicking outside
        document.addEventListener('click', function(e) {
            if (!dropdownButton.contains(e.target)) {
                var dropdownMenu = dropdownButton.nextElementSibling;
                if (dropdownMenu.classList.contains('show')) {
                    dropdownMenu.classList.remove('show');
                }
            }
        });
    }
});
document.getElementById('searchEvents').addEventListener('input', function (e) {
    const searchTerm = e.target.value.toLowerCase();
    
    document.querySelectorAll('.event-card').forEach(card => {
        const title = card.querySelector('.event-title').textContent.toLowerCase();
        if (title.includes(searchTerm)) {
            card.style.display = 'block'; // Show matching cards
        } else {
            card.style.display = 'none'; // Hide non-matching cards
        }
    });
});
</script>

<?php require_once 'templates/footer.php'; ?>