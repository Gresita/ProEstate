<?php
session_start();
require "config/db.php";

$message = "";
$messageType = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $action = $_POST["action"];

    if ($action == "register") {
        $name = trim($_POST["name"]);
        $email = trim($_POST["email"]);
        $password = $_POST["password"];
        $confirm_password = $_POST["confirm_password"];

        if ($password !== $confirm_password) {
            $message = "Fjalëkalimet nuk përputhen!";
            $messageType = "error";
        } else {
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $name, $email, $password_hash, $role = "user");
            
            if ($stmt->execute()) {
                $message = "Regjistrimi u krye me sukses! Tani mund të kyçeni.";
                $messageType = "success";
            } else {
                $message = "Gabim: Ky email është regjistruar tashmë!";
                $messageType = "error";
            }
        }
    } elseif ($action == "login") {
        $email = trim($_POST["email"]);
        $password = $_POST["password"];

        $stmt = $conn->prepare("SELECT id, name, password FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {
            if (password_verify($password, $row["password"])) {
                $_SESSION["user_id"] = $row["id"];
                $_SESSION["name"] = $row["name"];
                header("Location: index.php");
                exit;
            }
        }
        $message = "Email ose fjalëkalim i gabuar!";
        $messageType = "error";
    }
}
?>

<!DOCTYPE html>
<html lang="sq">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kyçu</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <header>
        <h1>🏠 Agjencia Imobiliare</h1>
        <nav><a href="index.php">Kryefaqja</a></nav>
    </header>

    <div class="container">
        <div class="form-container">
            <h2 class="page-title"><?php echo (isset($_GET["type"]) && $_GET["type"] == "register") ? "Regjistrohu" : "Kyçu"; ?></h2>
            
            <?php if($message): ?>
                <div class="alert alert-<?php echo $messageType; ?>"><?php echo $message; ?></div>
            <?php endif; ?>

            <form method="POST" action="login.php" style="display:<?php echo (isset($_GET["type"]) && $_GET["type"] == "register") ? "none" : "block"; ?>">
                <input type="hidden" name="action" value="login">
                <div class="form-group">
                    <label>Email:</label>
                    <input type="email" name="email" required>
                </div>
                <div class="form-group">
                    <label>Fjalëkalimi:</label>
                    <input type="password" name="password" required>
                </div>
                <button type="submit" class="btn" style="width: 100%;">Kyçu</button>
                <p style="text-align: center; margin-top: 15px;">
                    Nuk keni llogari? <a href="login.php?type=register">Regjistrohu këtu</a>
                </p>
            </form>

            <form method="POST" action="login.php?type=register" style="display:<?php echo (isset($_GET["type"]) && $_GET["type"] == "register") ? "block" : "none"; ?>">
                <input type="hidden" name="action" value="register">
                <div class="form-group">
                    <label>Emri i plotë:</label>
                    <input type="text" name="name" required>
                </div>
                <div class="form-group">
                    <label>Email:</label>
                    <input type="email" name="email" required>
                </div>
                <div class="form-group">
                    <label>Fjalëkalimi:</label>
                    <input type="password" name="password" required minlength="6">
                </div>
                <div class="form-group">
                    <label>Konfirmo Fjalëkalimin:</label>
                    <input type="password" name="confirm_password" required>
                </div>
                <button type="submit" class="btn btn-success" style="width: 100%;">Regjistrohu</button>
                <p style="text-align: center; margin-top: 15px;">
                    Keni llogari? <a href="login.php">Kyçu këtu</a>
                </p>
            </form>
        </div>
    </div>
</body>
</html>
