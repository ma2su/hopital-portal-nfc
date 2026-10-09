<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

try {
    $pdo = new PDO("mysql:host=db;dbname=DPI;charset=utf8mb4", 'sven', 'sven', [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);

    // 1. Chercher un scan récent (reçu dans les 10 dernières secondes)
    $stmt = $pdo->query("
        SELECT q.uid, u.id AS user_id, u.username, u.role
        FROM badge_login_queue q
        LEFT JOIN users u ON q.uid = u.badge_uid
        WHERE q.created_at >= NOW() - INTERVAL 10 SECOND
        ORDER BY q.id DESC 
        LIMIT 1
    ");
    $scan = $stmt->fetch();

    if ($scan) {
        // Consommer le scan pour ne pas le réutiliser
        $pdo->exec("DELETE FROM badge_login_queue");

        // Si le badge est bien rattaché à un compte utilisateur
        if (!empty($scan['user_id'])) {
            session_regenerate_id(true);

            $_SESSION['user_id']  = $scan['user_id'];
            $_SESSION['username'] = $scan['username'];
            
            $role = strtolower(trim($scan['role'] ?? 'patient'));
            $_SESSION['role'] = $role;

            // Définition de la page de destination selon le rôle
            if ($role === 'docteur') {
                $redirectUrl = 'dashboard.php';
            } else {
                $redirectUrl = 'espace_patient.php';
            }

            echo json_encode([
                "scanned"  => true,
                "status"   => "success",
                "username" => $scan['username'],
                "role"     => $role,
                "redirect" => $redirectUrl
            ]);
            exit;
        }

        // Cas où le badge est scanné mais absent de la table users
        echo json_encode([
            "scanned" => true,
            "status"  => "error",
            "message" => "Badge inconnu ou non rattaché à un utilisateur."
        ]);
        exit;
    }

    // Aucun badge en attente
    echo json_encode(["scanned" => false]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["scanned" => false, "status" => "error", "message" => "Erreur de base de données."]);
}
