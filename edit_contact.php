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

// Fetch contact details
$stmt = $pdo->prepare("SELECT * FROM contacts WHERE id = ? AND user_id = ?");
$stmt->execute([$contact_id, $_SESSION['user_id']]);
$contact = $stmt->fetch();

if (!$contact) {
    header("Location: dashboard.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name']);
    $phone = trim($_POST['phone']);
    $email = trim($_POST['email']);
    $address = trim($_POST['address']);
    
    if (empty($name) || empty($phone)) {
        $error = "Name and phone number are required";
    } else {
        try {
            $stmt = $pdo->prepare("UPDATE contacts SET name = ?, phone = ?, email = ?, address = ? WHERE id = ? AND user_id = ?");
            $stmt->execute([$name, $phone, $email, $address, $contact_id, $_SESSION['user_id']]);
            header("Location: dashboard.php");
            exit;
        } catch(PDOException $e) {
            $error = "Error updating contact";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Contact - Phone Book</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>Edit Contact</h2>
            <a href="dashboard.php" class="btn-back">Back to Dashboard</a>
        </div>

        <?php if (isset($error)) echo "<div class='error'>$error</div>"; ?>

        <form method="POST" class="contact-form">
            <div class="form-group">
                <label for="name">Name *</label>
                <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($contact['name']); ?>" required>
            </div>

            <div class="form-group">
                <label for="phone">Phone Number *</label>
                <input type="text" id="phone" name="phone" value="<?php echo htmlspecialchars($contact['phone']); ?>" required>
            </div>

            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($contact['email']); ?>">
            </div>

            <div class="form-group">
                <label for="address">Address</label>
                <input type="text" id="address" name="address" value="<?php echo htmlspecialchars($contact['address']); ?>">
            </div>

            <div class="form-actions">
                <button type="submit" class="btn-submit">Update Contact</button>
                <a href="dashboard.php" class="btn-cancel">Cancel</a>
            </div>
        </form>
    </div>
</body>
</html>