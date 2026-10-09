<?php
// api_login_badge.php
header('Content-Type: application/json; charset=utf-8');

// Configuration
$expected_token = 'semi';
$host = 'db';
$db   = 'DPI';
$user = 'sven';
$pass = 'sven';

// 1. Vérification de la méthode HTTP (POST uniquement)
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["status" => "error", "message" => "Méthode non autorisée"]);
    exit;
}

// 2. Vérification du Bearer Token
$headers = getallheaders();
$auth_header = $headers['Authorization'] ?? $headers['authorization'] ?? '';

if (!preg_match('/Bearer\s(\S+)/', $auth_header, $matches) || $matches[1] !== $expected_token) {
    http_response_code(401);
    echo json_encode(["status" => "error", "message" => "Non autorisé"]);
    exit;
}

// 3. Récupération et parsing du payload JSON
$input = json_decode(file_get_contents('php://input'), true);
$uid = trim($input['uid'] ?? '');

if (empty($uid)) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "UID manquant"]);
    exit;
}

// 4. Enregistrement dans la file d'attente (badge_login_queue)
try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);

    // Création de la table tampon si elle n'existe pas encore
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS badge_login_queue (
            id INT AUTO_INCREMENT PRIMARY KEY,
            uid VARCHAR(64) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");

    // Vider les précédents scans non consommés et insérer le nouveau
    $pdo->exec("DELETE FROM badge_login_queue");
    
    $stmt = $pdo->prepare("INSERT INTO badge_login_queue (uid) VALUES (?)");
    $stmt->execute([$uid]);

    // Répondre avec succès au script Bash
    http_response_code(200);
    echo json_encode([
        "status"  => "success",
        "message" => "UID transmis à la file de connexion",
        "uid"     => $uid
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        "status"  => "error",
        "message" => "Erreur BDD : " . $e->getMessage()
    ]);
}
