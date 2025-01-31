<?php
require_once 'config/database.php';
require_once 'includes/functions.php';
require_once 'templates/header.php';

$orderBy = $_GET['sort'] ?? 'event_date';
$orderDir = $_GET['order'] ?? 'DESC';
$searchTerm = $_GET['search'] ?? '';

$allowedColumns = ['title', 'event_date'];
$allowedDirections = ['ASC', 'DESC'];
if (!in_array($orderBy, $allowedColumns) || !in_array($orderDir, $allowedDirections)) {
    $orderBy = 'event_date';
    $orderDir = 'DESC';
}

$perPage = 6;
$page = $_GET['page'] ?? 1;
$page = max(1, (int)$page);
$offset = ($page - 1) * $perPage;

$db = getDBConnection();
$searchQuery = !empty($searchTerm) ? " WHERE e.title LIKE :searchTerm " : "";
$stmtCount = $db->prepare("SELECT COUNT(*) FROM events e $searchQuery");
if (!empty($searchTerm)) {
    $stmtCount->bindValue(':searchTerm', '%' . $searchTerm . '%', PDO::PARAM_STR);
}
$stmtCount->execute();
$totalEvents = $stmtCount->fetchColumn();

$totalPages = ceil($totalEvents / $perPage);
$stmt = $db->prepare("
    SELECT 
        e.*, 
        COUNT(a.id) as attendee_count 
    FROM events e 
    LEFT JOIN attendees a ON e.id = a.event_id
     $searchQuery
    GROUP BY e.id 
    ORDER BY $orderBy $orderDir
    LIMIT :limit OFFSET :offset
");
$stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);

if (!empty($searchTerm)) {
    $stmt->bindValue(':searchTerm', '%' . $searchTerm . '%', PDO::PARAM_STR);
}
$stmt->execute();
?>

<div class="container mt-5">
    <div class="row mb-4">
        <div class="col-md-8">
            <h1>Upcoming Events</h1>
        </div>
        <div class="col-md-4 text-right">
            <div class="d-flex justify-content-end gap-2">
                <a href="admin/auth/login.php" class="btn btn-primary">Admin Login</a>
                <a href="admin/auth/register.php" class="btn btn-secondary">Admin Register</a>
            </div>
        </div>
    </div>

   <div class="row align-items-center mb-3">
        <!-- Search Bar -->
          <div class="col-12 col-md-4 mb-2 mb-md-0">
                <form method="GET" action="" class="d-flex gap-2">
                    <input type="text" name="search" id="searchEvents" class="form-control" placeholder="Search events by name..." value="<?= htmlspecialchars($searchTerm) ?>">
                     <input type="hidden" name="sort" value="<?= htmlspecialchars($orderBy) ?>">
                    <input type="hidden" name="order" value="<?= htmlspecialchars($orderDir) ?>">
                    <button type="submit" class="btn btn-primary">Search</button>
                </form>
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
                        <li><a class="dropdown-item" href="?sort=title&order=<?= $orderDir ?><?=!empty($searchTerm) ? '&search=' . urlencode($searchTerm) : '' ?>">Title</a></li>
                        <li><a class="dropdown-item" href="?sort=event_date&order=<?= $orderDir ?><?=!empty($searchTerm) ? '&search=' . urlencode($searchTerm) : '' ?>">Date</a></li>
                    </ul>
                </div>
            </div>

            <!-- Toggle Order Button -->
            <div class="col-6 col-md-auto">
                <a href="?sort=<?= $orderBy ?>&order=<?= $orderDir === 'ASC' ? 'DESC' : 'ASC' ?><?=!empty($searchTerm) ? '&search=' . urlencode($searchTerm) : '' ?>"
                   class="btn btn-primary w-100 text-center">
                    <?= $orderDir === 'ASC' ? 'Descending' : 'Ascending' ?>
                </a>
            </div>
        </div>
    </div>
    </div>
    <div class="row">
        <?php while ($event = $stmt->fetch(PDO::FETCH_ASSOC)): ?>
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
    
    <!-- Pagination Links -->
    <nav aria-label="Page navigation" class="mt-4">
        <ul class="pagination justify-content-center">
           <?php if ($page > 1): ?>
                <li class="page-item">
                    <a class="page-link" href="?page=<?= $page - 1 ?>&sort=<?= $orderBy ?>&order=<?= $orderDir ?><?=!empty($searchTerm) ? '&search=' . urlencode($searchTerm) : ''?>">Previous</a>
                </li>
            <?php endif; ?>

             <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <li class="page-item <?= ($i == $page) ? 'active' : '' ?>">
                    <a class="page-link" href="?page=<?= $i ?>&sort=<?= $orderBy ?>&order=<?= $orderDir ?><?=!empty($searchTerm) ? '&search=' . urlencode($searchTerm) : ''?>"><?= $i ?></a>
                </li>
            <?php endfor; ?>

            <?php if ($page < $totalPages): ?>
                <li class="page-item">
                    <a class="page-link" href="?page=<?= $page + 1 ?>&sort=<?= $orderBy ?>&order=<?= $orderDir ?><?=!empty($searchTerm) ? '&search=' . urlencode($searchTerm) : ''?>">Next</a>
                </li>
            <?php endif; ?>
        </ul>
    </nav>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    
    var dropdownButton = document.getElementById('dropdownMenuButton');
    if(dropdownButton) {
        dropdownButton.addEventListener('click', function() {
            var dropdownMenu = this.nextElementSibling;
            dropdownMenu.classList.toggle('show');
        });
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
</script>
<?php require_once 'templates/footer.php'; ?>