<?php
// kitchen-actions.php - kitchen buttons: "Start" (waiting → cooking) and "Ready" (→ ready, time_ended)
require_once __DIR__ . '/../checkIfLoggedIn.php';   // staff only: if not logged in, go to the staff login page
require_once '../../config.php';

if (isset($_GET['action']) && isset($_GET['kitchen_id'])) {
    $action = $_GET['action'];
    $kitchen_id = intval($_GET['kitchen_id']);

    if ($action === 'start') {
        // the cook starts this dish
        $stmt = $link->prepare("UPDATE Kitchen SET status = 'cooking' WHERE kitchen_id = ? AND status = 'waiting'");
        $stmt->bind_param('i', $kitchen_id);
        $stmt->execute();
    }

    if ($action === 'set_time_ended') {
        // the dish is ready: save the time it was finished
        $currentTime = date('Y-m-d H:i:s');
        $stmt = $link->prepare("UPDATE Kitchen SET time_ended = ?, status = 'ready' WHERE kitchen_id = ?");
        $stmt->bind_param('si', $currentTime, $kitchen_id);
        $stmt->execute();
    }
}

// the kitchen screen calls this with ajax=1 and reloads the tickets itself
if (isset($_GET['ajax'])) {
    echo 'ok';
    exit;
}
header("Location: ../../panel/kitchen-panel.php");
exit;
