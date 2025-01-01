<?php
session_start();
require_once 'db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

// Get contact ID
$contact_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Delete contact
try {
    $stmt = $pdo->prepare("DELETE FROM contacts WHERE id = ? AND user_id = ?");
    $stmt->execute([$contact_id, $_SESSION['user_id']]);
} catch(PDOException $e) {
    // Handle error if needed
}

header("Location: dashboard.php");
exit;