<?php
$host = "localhost";
$user = "root";
$pass = "";
$db   = "real_estate_db";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Lidhja dështoi: " . $conn->connect_error);
}
?>
