<?php
session_start();
require_once '../../config/database.php';

if (!isset($_SESSION['admin_id']) || !isset($_GET['id'])) {
    header('Location: ../dashboard.php');
    exit();
}

$db = getDBConnection();

// First delete all attendees for this event
$stmt = $db->prepare("DELETE FROM attendees WHERE event_id = ?");
$stmt->execute([$_GET['id']]);

// Then delete the event
$stmt = $db->prepare("DELETE FROM events WHERE id = ?");
$stmt->execute([$_GET['id']]);

header('Location: ../dashboard.php');
exit();