<?php
session_start();
require 'config/db.php';

if (!isset($_SESSION['user_id'])) {
    echo "Duhet të jeni i kyçur për të shtuar në të preferuarat!";
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $property_id = intval($_POST['property_id']);
    $user_id = $_SESSION['user_id'];
    
    // Kontrollo nëse ekziston
    $check = $conn->prepare("SELECT id FROM favorites WHERE user_id = ? AND property_id = ?");
    $check->bind_param("ii", $user_id, $property_id);
    $check->execute();
    
    if ($check->get_result()->num_rows > 0) {
        echo "Kjo pronë është tashmë në të preferuarat!";
    } else {
        $stmt = $conn->prepare("INSERT INTO favorites (user_id, property_id) VALUES (?, ?)");
        $stmt->bind_param("ii", $user_id, $property_id);
        
        if ($stmt->execute()) {
            echo "U shtua në të preferuarat!";
        } else {
            echo "Gabim!";
        }
    }
}
?>
