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
   RECHERCHE
========================= */

$recherche = '';

if (isset($_GET['recherche'])) {
    $recherche = trim($_GET['recherche']);
}

/* =========================
   RÉCUPÉRATION DES ACCÈS
========================= */

if ($recherche !== '') {

    $sql = "
        SELECT
            acces.id,
            acces.date_heure,
            badges.id AS badge_id,
            badges.numero_badge,
            badges.nom,
            badges.prenom,
            badges.statut,
            badges.date_expiration
        FROM acces
        INNER JOIN badges
            ON acces.badge_id = badges.id
        WHERE
            badges.numero_badge LIKE ?
            OR badges.nom LIKE ?
            OR badges.prenom LIKE ?
        ORDER BY acces.date_heure DESC
    ";

    $stmt = $mysqli->prepare($sql);

    $search = '%' . $recherche . '%';

    $stmt->bind_param(
        'sss',
        $search,
        $search,
        $search
    );

    $stmt->execute();

    $result = $stmt->get_result();

} else {

    $sql = "
        SELECT
            acces.id,
            acces.date_heure,
            badges.id AS badge_id,
            badges.numero_badge,
            badges.nom,
            badges.prenom,
            badges.statut,
            badges.date_expiration
        FROM acces
        INNER JOIN badges
            ON acces.badge_id = badges.id
        ORDER BY acces.date_heure DESC
    ";

    $result = $mysqli->query($sql);

    if (!$result) {
        die('Erreur lors de la récupération des accès.');
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

    <title>Historique des accès - Contrôle d'accès</title>

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

        /* =========================
           RECHERCHE
        ========================= */

        .search-container {
            background: white;

            padding: 20px;

            border-radius: 12px;

            box-shadow: 0 2px 8px rgba(0,0,0,0.05);

            margin-bottom: 20px;
        }

        .search-form {
            display: flex;
            gap: 10px;
        }

        .search-input {
            flex: 1;

            padding: 12px 15px;

            border: 1px solid #ddd;

            border-radius: 8px;

            font-size: 14px;

            outline: none;
        }

        .search-input:focus {
            border-color: #8b1e2d;
        }

        .btn-search {
            background: #8b1e2d;
            color: white;

            border: none;

            padding: 12px 20px;

            border-radius: 8px;

            cursor: pointer;

            font-weight: bold;

            font-size: 14px;
        }

        .btn-search:hover {
            background: #741825;
        }

        .btn-reset {
            display: flex;
            align-items: center;

            padding: 12px 16px;

            background: #eef2f7;

            color: #34495e;

            text-decoration: none;

            border-radius: 8px;

            font-size: 14px;

            font-weight: bold;
        }

        .btn-reset:hover {
            background: #e1e7ee;
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

            max-height: 650px;
            overflow-y: auto;
        }

        table {
            width: 100%;

            border-collapse: collapse;

            min-width: 850px;
        }

        th {
            position: sticky;
            top: 0;

            background: #f8f8f8;

            color: #555;

            font-size: 13px;

            text-align: left;

            padding: 15px;

            border-bottom: 1px solid #ddd;

            z-index: 2;
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
           BADGE
        ========================= */

        .badge-number {
            color: #8b1e2d;

            font-weight: bold;

            text-decoration: none;
        }

        .badge-number:hover {
            text-decoration: underline;
        }

        /* =========================
           DATE / HEURE
        ========================= */

        .date {
            color: #444;
        }

        .time {
            font-weight: bold;
            color: #333;
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
           ACCÈS ENREGISTRÉ
        ========================= */

        .access-ok {
            display: inline-flex;

            align-items: center;

            gap: 5px;

            color: #218739;

            font-size: 13px;

            font-weight: bold;
        }

        /* =========================
           VIDE
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

            .search-form {
                flex-direction: column;
            }

            .btn-search,
            .btn-reset {
                justify-content: center;
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

        <a href="badges.php">
            🎫 Gestion des badges
        </a>

        <a href="acces.php" class="active">
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
     CONTENU
========================= -->

<main class="main">


    <!-- TOPBAR -->

    <header class="topbar">

        <h2>
            Historique des accès
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
                Historique des passages
            </h3>

            <p>
                Consultez l'ensemble des accès enregistrés avec les badges.
            </p>

        </div>


        <!-- RECHERCHE -->

        <div class="search-container">

            <form
                method="GET"
                action="acces.php"
                class="search-form"
            >

                <input
                    type="text"
                    name="recherche"
                    class="search-input"
                    placeholder="Rechercher par badge, nom ou prénom..."
                    value="<?= htmlspecialchars($recherche) ?>"
                >

                <button
                    type="submit"
                    class="btn-search"
                >
                    🔎 Rechercher
                </button>

                <?php if ($recherche !== ''): ?>

                    <a
                        href="acces.php"
                        class="btn-reset"
                    >
                        Réinitialiser
                    </a>

                <?php endif; ?>

            </form>

        </div>


        <!-- TABLEAU -->

        <div class="table-container">

            <?php if ($result->num_rows > 0): ?>

                <div class="table-wrapper">

                    <table>

                        <thead>

                            <tr>

                                <th>
                                    Badge
                                </th>

                                <th>
                                    Nom
                                </th>

                                <th>
                                    Prénom
                                </th>

                                <th>
                                    Date
                                </th>

                                <th>
                                    Heure
                                </th>

                                <th>
                                    Statut du badge
                                </th>

                                <th>
                                    Accès
                                </th>

                            </tr>

                        </thead>


                        <tbody>

                            <?php while ($acces = $result->fetch_assoc()): ?>

                                <?php

                                $dateExpiration = strtotime(
                                    $acces['date_expiration']
                                );

                                $aujourdHui = strtotime(
                                    date('Y-m-d')
                                );

                                $expire = $dateExpiration < $aujourdHui;

                                ?>

                                <tr>


                                    <!-- BADGE -->

                                    <td>

                                        <a
                                            href="badge.php?id=<?= (int)$acces['badge_id'] ?>"
                                            class="badge-number"
                                        >
                                            <?= htmlspecialchars(
                                                $acces['numero_badge']
                                            ) ?>
                                        </a>

                                    </td>


                                    <!-- NOM -->

                                    <td>

                                        <?= htmlspecialchars(
                                            $acces['nom']
                                        ) ?>

                                    </td>


                                    <!-- PRÉNOM -->

                                    <td>

                                        <?= htmlspecialchars(
                                            $acces['prenom']
                                        ) ?>

                                    </td>


                                    <!-- DATE -->

                                    <td class="date">

                                        <?= date(
                                            'd/m/Y',
                                            strtotime($acces['date_heure'])
                                        ) ?>

                                    </td>


                                    <!-- HEURE -->

                                    <td class="time">

                                        <?= date(
                                            'H:i:s',
                                            strtotime($acces['date_heure'])
                                        ) ?>

                                    </td>


                                    <!-- STATUT -->

                                    <td>

                                        <?php if ($expire): ?>

                                            <span class="status expired">
                                                Expiré
                                            </span>

                                        <?php elseif ($acces['statut'] === 'bloque'): ?>

                                            <span class="status blocked">
                                                Bloqué
                                            </span>

                                        <?php else: ?>

                                            <span class="status active">
                                                Actif
                                            </span>

                                        <?php endif; ?>

                                    </td>


                                    <!-- ACCÈS -->

                                    <td>

                                        <span class="access-ok">
                                            ✓ Enregistré
                                        </span>

                                    </td>


                                </tr>

                            <?php endwhile; ?>

                        </tbody>

                    </table>

                </div>

            <?php else: ?>

                <div class="empty">

                    <div class="empty-icon">
                        📋
                    </div>

                    <?php if ($recherche !== ''): ?>

                        <h4>
                            Aucun résultat
                        </h4>

                        <p>
                            Aucun accès ne correspond à
                            « <?= htmlspecialchars($recherche) ?> ».
                        </p>

                    <?php else: ?>

                        <h4>
                            Aucun accès enregistré
                        </h4>

                        <p>
                            Aucun passage de badge n'a encore été enregistré.
                        </p>

                    <?php endif; ?>

                </div>

            <?php endif; ?>

        </div>


    </section>

</main>

</body>

</html>

<?php

if (isset($stmt)) {
    $stmt->close();
}

$mysqli->close();

?>