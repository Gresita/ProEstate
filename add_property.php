<?php
session_start();
require 'config/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $price = floatval($_POST['price']);
    $city = trim($_POST['city']);
    $address = trim($_POST['address']);
    $property_type = $_POST['property_type'];
    $user_id = $_SESSION['user_id'];

    // Validimi
    if (empty($title) || empty($description) || empty($city) || empty($address)) {
        $message = "Ju lutemi plotësoni të gjitha fushat!";
    } elseif ($price <= 0) {
        $message = "Çmimi duhet të jetë pozitiv!";
    } else {
        // Uploadimi i fotos
        if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
            $target_dir = "uploads/";
            $file_extension = strtolower(pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION));
            $new_filename = uniqid() . '.' . $file_extension;
            $target_file = $target_dir . $new_filename;
            
            // Lejo vetëm disa formate
            $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
            if (in_array($file_extension, $allowed_types)) {
                if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
                    
                    // Shtimi në databazë
                    $stmt = $conn->prepare("INSERT INTO properties (user_id, title, description, price, city, address, property_type) VALUES (?, ?, ?, ?, ?, ?, ?)");
                    $stmt->bind_param("issssss", $user_id, $title, $description, $price, $city, $address, $property_type);
                    
                    if ($stmt->execute()) {
                        $property_id = $stmt->insert_id;
                        
                        // Shtimi i fotos
                        $img_stmt = $conn->prepare("INSERT INTO property_images (property_id, image_name) VALUES (?, ?)");
                        $img_stmt->bind_param("is", $property_id, $new_filename);
                        $img_stmt->execute();
                        
                        echo "<script>
                            alert('Prona u shtua me sukses!');
                            window.location.href = 'index.php';
                        </script>";
                        exit;
                    } else {
                        $message = "Gabim gjatë ruajtjes së pronës!";
                    }
                } else {
                    $message = "Gabim gjatë ngarkimit të fotos!";
                }
            } else {
                $message = "Formati i fotos nuk është i lejuar! (Lejohen: JPG, JPEG, PNG, GIF)";
            }
        } else {
            $message = "Ju lutemi ngarkoni një foto!";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="sq">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shto Pronë - Agjencia Imobiliare</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <header>
        <h1>🏠 Agjencia Imobiliare</h1>
        <nav>
            <a href="index.php">Kryefaqja</a>
            <a href="logout.php">Dil</a>
        </nav>
    </header>

    <div class="container">
        <div class="form-container">
            <h2 class="page-title">Shto Pronë të Re</h2>
            
            <?php if($message): ?>
                <div class="alert alert-error"><?php echo $message; ?></div>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data" onsubmit="return validateForm('propertyForm')">
                <div class="form-group">
                    <label for="title">Titulli i Pronës:</label>
                    <input type="text" id="title" name="title" required placeholder="P.sh. Shtëpi 3+1 në Tiranë">
                </div>
                
                <div class="form-group">
                    <label for="description">Përshkrimi:</label>
                    <textarea id="description" name="description" required placeholder="Përshkruani pronën në detaje..."></textarea>
                </div>
                
                <div class="form-group">
                    <label for="price">Çmimi (€):</label>
                    <input type="number" id="price" name="price" required min="1" step="0.01" placeholder="P.sh. 150000">
                </div>
                
                <div class="form-group">
                    <label for="city">Qyteti:</label>
                    <input type="text" id="city" name="city" required placeholder="P.sh. Tiranë">
                </div>
                
                <div class="form-group">
                    <label for="address">Adresa:</label>
                    <input type="text" id="address" name="address" required placeholder="P.sh. Rruga 'B' Nr. 10">
                </div>
                
                <div class="form-group">
                    <label for="property_type">Lloji i Pronës:</label>
                    <select id="property_type" name="property_type" required>
                        <option value="">Zgjidhni llojin</option>
                        <option value="house">Shtëpi</option>
                        <option value="apartment">Apartament</option>
                        <option value="land">Tokë</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="image">Foto kryesore:</label>
                    <input type="file" id="image" name="image" accept="image/*" required onchange="previewImage(this, 'imagePreview')">
                    <img id="imagePreview" src="#" alt="Preview" style="display: none; max-width: 100%; margin-top: 10px; border-radius: 5px;">
                </div>
                
                <button type="submit" class="btn btn-success" style="width: 100%;">Publiko Pronën</button>
            </form>
        </div>
    </div>

    <footer class="footer">
        <p>&copy; 2024 Agjencia Imobiliare. Të gjitha të drejtat e rezervuara.</p>
    </footer>

    <script src="js/script.js"></script>
</body>
</html>
