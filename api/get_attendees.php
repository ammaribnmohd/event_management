<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('HTTP/1.1 401 Unauthorized');
    echo json_encode(['success' => false, 'message' => 'Authentication required']);
    exit();
}

if (!isset($_GET['event_id']) || !is_numeric($_GET['event_id'])) {
    header('HTTP/1.1 400 Bad Request');
    echo json_encode(['success' => false, 'message' => 'Invalid event ID']);
    exit();
}

$eventId = (int)$_GET['event_id'];

try {
    $db = getDBConnection();
    $stmt = $db->prepare("
        SELECT name, email, registration_date 
        FROM attendees 
        WHERE event_id = ?
        ORDER BY registration_date DESC
    ");
    $stmt->execute([$eventId]);
    $attendees = $stmt->fetchAll(PDO::FETCH_ASSOC);

    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'attendees' => $attendees
    ]);
} catch (PDOException $e) {
    header('HTTP/1.1 500 Internal Server Error');
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}