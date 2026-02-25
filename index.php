<?php
session_start();
require "config/db.php";

$sql = "SELECT p.*, (SELECT image_name FROM property_images WHERE property_id = p.id LIMIT 1) as img FROM properties p WHERE p.status = "available" ORDER BY p.created_at DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="sq">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agjencia Imobiliare - Kryefaqja</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <header>
        <h1>🏠 Agjencia Imobiliare</h1>
        <nav>
            <a href="index.php">Kryefaqja</a>
            <?php if(isset($_SESSION["user_id"])): ?>
                <a href="add_property.php">+ Shto Pronë</a>
                <a href="my_properties.php">Pronat e Mia</a>
                <a href="favorites.php">Të Preferuarat</a>
                <a href="logout.php">Dil (<?php echo htmlspecialchars($_SESSION["name"]); ?>)</a>
            <?php else: ?>
                <a href="login.php">Kycu</a>
                <a href="login.php?type=register">Regjistrohu</a>
            <?php endif; ?>
        </nav>
    </header>

    <div class="container">
        <h2 class="page-title">Pronat më të Reja</h2>
        
        <?php if($result->num_rows > 0): ?>
            <div class="property-grid">
                <?php while($row = $result->fetch_assoc()): ?>
                    <div class="card">
                        <img src="uploads/<?php echo $row["img"] ? htmlspecialchars($row["img"]) : "no-image.png"; ?>" alt="<?php echo htmlspecialchars($row["title"]); ?>">
                        <div class="card-body">
                            <h3 class="card-title"><?php echo htmlspecialchars($row["title"]); ?></h3>
                            <p class="card-info">📍 <?php echo htmlspecialchars($row["city"]); ?> - <?php echo htmlspecialchars($row["address"]); ?></p>
                            <p class="card-info">🏠 <?php echo ucfirst($row["property_type"]); ?></p>
                            <p class="card-price">€<?php echo number_format($row["price"], 2); ?></p>
                            <a href="property.php?id=<?php echo $row["id"]; ?>" class="btn">Shiko Detajet</a>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <p style="text-align: center; color: #666;">Nuk ka prona të disponueshme.</p>
        <?php endif; ?>
    </div>

    <footer class="footer">
        <p>&copy; 2024 Agjencia Imobiliare</p>
    </footer>
</body>
</html>
