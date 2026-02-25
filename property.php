<?php
session_start();
require 'config/db.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id <= 0) {
    header("Location: index.php");
    exit;
}

// Merr property
$stmt = $conn->prepare("SELECT p.*, u.name as owner_name, u.email as owner_email 
                        FROM properties p 
                        JOIN users u ON p.user_id = u.id 
                        WHERE p.id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$prop = $stmt->get_result()->fetch_assoc();

if (!$prop) {
    header("Location: index.php");
    exit;
}

// Merr fotot
$img_stmt = $conn->prepare("SELECT * FROM property_images WHERE property_id = ?");
$img_stmt->bind_param("i", $id);
$img_stmt->execute();
$images = $img_stmt->get_result();

// Mesazhi
$msg_result = "";
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['send_message'])) {
    $sender_name = trim($_POST['sender_name']);
    $sender_email = trim($_POST['sender_email']);
    $message = trim($_POST['message']);
    
    if ($sender_name && $sender_email && $message) {
        $msg_stmt = $conn->prepare("INSERT INTO messages (property_id, sender_name, sender_email, message) VALUES (?, ?, ?, ?)");
        $msg_stmt->bind_param("isss", $id, $sender_name, $sender_email, $message);
        
        if ($msg_stmt->execute()) {
            $msg_result = "<div class='alert alert-success'>Mesazhi u dërgua me sukses!</div>";
        } else {
            $msg_result = "<div class='alert alert-error'>Gabim gjatë dërgimit të mesazhit!</div>";
        }
    } else {
        $msg_result = "<div class='alert alert-error'>Ju lutemi plotësoni të gjitha fushat!</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="sq">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($prop['title']); ?> - Agjencia Imobiliare</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <header>
        <h1>🏠 Agjencia Imobiliare</h1>
        <nav>
            <a href="index.php">Kryefaqja</a>
            <?php if(isset($_SESSION['user_id'])): ?>
                <a href="add_property.php">Shto Pronë</a>
                <a href="logout.php">Dil</a>
            <?php else: ?>
                <a href="login.php">Kyçu</a>
            <?php endif; ?>
        </nav>
    </header>

    <div class="container">
        <div class="property-detail">
            <a href="index.php" class="btn" style="margin-bottom: 20px;">← Kthehu</a>
            
            <h1><?php echo htmlspecialchars($prop['title']); ?></h1>
            
            <!-- Foto kryesore -->
            <?php if($images->num_rows > 0): ?>
                <?php $main_img = $images->fetch_assoc(); ?>
                <img src="uploads/<?php echo htmlspecialchars($main_img['image_name']); ?>" alt="<?php echo htmlspecialchars($prop['title']); ?>">
            <?php else: ?>
                <img src="uploads/no-image.png" alt="No Image">
            <?php endif; ?>
            
            <div class="property-meta">
                <span>📍 <?php echo htmlspecialchars($prop['city']); ?></span>
                <span>🏠 <?php echo ucfirst($prop['property_type']); ?></span>
                <span>💰 €<?php echo number_format($prop['price'], 2); ?></span>
                <span>📅 <?php echo date('d/m/Y', strtotime($prop['created_at'])); ?></span>
            </div>
            
            <h3>Përshkrimi</h3>
            <p style="line-height: 1.8; margin: 15px 0;"><?php echo nl2br(htmlspecialchars($prop['description'])); ?></p>
            
            <p><strong>Adresa:</strong> <?php echo htmlspecialchars($prop['address']); ?></p>
            <p><strong>Postuesi:</strong> <?php echo htmlspecialchars($prop['owner_name']); ?></p>
            
            <!-- Dërgimi i mesazhit -->
            <div class="messages-section">
                <h3>Dërgo Mesazh</h3>
                <?php echo $msg_result; ?>
                
                <form method="POST" id="messageForm">
                    <input type="hidden" name="send_message" value="1">
                    <div class="form-group">
                        <label for="sender_name">Emri:</label>
                        <input type="text" id="sender_name" name="sender_name" required>
                    </div>
                    <div class="form-group">
                        <label for="sender_email">Email:</label>
                        <input type="email" id="sender_email" name="sender_email" required>
                    </div>
                    <div class="form-group">
                        <label for="message">Mesazhi:</label>
                        <textarea id="message" name="message" required placeholder="Shkruani mesazhin tuaj..."></textarea>
                    </div>
                    <button type="submit" class="btn">Dërgo Mesazh</button>
                </form>
            </div>
        </div>
    </div>

    <footer class="footer">
        <p>&copy; 2024 Agjencia Imobiliare. Të gjitha të drejtat e rezervuara.</p>
    </footer>
</body>
</html>
