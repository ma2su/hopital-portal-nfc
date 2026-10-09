<?php
session_start();

// Traitement de la déconnexion
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    $_SESSION = [];
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    session_destroy();
    header('Location: ../index.php');
    exit;
}

// 1. Sécurité : Vérifier que l'utilisateur est authentifié
if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit;
}

$user_id  = $_SESSION['user_id'];
$username = $_SESSION['username'] ?? 'Patient';
$role     = $_SESSION['role'] ?? 'patient';

// 2. Connexion à la base de données DPI
$patientInfo = null;

try {
    $mysqli = new mysqli('db', 'sven', 'sven', 'DPI', 3306);
    $mysqli->set_charset('utf8mb4');

    // Récupérer les informations du compte utilisateur
    $stmt = $mysqli->prepare("
        SELECT id, username, role, created_at, badge_uid
        FROM users
        WHERE id = ?
        LIMIT 1
    ");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $patientInfo = $result->fetch_assoc();

    $stmt->close();
    $mysqli->close();
} catch (Exception $e) {
    // Gestion silencieuse côté vue
    $patientInfo = null;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Espace Patient — DPI</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f8fafc;
            color: #1e293b;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* Barre de navigation supérieure */
        .navbar {
            background: #ffffff;
            border-bottom: 1px solid #e2e8f0;
            padding: 16px 32px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        }

        .navbar-brand {
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 20px;
            font-weight: 700;
            color: #3b82f6;
            text-decoration: none;
        }

        .navbar-brand span {
            font-size: 26px;
        }

        .user-menu {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .badge-role {
            background-color: #e0f2fe;
            color: #0369a1;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
        }

        .btn-logout {
            background: #fee2e2;
            color: #dc2626;
            text-decoration: none;
            padding: 8px 14px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
            transition: all 0.2s ease;
        }

        .btn-logout:hover {
            background: #fecaca;
        }

        /* Contenu principal */
        .container {
            max-width: 1050px;
            margin: 35px auto;
            padding: 0 20px;
            width: 100%;
            flex: 1;
        }

        .welcome-card {
            background: linear-gradient(135deg, #3b82f6, #1d4ed8);
            border-radius: 16px;
            padding: 30px;
            color: white;
            box-shadow: 0 10px 25px rgba(29, 78, 216, 0.2);
            margin-bottom: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .welcome-card h1 {
            font-size: 26px;
            margin-bottom: 8px;
        }

        .welcome-card p {
            font-size: 15px;
            opacity: 0.9;
        }

        .badge-status {
            background: rgba(255, 255, 255, 0.2);
            padding: 10px 18px;
            border-radius: 12px;
            backdrop-filter: blur(8px);
            font-size: 13px;
        }

        /* Grille des fonctionnalités */
        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 22px;
            margin-bottom: 30px;
        }

        .card {
            background: #ffffff;
            border-radius: 14px;
            padding: 24px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .card:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.08);
        }

        .card-header {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 14px;
        }

        .card-icon {
            width: 44px;
            height: 44px;
            border-radius: 10px;
            background: #f1f5f9;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
        }

        .card h2 {
            font-size: 18px;
            color: #0f172a;
        }

        .card p {
            font-size: 14px;
            color: #64748b;
            line-height: 1.5;
            margin-bottom: 18px;
        }

        .card-link {
            display: inline-block;
            font-size: 13px;
            font-weight: 600;
            color: #2563eb;
            text-decoration: none;
        }

        .card-link:hover {
            text-decoration: underline;
        }

        /* Bloc Récapitulatif Profil */
        .profile-summary {
            background: #ffffff;
            border-radius: 14px;
            padding: 24px;
            border: 1px solid #e2e8f0;
        }

        .profile-summary h3 {
            font-size: 16px;
            margin-bottom: 16px;
            color: #334155;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #f1f5f9;
            font-size: 14px;
        }

        .info-row:last-child {
            border-bottom: none;
        }

        .info-label {
            color: #64748b;
        }

        .info-value {
            font-weight: 600;
            color: #0f172a;
        }

        footer {
            text-align: center;
            padding: 20px;
            font-size: 13px;
            color: #94a3b8;
            border-top: 1px solid #e2e8f0;
            background: #ffffff;
        }
    </style>
</head>
<body>

    <!-- Barre supérieure -->
    <nav class="navbar">
        <a href="#" class="navbar-brand">
            <span>🏥</span> Portail Patient DPI
        </a>
        <div class="user-menu">
            <span class="badge-role"><?= htmlspecialchars($role) ?></span>
            <span><?= htmlspecialchars($username) ?></span>
            <a href="login_dpi.php?action=logout" class="btn-logout">Déconnexion</a>
        </div>
    </nav>

    <!-- Conteneur principal -->
    <div class="container">

        <!-- Bannière d'accueil -->
        <div class="welcome-card">
            <div>
                <h1>Bonjour, <?= htmlspecialchars($username) ?> 👋</h1>
                <p>Bienvenue sur votre espace médical sécurisé.</p>
            </div>
            <div class="badge-status">
                🪪 Badge actif : <b><?= htmlspecialchars($patientInfo['badge_uid'] ?? 'Non associé') ?></b>
            </div>
        </div>

        <!-- Raccourcis patients -->
        <div class="grid">
            <div class="card">
                <div class="card-header">
                    <div class="card-icon">📋</div>
                    <h2>Mes Rendez-vous</h2>
                </div>
                <p>Consultez vos consultations à venir ainsi que l'historique de vos visites hospitalières.</p>
                <a href="#" class="card-link">Voir mes rendez-vous →</a>
            </div>

            <div class="card">
                <div class="card-header">
                    <div class="card-icon">📁</div>
                    <h2>Dossier & Résultats</h2>
                </div>
                <p>Accédez à vos ordonnances récentes, analyses de laboratoire et comptes rendus médicaux.</p>
                <a href="#" class="card-link">Consulter mes documents →</a>
            </div>

            <div class="card">
                <div class="card-header">
                    <div class="card-icon">👨‍⚕️</div>
                    <h2>Mon Équipe Médicale</h2>
                </div>
                <p>Retrouvez les coordonnées de votre médecin référent et du service qui assure votre suivi.</p>
                <a href="#" class="card-link">Contacter le secrétariat →</a>
            </div>
        </div>

        <!-- Informations de compte -->
        <div class="profile-summary">
            <h3>Informations du compte</h3>
            <div class="info-row">
                <span class="info-label">Identifiant patient</span>
                <span class="info-value">#<?= htmlspecialchars($patientInfo['id'] ?? $user_id) ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Nom d'utilisateur</span>
                <span class="info-value"><?= htmlspecialchars($username) ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">UID Carte sans contact (NFC)</span>
                <span class="info-value"><?= htmlspecialchars($patientInfo['badge_uid'] ?? 'Aucun badge scanné') ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Compte créé le</span>
                <span class="info-value"><?= htmlspecialchars($patientInfo['created_at'] ?? 'Non renseigné') ?></span>
            </div>
        </div>

    </div>

    <footer>
        Hôpital — Système d'Information Hospitalier (DPI) &copy; <?= date('Y') ?>
    </footer>

</body>
</html>
