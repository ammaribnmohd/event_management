<?php
session_start();
require_once '../../config/database.php';

if (!isset($_SESSION['admin_id']) || !isset($_GET['id'])) {
    header('Location: ../dashboard.php');
    exit();
}

$db = getDBConnection();

// Get event details
$stmt = $db->prepare("SELECT title FROM events WHERE id = ?");
$stmt->execute([$_GET['id']]);
$event = $stmt->fetch(PDO::FETCH_ASSOC);

// Get attendees
$stmt = $db->prepare("
    SELECT name, email, phone, registration_date 
    FROM attendees 
    WHERE event_id = ? 
    ORDER BY registration_date
");
$stmt->execute([$_GET['id']]);
$attendees = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Generate CSV
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="' . $event['title'] . '_attendees.csv"');

$output = fopen('php://output', 'w');
fputcsv($output, ['Name', 'Email', 'Phone', 'Registration Date']);

foreach ($attendees as $attendee) {
    fputcsv($output, $attendee);
}

fclose($output);
exit();