<?php
session_start();
require_once 'db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

// Search functionality
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$params = [$_SESSION['user_id']];

if (!empty($search)) {
    // Modified SQL query to search across multiple fields
    $stmt = $pdo->prepare("
        SELECT * FROM contacts 
        WHERE user_id = ? 
        AND (
            name LIKE ? 
            OR phone LIKE ? 
            OR email LIKE ? 
            OR address LIKE ?
        )
        ORDER BY name
    ");
    
    $searchTerm = "%$search%";
    $params = [$_SESSION['user_id'], $searchTerm, $searchTerm, $searchTerm, $searchTerm];
} else {
    // If no search, get all contacts
    $stmt = $pdo->prepare("SELECT * FROM contacts WHERE user_id = ? ORDER BY name");
}

$stmt->execute($params);
$contacts = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Phone Book</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="header-left">
                <h2>Phone Book</h2>
                <p class="welcome-text">Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?></p>
            </div>
            <div class="header-right">
                <a href="add_contact.php" class="btn-add">Add New Contact</a>
                <a href="logout.php" class="btn-logout">Logout</a>
            </div>
        </div>

        <div class="search-box">
            <form method="GET" action="">
                <div class="search-container">
                    <input type="text" 
                           name="search" 
                           placeholder="Search by name, phone, email or address..." 
                           value="<?php echo htmlspecialchars($search); ?>"
                    >
                    <button type="submit" class="btn-search">Search</button>
                    <?php if (!empty($search)): ?>
                        <a href="dashboard.php" class="btn-clear">Clear</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <?php if (empty($contacts) && !empty($search)): ?>
            <div class="no-results">
                <p>No contacts found matching "<?php echo htmlspecialchars($search); ?>"</p>
            </div>
        <?php elseif (empty($contacts)): ?>
            <div class="no-contacts">
                <p>No contacts yet. Add your first contact!</p>
                <a href="add_contact.php" class="btn-add-large">Add New Contact</a>
            </div>
        <?php else: ?>
            <div class="contacts-list">
                <?php foreach ($contacts as $contact): ?>
                    <div class="contact-card">
                        <div class="contact-info">
                            <h3><?php echo htmlspecialchars($contact['name']); ?></h3>
                            <div class="contact-details">
                                <p>
                                    <span class="label">Phone:</span>
                                    <span class="value"><?php echo htmlspecialchars($contact['phone']); ?></span>
                                </p>
                                <?php if (!empty($contact['email'])): ?>
                                    <p>
                                        <span class="label">Email:</span>
                                        <span class="value"><?php echo htmlspecialchars($contact['email']); ?></span>
                                    </p>
                                <?php endif; ?>
                                <?php if (!empty($contact['address'])): ?>
                                    <p>
                                        <span class="label">Address:</span>
                                        <span class="value"><?php echo htmlspecialchars($contact['address']); ?></span>
                                    </p>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="actions">
                            <a href="edit_contact.php?id=<?php echo $contact['id']; ?>" class="btn-edit">Edit</a>
                            <a href="delete_contact.php?id=<?php echo $contact['id']; ?>" 
                               class="btn-delete" 
                               onclick="return confirm('Are you sure you want to delete this contact?')">Delete</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <script>
        // Optional: Add fade-in animation for contact cards
        document.addEventListener('DOMContentLoaded', function() {
            const cards = document.querySelectorAll('.contact-card');
            cards.forEach((card, index) => {
                card.style.animation = `fadeIn 0.3s ease-out ${index * 0.1}s forwards`;
            });
        });
    </script>
</body>
</html>