<?php
// acces.php

// Configuration de la base de données
$host = 'db';
$db   = 'CONTROLE_ACCES';
$user = 'sven';
$pass = 'sven';
$charset = 'utf8mb4';

// Token secret configuré dans le script Bash
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

$uid = $data['uid'] ?? null;
$timestamp = $data['timestamp'] ?? date('Y-m-d H:i:s');

if (!$uid) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "UID manquant"]);
    exit;
}

// 4. Connexion et logique BDD via PDO
try {
    $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];
    
    $pdo = new PDO($dsn, $user, $pass, $options);

    // A. Chercher le badge dans la table 'badges'
    $stmt = $pdo->prepare("SELECT id, nom, prenom, statut FROM badges WHERE numero_badge = ?");
    $stmt->execute([$uid]);
    $badge = $stmt->fetch();

    if (!$badge) {
        http_response_code(404);
        echo json_encode([
            "status" => "error", 
            "message" => "Accès REFUSÉ : Badge inconnu",
            "uid" => $uid
        ]);
        exit;
    }

    if ($badge['statut'] !== 'actif') {
        http_response_code(403);
        echo json_encode([
            "status" => "error", 
            "message" => "Accès REFUSÉ : Badge bloqué",
            "utilisateur" => $badge['prenom'] . " " . $badge['nom']
        ]);
        exit;
    }

    // B. Enregistrer l'accès dans la table 'acces'
    $badge_id = $badge['id'];
    $formatted_date = date('Y-m-d H:i:s', strtotime($timestamp));

    $stmt_insert = $pdo->prepare("INSERT INTO acces (badge_id, date_heure) VALUES (?, ?)");
    $stmt_insert->execute([$badge_id, $formatted_date]);

    // C. Réponse de succès
    http_response_code(200);
    echo json_encode([
        "status" => "success", 
        "message" => "Accès AUTORISÉ",
        "utilisateur" => $badge['prenom'] . " " . $badge['nom'],
        "heure" => $formatted_date
    ]);

} catch (\PDOException $e) {
    http_response_code(500);
    echo json_encode([
        "status" => "error", 
        "message" => "Erreur de base de données : " . $e->getMessage()
    ]);
}
?>
