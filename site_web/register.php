<?php
// register.php

// Configuration de la base de données
$host = 'db';
$db   = 'CONTROLE_ACCES';
$user = 'sven';
$pass = 'sven';
$charset = 'utf8mb4';

// Token secret configuré dans votre script Bash
$expected_token = 'semi';

// En-têtes pour répondre en JSON
header('Content-Type: application/json; charset=utf-8');

// 1. Vérifier la méthode HTTP (POST requis)
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["status" => "error", "message" => "Méthode non autorisée"]);
    exit;
}

// 2. Vérifier le Token d'authentification
$headers = getallheaders();
$auth_header = $headers['Authorization'] ?? $headers['authorization'] ?? '';

if (!preg_match('/Bearer\s(\S+)/', $auth_header, $matches) || $matches[1] !== $expected_token) {
    http_response_code(401);
    echo json_encode(["status" => "error", "message" => "Non autorisé (Token invalide)"]);
    exit;
}

// 3. Lire les données JSON reçues
$input = file_get_contents('php://input');
$data = json_decode($input, true);

$prenom = $data['prenom'] ?? null;
$nom = $data['nom'] ?? null;
$uid = $data['uid'] ?? null;

if (!$prenom || !$nom || !$uid) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Données manquantes (prénom, nom ou UID)"]);
    exit;
}

// 4. Enregistrement dans la base de données via PDO
try {
    $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];
    
    $pdo = new PDO($dsn, $user, $pass, $options);

    // Vérifier si le badge existe déjà
    $stmt_check = $pdo->prepare("SELECT id FROM badges WHERE numero_badge = ?");
    $stmt_check->execute([$uid]);
    if ($stmt_check->fetch()) {
        http_response_code(409);
        echo json_encode(["status" => "error", "message" => "Ce badge est déjà enregistré dans la base de données"]);
        exit;
    }

    // Insérer le nouveau badge dans la table 'badges'
    $stmt = $pdo->prepare("INSERT INTO badges (numero_badge, nom, prenom, statut) VALUES (?, ?, ?, 'actif')");
    $stmt->execute([$uid, $nom, $prenom]);

    // Réponse de succès
    http_response_code(200);
    echo json_encode([
        "status" => "success",
        "message" => "Badge enregistré avec succès !",
        "utilisateur" => "$prenom $nom",
        "uid" => $uid
    ]);

} catch (\PDOException $e) {
    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "message" => "Erreur de base de données : " . $e->getMessage()
    ]);
}
?>
