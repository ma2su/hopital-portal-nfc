<?php

session_start();

/* =========================
   VÉRIFICATION SESSION
========================= */

if (!isset($_SESSION['securite_user_id'])) {
    header('Location: login_securite.php');
    exit;
}

/* =========================
   CONNEXION BDD
========================= */

$mysqli = new mysqli(
    'db',
    'sven',
    'sven',
    'CONTROLE_ACCES',
    3306
);

if ($mysqli->connect_error) {
    die('Erreur de connexion à la base de données.');
}

$mysqli->set_charset('utf8mb4');

$username = $_SESSION['securite_username'];

/* =========================
   VARIABLES
========================= */

$nom = '';
$prenom = '';
$date_expiration = '';

$erreur = '';

/* =========================
   TRAITEMENT DU FORMULAIRE
========================= */

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nom = trim($_POST['nom'] ?? '');
    $prenom = trim($_POST['prenom'] ?? '');
    $date_expiration = trim($_POST['date_expiration'] ?? '');

    /* =========================
       VALIDATION
    ========================= */

    if ($nom === '' || $prenom === '' || $date_expiration === '') {

        $erreur = 'Veuillez remplir tous les champs.';

    } elseif (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date_expiration)) {

        $erreur = 'La date d’expiration est invalide.';

    } elseif ($date_expiration < date('Y-m-d')) {

        $erreur = 'La date d’expiration doit être dans le futur.';

    } else {

        /* =========================
           CRÉATION DU BADGE
        ========================= */

        /*
         * On utilise une transaction afin de pouvoir :
         * 1. créer le badge
         * 2. récupérer son ID
         * 3. générer son numéro BADGE-XXXX
         */

        $mysqli->begin_transaction();

        try {

            /*
             * On utilise temporairement une valeur unique
             * pour numero_badge.
             */

            $numero_temporaire = 'TEMP-' . uniqid('', true);

            $stmt = $mysqli->prepare("
                INSERT INTO badges
                (
                    numero_badge,
                    nom,
                    prenom,
                    date_expiration,
                    statut
                )
                VALUES (?, ?, ?, ?, 'actif')
            ");

            if (!$stmt) {
                throw new Exception('Erreur lors de la préparation de la requête.');
            }

            $stmt->bind_param(
                'ssss',
                $numero_temporaire,
                $nom,
                $prenom,
                $date_expiration
            );

            if (!$stmt->execute()) {
                throw new Exception('Impossible de créer le badge.');
            }

            $badge_id = $stmt->insert_id;

            $stmt->close();

            /* =========================
               GÉNÉRATION NUMÉRO BADGE
            ========================= */

            $numero_badge = 'BADGE-' . str_pad(
                $badge_id,
                4,
                '0',
                STR_PAD_LEFT
            );

            $stmt = $mysqli->prepare("
                UPDATE badges
                SET numero_badge = ?
                WHERE id = ?
            ");

            if (!$stmt) {
                throw new Exception('Erreur lors de la préparation du numéro de badge.');
            }

            $stmt->bind_param(
                'si',
                $numero_badge,
                $badge_id
            );

            if (!$stmt->execute()) {
                throw new Exception('Impossible de générer le numéro du badge.');
            }

            $stmt->close();

            /* =========================
               VALIDATION
            ========================= */

            $mysqli->commit();

            /*
             * Retour vers la gestion des badges
             */

            header('Location: badges.php');
            exit;

        } catch (Exception $e) {

            $mysqli->rollback();

            $erreur = $e->getMessage();
        }
    }
}

?>

<!DOCTYPE html>
<html lang="fr">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Ajouter un badge - Contrôle d'accès</title>

    <style>

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: Arial, sans-serif;

            background: #f5f6f8;

            color: #222;
        }

        /* =========================
           SIDEBAR
        ========================= */

        .sidebar {

            position: fixed;

            left: 0;
            top: 0;

            width: 250px;
            height: 100vh;

            background: #8b1e2d;

            color: white;

            padding: 25px 15px;

            display: flex;

            flex-direction: column;
        }

        .logo {

            text-align: center;

            margin-bottom: 35px;
        }

        .logo-icon {

            font-size: 42px;

            margin-bottom: 8px;
        }

        .logo h1 {

            font-size: 20px;
        }

        .logo p {

            font-size: 12px;

            opacity: 0.8;

            margin-top: 4px;
        }

        .menu {

            display: flex;

            flex-direction: column;

            gap: 8px;
        }

        .menu a {

            text-decoration: none;

            color: white;

            padding: 13px 15px;

            border-radius: 8px;

            font-size: 14px;

            transition: 0.2s;
        }

        .menu a:hover {

            background: rgba(255,255,255,0.12);
        }

        .menu a.active {

            background: rgba(255,255,255,0.18);

            font-weight: bold;
        }

        .logout {

            margin-top: auto;
        }

        .logout a {

            display: block;

            text-align: center;

            background: #6d1522;

            color: white;

            text-decoration: none;

            padding: 12px;

            border-radius: 8px;

            font-size: 14px;
        }

        .logout a:hover {

            background: #5a111c;
        }

        /* =========================
           CONTENU
        ========================= */

        .main {

            margin-left: 250px;

            min-height: 100vh;
        }

        .topbar {

            height: 70px;

            background: white;

            border-bottom: 1px solid #ddd;

            display: flex;

            align-items: center;

            justify-content: space-between;

            padding: 0 30px;
        }

        .topbar h2 {

            font-size: 22px;

            color: #333;
        }

        .user {

            font-size: 14px;

            color: #666;
        }

        .content {

            padding: 30px;
        }

        /* =========================
           FORMULAIRE
        ========================= */

        .page-header {

            margin-bottom: 25px;
        }

        .page-header h3 {

            font-size: 25px;

            color: #333;
        }

        .page-header p {

            margin-top: 5px;

            color: #777;

            font-size: 14px;
        }

        .form-container {

            max-width: 700px;

            background: white;

            border-radius: 12px;

            box-shadow: 0 2px 8px rgba(0,0,0,0.05);

            padding: 30px;
        }

        /* =========================
           ERREUR
        ========================= */

        .error {

            background: #fdeaea;

            border: 1px solid #f3b8b8;

            color: #b71c1c;

            padding: 13px 15px;

            border-radius: 8px;

            margin-bottom: 20px;

            font-size: 14px;
        }

        /* =========================
           CHAMPS
        ========================= */

        .form-group {

            margin-bottom: 20px;
        }

        .form-group label {

            display: block;

            margin-bottom: 8px;

            font-size: 14px;

            font-weight: bold;

            color: #444;
        }

        .required {

            color: #8b1e2d;
        }

        .form-group input {

            width: 100%;

            padding: 12px 14px;

            border: 1px solid #d8d8d8;

            border-radius: 8px;

            font-size: 14px;

            outline: none;

            transition: 0.2s;
        }

        .form-group input:focus {

            border-color: #8b1e2d;

            box-shadow: 0 0 0 2px rgba(139,30,45,0.08);
        }

        .help {

            margin-top: 6px;

            font-size: 12px;

            color: #888;
        }

        /* =========================
           INFO BADGE
        ========================= */

        .info {

            background: #f8f8f8;

            border-left: 4px solid #8b1e2d;

            padding: 15px;

            margin-bottom: 25px;

            border-radius: 5px;
        }

        .info strong {

            display: block;

            color: #444;

            margin-bottom: 5px;
        }

        .info p {

            font-size: 13px;

            color: #777;

            line-height: 1.5;
        }

        /* =========================
           BOUTONS
        ========================= */

        .buttons {

            display: flex;

            align-items: center;

            gap: 10px;

            margin-top: 25px;
        }

        .btn {

            padding: 12px 18px;

            border-radius: 8px;

            text-decoration: none;

            font-size: 14px;

            font-weight: bold;

            border: none;

            cursor: pointer;
        }

        .btn-primary {

            background: #8b1e2d;

            color: white;
        }

        .btn-primary:hover {

            background: #741825;
        }

        .btn-secondary {

            background: #eef2f7;

            color: #34495e;
        }

        .btn-secondary:hover {

            background: #e1e7ee;
        }

        /* =========================
           RESPONSIVE
        ========================= */

        @media (max-width: 800px) {

            .sidebar {

                width: 210px;
            }

            .main {

                margin-left: 210px;
            }

            .content {

                padding: 20px;
            }
        }

        @media (max-width: 600px) {

            .sidebar {

                position: relative;

                width: 100%;

                height: auto;
            }

            .main {

                margin-left: 0;
            }

            .logout {

                margin-top: 20px;
            }

            .topbar {

                padding: 0 20px;
            }

            .user {

                display: none;
            }

            .form-container {

                padding: 20px;
            }

            .buttons {

                flex-direction: column;

                align-items: stretch;
            }

            .btn {

                text-align: center;
            }
        }

    </style>

</head>

<body>

<!-- =========================
     SIDEBAR
========================= -->

<aside class="sidebar">

    <div class="logo">

        <div class="logo-icon">
            🔐
        </div>

        <h1>
            Contrôle d'accès
        </h1>

        <p>
            Hôpital
        </p>

    </div>


    <nav class="menu">

        <a href="dashboard.php">
            🏠 Accueil
        </a>

        <a href="badges.php" class="active">
            🎫 Gestion des badges
        </a>

        <a href="acces.php">
            📋 Historique des accès
        </a>

    </nav>


    <div class="logout">

        <a href="login_securite.php?action=logout">
            🚪 Déconnexion
        </a>

    </div>

</aside>


<!-- =========================
     CONTENU PRINCIPAL
========================= -->

<main class="main">


    <!-- TOPBAR -->

    <header class="topbar">

        <h2>
            Ajouter un badge
        </h2>

        <div class="user">

            Connecté :
            <strong>
                <?= htmlspecialchars($username) ?>
            </strong>

        </div>

    </header>


    <section class="content">


        <!-- TITRE -->

        <div class="page-header">

            <h3>
                Créer un nouveau badge
            </h3>

            <p>
                Enregistrez un nouveau badge d'accès pour un membre du personnel.
            </p>

        </div>


        <!-- FORMULAIRE -->

        <div class="form-container">


            <?php if ($erreur !== ''): ?>

                <div class="error">

                    ⚠️
                    <?= htmlspecialchars($erreur) ?>

                </div>

            <?php endif; ?>


            <div class="info">

                <strong>
                    🎫 Création du badge
                </strong>

                <p>
                    Le numéro du badge sera généré automatiquement.
                    Le badge sera créé avec le statut
                    <strong>actif</strong>.
                </p>

            </div>


            <form
                method="POST"
                action="ajouter_badge.php"
            >


                <!-- NOM -->

                <div class="form-group">

                    <label for="nom">

                        Nom
                        <span class="required">*</span>

                    </label>

                    <input
                        type="text"
                        id="nom"
                        name="nom"
                        value="<?= htmlspecialchars($nom) ?>"
                        placeholder="Exemple : Dupont"
                        maxlength="100"
                        required
                    >

                </div>


                <!-- PRÉNOM -->

                <div class="form-group">

                    <label for="prenom">

                        Prénom
                        <span class="required">*</span>

                    </label>

                    <input
                        type="text"
                        id="prenom"
                        name="prenom"
                        value="<?= htmlspecialchars($prenom) ?>"
                        placeholder="Exemple : Jean"
                        maxlength="100"
                        required
                    >

                </div>


                <!-- DATE EXPIRATION -->

                <div class="form-group">

                    <label for="date_expiration">

                        Date d'expiration
                        <span class="required">*</span>

                    </label>

                    <input
                        type="date"
                        id="date_expiration"
                        name="date_expiration"
                        value="<?= htmlspecialchars($date_expiration) ?>"
                        min="<?= date('Y-m-d', strtotime('+1 day')) ?>"
                        required
                    >

                    <p class="help">
                        Le badge ne pourra pas être utilisé après cette date.
                    </p>

                </div>


                <!-- BOUTONS -->

                <div class="buttons">

                    <a
                        href="badges.php"
                        class="btn btn-secondary"
                    >
                        ← Annuler
                    </a>

                    <button
                        type="submit"
                        class="btn btn-primary"
                    >
                        ✓ Créer le badge
                    </button>

                </div>


            </form>

        </div>


    </section>

</main>

</body>

</html>

<?php

$mysqli->close();

?>