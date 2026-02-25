<?php
session_start();
require 'config/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Merr të preferuarat
$sql = "SELECT p.*, f.id as fav_id,
        (SELECT image_name FROM property_images WHERE property_id = p.id LIMIT 1) as img 
        FROM properties p 
        JOIN favorites f ON p.id = f.property_id 
        WHERE f.user_id = ? 
        ORDER BY f.id DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

// Hiq nga favorite
if (isset($_GET['remove']) && is_numeric($_GET['remove'])) {
    $remove_id = intval($_GET['remove']);
    $del_stmt = $conn->prepare("DELETE FROM favorites WHERE id = ? AND user_id = ?");
    $del_stmt->bind_param("ii", $remove_id, $user_id);
    $del_stmt->execute();
    header("Location: favorites.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="sq">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Të Preferuarat - Agjencia Imobiliare</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <header>
        <h1>🏠 Agjencia Imobiliare</h1>
        <nav>
            <a href="index.php">Kryefaqja</a>
            <a href="add_property.php">Shto Pronë</a>
            <a href="my_properties.php">Pronat e Mia</a>
            <a href="logout.php">Dil</a>
        </nav>
    </header>

    <div class="container">
        <h2 class="page-title">Të Preferuarat e Mia</h2>
        
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
                            <a href="property.php?id=<?php echo $row['id']; ?>" class="btn">Shiko Detajet</a>
                            <a href="favorites.php?remove=<?php echo $row['fav_id']; ?>" 
                               class="btn" 
                               style="background: #e74c3c;"
                               onclick="return confirmDelete('A doni ta hiqni nga të preferuarat?');">Hiq</a>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <p style="text-align: center; color: #666;">Nuk keni asnjë pronë në të preferuarat.</p>
            <p style="text-align: center;"><a href="index.php" class="btn btn-success">Shfleto Pronat</a></p>
        <?php endif; ?>
    </div>

    <footer class="footer">
        <p>&copy; 2024 Agjencia Imobiliare. Të gjitha të drejtat e rezervuara.</p>
    </footer>
</body>
</html>
