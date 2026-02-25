<?php
session_start();
require 'config/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Merr pronat e përdoruesit
$sql = "SELECT p.*, 
        (SELECT image_name FROM property_images WHERE property_id = p.id LIMIT 1) as img 
        FROM properties p 
        WHERE p.user_id = ? 
        ORDER BY p.created_at DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

// Fshirja e pronës
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $delete_id = intval($_GET['delete']);
    
    // Sigurohu që prona i takon përdoruesit
    $check = $conn->prepare("SELECT id FROM properties WHERE id = ? AND user_id = ?");
    $check->bind_param("ii", $delete_id, $user_id);
    $check->execute();
    
    if ($check->get_result()->num_rows > 0) {
        // Fshi fotot nga dosja
        $img_sql = "SELECT image_name FROM property_images WHERE property_id = ?";
        $img_stmt = $conn->prepare($img_sql);
        $img_stmt->bind_param("i", $delete_id);
        $img_stmt->execute();
        $img_result = $img_stmt->get_result();
        
        while($img = $img_result->fetch_assoc()) {
            $file_path = "uploads/" . $img['image_name'];
            if (file_exists($file_path)) {
                unlink($file_path);
            }
        }
        
        // Fshi nga databaza (foreign keys do të fshijnë automatikisht)
        $del_stmt = $conn->prepare("DELETE FROM properties WHERE id = ?");
        $del_stmt->bind_param("i", $delete_id);
        $del_stmt->execute();
        
        header("Location: my_properties.php?deleted=1");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="sq">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pronat e Mia - Agjencia Imobiliare</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <header>
        <h1>🏠 Agjencia Imobiliare</h1>
        <nav>
            <a href="index.php">Kryefaqja</a>
            <a href="add_property.php">+ Shto Pronë</a>
            <a href="logout.php">Dil</a>
        </nav>
    </header>

    <div class="container">
        <h2 class="page-title">Pronat e Mia</h2>
        
        <?php if(isset($_GET['deleted'])): ?>
            <div class="alert alert-success">Prona u fshi me sukses!</div>
        <?php endif; ?>
        
        <?php if($result->num_rows > 0): ?>
            <div class="property-grid">
                <?php while($row = $result->fetch_assoc()): ?>
                    <div class="card">
                        <img src="uploads/<?php echo $row['img'] ? htmlspecialchars($row['img']) : 'no-image.png'; ?>" 
                             alt="<?php echo htmlspecialchars($row['title']); ?>">
                        <div class="card-body">
                            <h3 class="card-title"><?php echo htmlspecialchars($row['title']); ?></h3>
                            <p class="card-info">📍 <?php echo htmlspecialchars($row['city']); ?></p>
                            <p class="card-price">€<?php echo number_format($row['price'], 2); ?></p>
                            <p class="card-info">Status: <?php echo $row['status'] == 'available' ? '✅ Në dispozicion' : '❌ E shitur'; ?></p>
                            <a href="property.php?id=<?php echo $row['id']; ?>" class="btn">Shiko</a>
                            <a href="my_properties.php?delete=<?php echo $row['id']; ?>" 
                               class="btn" 
                               style="background: #e74c3c;"
                               onclick="return confirmDelete('A jeni i sigurt që dëshironi të fshini këtë pronë?');">Fshi</a>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <p style="text-align: center; color: #666;">Nuk keni shtuar asnjë pronë ende.</p>
            <p style="text-align: center;"><a href="add_property.php" class="btn btn-success">Shto Pronën e Parë</a></p>
        <?php endif; ?>
    </div>

    <footer class="footer">
        <p>&copy; 2024 Agjencia Imobiliare. Të gjitha të drejtat e rezervuara.</p>
    </footer>
</body>
</html>
