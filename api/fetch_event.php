<?php
// Turn off error reporting for the API endpoint
error_reporting(0);
ini_set('display_errors', 0);

require_once '../config/database.php';
require_once '../includes/functions.php';

// Set JSON content type header
header('Content-Type: application/json');

// Handle CORS if needed
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

// Initialize response array
$response = [
    'success' => false,
    'data' => null,
    'error' => null
];

try {
    // Check if ID is provided
    if (!isset($_GET['id'])) {
        throw new Exception('Event ID is required');
    }

    $db = getDBConnection();

    // Fetch event details
    $stmt = $db->prepare("
        SELECT 
            e.*,
            COUNT(a.id) as current_attendees
        FROM events e
        LEFT JOIN attendees a ON e.id = a.event_id
        WHERE e.id = ?
        GROUP BY e.id
    ");
    
    $stmt->execute([$_GET['id']]);
    $event = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$event) {
        throw new Exception('Event not found');
    }

    // Add registration status using max_capacity instead of capacity
    $event['is_full'] = isEventFull($_GET['id']);
    $event['spots_remaining'] = $event['max_capacity'] - $event['current_attendees'];

    // Remove sensitive fields if any
    unset($event['admin_notes']);

    $response['success'] = true;
    $response['data'] = $event;

} catch (Exception $e) {
    http_response_code(400);
    $response['error'] = $e->getMessage();
} catch (PDOException $e) {
    http_response_code(500);
    $response['error'] = 'Database error occurred';
}

// Output JSON response
echo json_encode($response);