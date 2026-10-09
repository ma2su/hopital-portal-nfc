<?php
// register_dpi_badge.php
header('Content-Type: application/json; charset=utf-8');

$expected_token = 'semi';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["status" => "error", "message" => "Méthode non autorisée"]);
    exit;
}

// Vérification du token Bearer
$headers = getallheaders();
$auth_header = $headers['Authorization'] ?? $headers['authorization'] ?? '';

if (!preg_match('/Bearer\s(\S+)/', $auth_header, $matches) || $matches[1] !== $expected_token) {
    http_response_code(401);
    echo json_encode(["status" => "error", "message" => "Non autorisé"]);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

$username = trim($input['username'] ?? '');
$role     = strtolower(trim($input['role'] ?? ''));
$uid      = trim($input['uid'] ?? '');

if (empty($username) || empty($uid)) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Nom d'utilisateur ou UID manquant"]);
    exit;
}

if (!in_array($role, ['docteur', 'patient'], true)) {
    $role = 'patient'; // Rôle par défaut
}

try {
    $pdo = new PDO("mysql:host=db;dbname=DPI;charset=utf8mb4", 'sven', 'sven', [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);

    // 1. Vérifier si ce badge est déjà attribué à un autre compte
    $stmtCheckBadge = $pdo->prepare("SELECT username FROM users WHERE badge_uid = ? LIMIT 1");
    $stmtCheckBadge->execute([$uid]);
    $existingOwner = $stmtCheckBadge->fetch();

    if ($existingOwner) {
        http_response_code(409);
        echo json_encode([
            "status"  => "error",
            "message" => "Badge déjà associé au compte : " . $existingOwner['username']
        ]);
        exit;
    }

    // 2. Vérifier si l'utilisateur existe déjà dans DPI
    $stmtUser = $pdo->prepare("SELECT id FROM users WHERE username = ? LIMIT 1");
    $stmtUser->execute([$username]);
    $user = $stmtUser->fetch();

    if ($user) {
        // Mise à jour de l'utilisateur existant
        $stmtUpdate = $pdo->prepare("UPDATE users SET badge_uid = ?, role = ? WHERE id = ?");
        $stmtUpdate->execute([$uid, $role, $user['id']]);

        echo json_encode([
            "status"   => "success",
            "action"   => "updated",
            "message"  => "Badge attribué au compte existant",
            "username" => $username,
            "role"     => $role,
            "uid"      => $uid
        ]);
    } else {
        // Création d'un nouvel utilisateur (mot de passe initial vide ou hash par défaut)
        $defaultPasswordHash = password_hash('changeme123', PASSWORD_DEFAULT);
        $stmtInsert = $pdo->prepare("INSERT INTO users (username, password, role, badge_uid) VALUES (?, ?, ?, ?)");
        $stmtInsert->execute([$username, $defaultPasswordHash, $role, $uid]);

        echo json_encode([
            "status"   => "success",
            "action"   => "created",
            "message"  => "Nouvel utilisateur créé avec succès",
            "username" => $username,
            "role"     => $role,
            "uid"      => $uid
        ]);
    }

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Erreur BDD : " . $e->getMessage()]);
}
