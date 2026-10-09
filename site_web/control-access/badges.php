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
   ACTION BLOQUER / ACTIVER
========================= */

if (isset($_GET['action'], $_GET['id'])) {

    $id = (int) $_GET['id'];
    $action = $_GET['action'];

    if ($id > 0) {

        if ($action === 'bloquer') {

            $stmt = $mysqli->prepare("
                UPDATE badges
                SET statut = 'bloque'
                WHERE id = ?
            ");

            $stmt->bind_param('i', $id);
            $stmt->execute();
            $stmt->close();

        } elseif ($action === 'activer') {

            $stmt = $mysqli->prepare("
                UPDATE badges
                SET statut = 'actif'
                WHERE id = ?
            ");

            $stmt->bind_param('i', $id);
            $stmt->execute();
            $stmt->close();
        }
    }

    header('Location: badges.php');
    exit;
}

/* =========================
   RÉCUPÉRATION DES BADGES
========================= */

$sql = "
    SELECT
        badges.id,
        badges.numero_badge,
        badges.nom,
        badges.prenom,
        badges.date_creation,
        badges.date_expiration,
        badges.statut,
        COUNT(acces.id) AS nombre_acces
    FROM badges
    LEFT JOIN acces
        ON acces.badge_id = badges.id
    GROUP BY
        badges.id,
        badges.numero_badge,
        badges.nom,
        badges.prenom,
        badges.date_creation,
        badges.date_expiration,
        badges.statut
    ORDER BY badges.id DESC
";

$result = $mysqli->query($sql);

if (!$result) {
    die('Erreur lors de la récupération des badges.');
}

?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Gestion des badges - Contrôle d'accès</title>

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

            z-index: 10;
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
           EN-TÊTE
        ========================= */

        .page-header {
            display: flex;
            align-items: center;
            justify-content: space-between;

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

        .btn-add {
            background: #8b1e2d;
            color: white;

            text-decoration: none;

            padding: 12px 18px;

            border-radius: 8px;

            font-size: 14px;
            font-weight: bold;

            transition: 0.2s;
        }

        .btn-add:hover {
            background: #741825;
        }

        /* =========================
           TABLEAU
        ========================= */

        .table-container {
            background: white;

            border-radius: 12px;

            box-shadow: 0 2px 8px rgba(0,0,0,0.05);

            overflow: hidden;
        }

        .table-wrapper {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;

            min-width: 900px;
        }

        th {
            background: #f8f8f8;

            color: #555;

            font-size: 13px;

            text-align: left;

            padding: 15px;

            border-bottom: 1px solid #ddd;
        }

        td {
            padding: 15px;

            border-bottom: 1px solid #eee;

            font-size: 14px;
        }

        tr:last-child td {
            border-bottom: none;
        }

        tr:hover {
            background: #fafafa;
        }

        /* =========================
           BADGE NUMÉRO
        ========================= */

        .badge-number {
            color: #8b1e2d;
            font-weight: bold;
        }

        /* =========================
           STATUT
        ========================= */

        .status {
            display: inline-block;

            padding: 5px 10px;

            border-radius: 20px;

            font-size: 12px;

            font-weight: bold;
        }

        .status.active {
            background: #e8f7ee;
            color: #218739;
        }

        .status.blocked {
            background: #fdeaea;
            color: #c62828;
        }

        .status.expired {
            background: #fff2d9;
            color: #a56a00;
        }

        /* =========================
           ACTIONS
        ========================= */

        .actions {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .btn {
            text-decoration: none;

            padding: 7px 10px;

            border-radius: 6px;

            font-size: 12px;

            font-weight: bold;
        }

        .btn-view {
            background: #eef2f7;
            color: #34495e;
        }

        .btn-view:hover {
            background: #e1e7ee;
        }

        .btn-block {
            background: #fdeaea;
            color: #c62828;
        }

        .btn-block:hover {
            background: #f8d5d5;
        }

        .btn-active {
            background: #e8f7ee;
            color: #218739;
        }

        .btn-active:hover {
            background: #d7f1e1;
        }

        /* =========================
           AUCUN BADGE
        ========================= */

        .empty {
            text-align: center;

            padding: 60px 20px;

            color: #777;
        }

        .empty-icon {
            font-size: 45px;
            margin-bottom: 15px;
        }

        .empty h4 {
            font-size: 18px;
            color: #444;
            margin-bottom: 7px;
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

            .page-header {
                align-items: flex-start;
                gap: 15px;
                flex-direction: column;
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

        }

    </style>
</head>

<body>

<!-- =========================
     SIDEBAR
========================= -->

<aside class="sidebar">

    <div class="logo">

        <div class="logo-icon">🔐</div>

        <h1>Contrôle d'accès</h1>

        <p>Hôpital</p>

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

    <header class="topbar">

        <h2>Gestion des badges</h2>

        <div class="user">
            Connecté : <strong><?= htmlspecialchars($username) ?></strong>
        </div>

    </header>


    <section class="content">

        <!-- EN-TÊTE -->

        <div class="page-header">

            <div>

                <h3>Badges</h3>

                <p>
                    Gérez les badges d'accès de l'hôpital.
                </p>

            </div>

            <a href="ajouter_badge.php" class="btn-add">
                + Ajouter un badge
            </a>

        </div>


        <!-- TABLEAU -->

        <div class="table-container">

            <?php if ($result->num_rows > 0): ?>

                <div class="table-wrapper">

                    <table>

                        <thead>

                            <tr>

                                <th>Badge</th>

                                <th>Nom</th>

                                <th>Prénom</th>

                                <th>Date de création</th>

                                <th>Expiration</th>

                                <th>Accès</th>

                                <th>Statut</th>

                                <th>Actions</th>

                            </tr>

                        </thead>

                        <tbody>

                            <?php while ($badge = $result->fetch_assoc()): ?>

                                <?php

                                $dateExpiration = strtotime($badge['date_expiration']);
                                $aujourdHui = strtotime(date('Y-m-d'));

                                $expire = $dateExpiration < $aujourdHui;

                                ?>

                                <tr>

                                    <!-- NUMÉRO BADGE -->

                                    <td>

                                        <a
                                            href="badge.php?id=<?= (int)$badge['id'] ?>"
                                            class="badge-number"
                                        >
                                            <?= htmlspecialchars($badge['numero_badge']) ?>
                                        </a>

                                    </td>


                                    <!-- NOM -->

                                    <td>
                                        <?= htmlspecialchars($badge['nom']) ?>
                                    </td>


                                    <!-- PRÉNOM -->

                                    <td>
                                        <?= htmlspecialchars($badge['prenom']) ?>
                                    </td>


                                    <!-- DATE CRÉATION -->

                                    <td>

                                        <?= date(
                                            'd/m/Y',
                                            strtotime($badge['date_creation'])
                                        ) ?>

                                    </td>


                                    <!-- DATE EXPIRATION -->

                                    <td>

                                        <?= date(
                                            'd/m/Y',
                                            strtotime($badge['date_expiration'])
                                        ) ?>

                                    </td>


                                    <!-- NOMBRE D'ACCÈS -->

                                    <td>

                                        <?= (int)$badge['nombre_acces'] ?>

                                    </td>


                                    <!-- STATUT -->

                                    <td>

                                        <?php if ($expire): ?>

                                            <span class="status expired">
                                                Expiré
                                            </span>

                                        <?php elseif ($badge['statut'] === 'bloque'): ?>

                                            <span class="status blocked">
                                                Bloqué
                                            </span>

                                        <?php else: ?>

                                            <span class="status active">
                                                Actif
                                            </span>

                                        <?php endif; ?>

                                    </td>


                                    <!-- ACTIONS -->

                                    <td>

                                        <div class="actions">

                                            <a
                                                href="badge.php?id=<?= (int)$badge['id'] ?>"
                                                class="btn btn-view"
                                            >
                                                Voir
                                            </a>


                                            <?php if ($badge['statut'] === 'actif'): ?>

                                                <a
                                                    href="badges.php?action=bloquer&id=<?= (int)$badge['id'] ?>"
                                                    class="btn btn-block"
                                                    onclick="return confirm('Voulez-vous vraiment bloquer ce badge ?');"
                                                >
                                                    Bloquer
                                                </a>

                                            <?php else: ?>

                                                <a
                                                    href="badges.php?action=activer&id=<?= (int)$badge['id'] ?>"
                                                    class="btn btn-active"
                                                    onclick="return confirm('Voulez-vous vraiment réactiver ce badge ?');"
                                                >
                                                    Activer
                                                </a>

                                            <?php endif; ?>

                                        </div>

                                    </td>

                                </tr>

                            <?php endwhile; ?>

                        </tbody>

                    </table>

                </div>

            <?php else: ?>

                <div class="empty">

                    <div class="empty-icon">
                        🎫
                    </div>

                    <h4>Aucun badge enregistré</h4>

                    <p>
                        Commencez par créer un nouveau badge.
                    </p>

                </div>

            <?php endif; ?>

        </div>

    </section>

</main>

</body>

</html>

<?php
$mysqli->close();
?>