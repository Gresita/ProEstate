<?php
require 'config/db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $property_id = intval($_POST['property_id']);
    $sender_name = trim($_POST['name']);
    $sender_email = trim($_POST['email']);
    $message = trim($_POST['message']);
    
    if ($property_id && $sender_name && $sender_email && $message) {
        $stmt = $conn->prepare("INSERT INTO messages (property_id, sender_name, sender_email, message) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("isss", $property_id, $sender_name, $sender_email, $message);
        
        if ($stmt->execute()) {
            echo "Mesazhi u dërgua me sukses!";
        } else {
            echo "Gabim gjatë dërgimit!";
        }
    } else {
        echo "Ju lutemi plotësoni të gjitha fushat!";
    }
}
?>
