<?php
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

function isEventFull($eventId) {
    $db = getDBConnection();
    
    // Get event capacity
    $stmt = $db->prepare("SELECT max_capacity FROM events WHERE id = ?");
    $stmt->execute([$eventId]);
    $event = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Get current registration count
    $stmt = $db->prepare("SELECT COUNT(*) as count FROM attendees WHERE event_id = ?");
    $stmt->execute([$eventId]);
    $registrations = $stmt->fetch(PDO::FETCH_ASSOC);
    
    return $registrations['count'] >= $event['max_capacity'];
}