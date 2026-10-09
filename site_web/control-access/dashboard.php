<?php

session_start();

/*
 * Vérification de la connexion
 */
if (!isset($_SESSION['securite_user_id'])) {
    header('Location: login_securite.php');
    exit;
}

/*
 * Connexion à la base de données
 */
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {

    $mysqli = new mysqli(
        'db',
        'sven',
        'sven',
        'CONTROLE_ACCES',
        3306
    );

    $mysqli->set_charset('utf8mb4');

} catch (mysqli_sql_exception $e) {

    die("Erreur de connexion à la base de données.");
}


/*
 * Informations de l'utilisateur connecté
 */
$username = $_SESSION['securite_username'];


/*
 * Nombre total de badges
 */
$result = $mysqli->query("
    SELECT COUNT(*) AS total
    FROM badges
");

$total_badges = $result->fetch_assoc()['total'];


/*
 * Nombre de badges actifs
 */
$result = $mysqli->query("
    SELECT COUNT(*) AS total
    FROM badges
    WHERE statut = 'actif'
");

$badges_actifs = $result->fetch_assoc()['total'];


/*
 * Nombre de badges bloqués
 */
$result = $mysqli->query("
    SELECT COUNT(*) AS total
    FROM badges
    WHERE statut = 'bloque'
");

$badges_bloques = $result->fetch_assoc()['total'];


/*
 * Nombre d'accès aujourd'hui
 */
$result = $mysqli->query("
    SELECT COUNT(*) AS total
    FROM acces
    WHERE DATE(date_heure) = CURDATE()
");

$acces_aujourdhui = $result->fetch_assoc()['total'];


/*
 * Derniers accès
 */
$result = $mysqli->query("
    SELECT
        acces.id,
        acces.date_heure,
        badges.numero_badge,
        badges.nom,
        badges.prenom
    FROM acces
    INNER JOIN badges
        ON acces.badge_id = badges.id
    ORDER BY acces.date_heure DESC
    LIMIT 8
");

$derniers_acces = $result->fetch_all(MYSQLI_ASSOC);

?>

<!DOCTYPE html>
<html lang="fr">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Tableau de bord - Contrôle d'accès</title>

    <style>

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;

            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;

            background-color: #f5f6fa;

            color: #333;
        }


        /*
         * SIDEBAR
         */

        .sidebar {
            position: fixed;

            left: 0;
            top: 0;
            bottom: 0;

            width: 240px;

            background: #ffffff;

            border-right: 1px solid #e8e8e8;

            padding: 25px 15px;

            display: flex;
            flex-direction: column;
        }


        .logo {
            text-align: center;

            padding-bottom: 25px;

            border-bottom: 1px solid #eeeeee;

            margin-bottom: 20px;
        }


        .logo-icon {
            width: 55px;
            height: 55px;

            margin: 0 auto 10px;

            display: flex;
            align-items: center;
            justify-content: center;

            border-radius: 14px;

            background: linear-gradient(
                135deg,
                #ef5350,
                #d32f2f
            );

            color: white;

            font-size: 26px;

            box-shadow:
                0 5px 15px rgba(211, 47, 47, 0.20);
        }


        .logo h2 {
            margin: 0;

            font-size: 17px;

            color: #333;
        }


        .logo p {
            margin: 5px 0 0;

            font-size: 11px;

            color: #999;
        }


        /*
         * MENU
         */

        .menu {
            display: flex;

            flex-direction: column;

            gap: 5px;
        }


        .menu-title {
            font-size: 11px;

            font-weight: 600;

            color: #aaa;

            text-transform: uppercase;

            padding: 10px 13px 6px;
        }


        .menu-item {
            display: flex;

            align-items: center;

            gap: 12px;

            padding: 12px 13px;

            border-radius: 8px;

            color: #666;

            text-decoration: none;

            font-size: 14px;

            transition: all 0.2s ease;
        }


        .menu-item:hover {
            background-color: #fff1f1;

            color: #d32f2f;
        }


        .menu-item.active {
            background-color: #ffebee;

            color: #d32f2f;

            font-weight: 600;
        }


        .menu-icon {
            width: 20px;

            text-align: center;

            font-size: 16px;
        }


        /*
         * DECONNEXION
         */

        .logout {
            margin-top: auto;
        }


        .logout a {
            display: flex;

            align-items: center;

            gap: 12px;

            padding: 12px 13px;

            border-radius: 8px;

            color: #e53935;

            text-decoration: none;

            font-size: 14px;

            transition: all 0.2s ease;
        }


        .logout a:hover {
            background-color: #fff0f0;
        }


        /*
         * CONTENU PRINCIPAL
         */

        .main {
            margin-left: 240px;

            min-height: 100vh;
        }


        /*
         * TOPBAR
         */

        .topbar {
            height: 70px;

            background: #ffffff;

            border-bottom: 1px solid #eeeeee;

            display: flex;

            align-items: center;

            justify-content: space-between;

            padding: 0 30px;
        }


        .topbar h1 {
            margin: 0;

            font-size: 21px;

            color: #333;
        }


        .user {
            display: flex;

            align-items: center;

            gap: 10px;

            font-size: 14px;

            color: #555;
        }


        .user-icon {
            width: 35px;
            height: 35px;

            border-radius: 50%;

            display: flex;

            align-items: center;
            justify-content: center;

            background-color: #ffebee;

            color: #d32f2f;
        }


        /*
         * CONTENU
         */

        .content {
            padding: 30px;
        }


        .welcome {
            margin-bottom: 30px;
        }


        .welcome h2 {
            margin: 0 0 7px;

            font-size: 24px;

            color: #333;
        }


        .welcome p {
            margin: 0;

            color: #888;

            font-size: 14px;
        }


        /*
         * STATISTIQUES
         */

        .stats {
            display: grid;

            grid-template-columns:
                repeat(4, 1fr);

            gap: 20px;

            margin-bottom: 30px;
        }


        .stat-card {
            background: #ffffff;

            border-radius: 12px;

            padding: 22px;

            border: 1px solid #eeeeee;

            box-shadow:
                0 5px 15px rgba(0, 0, 0, 0.04);

            display: flex;

            align-items: center;

            gap: 15px;
        }


        .stat-icon {
            width: 50px;
            height: 50px;

            flex-shrink: 0;

            border-radius: 12px;

            display: flex;

            align-items: center;
            justify-content: center;

            font-size: 22px;
        }


        .stat-red {
            background-color: #ffebee;
        }


        .stat-green {
            background-color: #e8f5e9;
        }


        .stat-orange {
            background-color: #fff3e0;
        }


        .stat-blue {
            background-color: #e3f2fd;
        }


        .stat-info p {
            margin: 0 0 4px;

            font-size: 12px;

            color: #999;
        }


        .stat-info strong {
            font-size: 25px;

            color: #333;
        }


        /*
         * SECTION
         */

        .section {
            background: #ffffff;

            border: 1px solid #eeeeee;

            border-radius: 12px;

            box-shadow:
                0 5px 15px rgba(0, 0, 0, 0.04);

            overflow: hidden;
        }


        .section-header {
            padding: 20px 22px;

            border-bottom: 1px solid #eeeeee;

            display: flex;

            align-items: center;

            justify-content: space-between;
        }


        .section-header h3 {
            margin: 0;

            font-size: 17px;

            color: #333;
        }


        .section-header a {
            color: #d32f2f;

            font-size: 13px;

            text-decoration: none;
        }


        /*
         * TABLE
         */

        .table-container {
            max-height: 400px;

            overflow-y: auto;

            overflow-x: auto;
        }


        table {
            width: 100%;

            border-collapse: collapse;
        }


        th {
            text-align: left;

            padding: 13px 20px;

            font-size: 12px;

            color: #999;

            font-weight: 600;

            background-color: #fafafa;

            border-bottom: 1px solid #eeeeee;

            position: sticky;

            top: 0;

            z-index: 2;
        }


        td {
            padding: 14px 20px;

            font-size: 13px;

            color: #555;

            border-bottom: 1px solid #f0f0f0;
        }


        tr:last-child td {
            border-bottom: none;
        }


        .badge-number {
            font-weight: 600;

            color: #d32f2f;
        }


        .status {
            display: inline-block;

            padding: 5px 10px;

            border-radius: 20px;

            font-size: 11px;

            font-weight: 600;
        }


        .status-active {
            background-color: #e8f5e9;

            color: #2e7d32;
        }


        .empty {
            text-align: center;

            padding: 35px;

            color: #999;

            font-size: 13px;
        }


        /*
         * SCROLLBAR
         */

        .table-container::-webkit-scrollbar {
            width: 7px;
            height: 7px;
        }


        .table-container::-webkit-scrollbar-track {
            background: #f5f5f5;
        }


        .table-container::-webkit-scrollbar-thumb {
            background: #d0d0d0;

            border-radius: 10px;
        }


        .table-container::-webkit-scrollbar-thumb:hover {
            background: #aaa;
        }


        /*
         * RESPONSIVE
         */

        @media (max-width: 1100px) {

            .stats {
                grid-template-columns:
                    repeat(2, 1fr);
            }
        }


        @media (max-width: 700px) {

            .sidebar {
                width: 70px;

                padding: 15px 8px;
            }


            .logo h2,
            .logo p,
            .menu-title,
            .menu-item span,
            .logout span {
                display: none;
            }


            .logo-icon {
                width: 45px;
                height: 45px;

                font-size: 21px;
            }


            .menu-item,
            .logout a {
                justify-content: center;
            }


            .main {
                margin-left: 70px;
            }


            .topbar {
                padding: 0 20px;
            }


            .content {
                padding: 20px;
            }


            .stats {
                grid-template-columns: 1fr;
            }
        }

    </style>

</head>


<body>


<!-- SIDEBAR -->

<aside class="sidebar">

    <div class="logo">

        <div class="logo-icon">
            🔐
        </div>

        <h2>Contrôle d'accès</h2>

        <p>Système de sécurité</p>

    </div>


    <nav class="menu">

        <div class="menu-title">
            Navigation
        </div>


        <a href="dashboard.php" class="menu-item active">

            <span class="menu-icon">🏠</span>

            <span>Accueil</span>

        </a>


        <a href="badges.php" class="menu-item">

            <span class="menu-icon">🎫</span>

            <span>Gestion des badges</span>

        </a>


        <a href="acces.php" class="menu-item">

            <span class="menu-icon">🚪</span>

            <span>Historique des accès</span>

        </a>

    </nav>


    <div class="logout">

        <a href="login_securite.php?action=logout">

            <span class="menu-icon">🚪</span>

            <span>Déconnexion</span>

        </a>

    </div>

</aside>



<!-- CONTENU PRINCIPAL -->

<main class="main">


    <!-- TOPBAR -->

    <header class="topbar">

        <h1>
            Tableau de bord
        </h1>


        <div class="user">

            <div class="user-icon">
                👤
            </div>

            <span>
                <?= htmlspecialchars($username) ?>
            </span>

        </div>

    </header>



    <!-- CONTENU -->

    <div class="content">


        <div class="welcome">

            <h2>
                Bienvenue, <?= htmlspecialchars($username) ?>
            </h2>

            <p>
                Vue d'ensemble du système de contrôle d'accès.
            </p>

        </div>



        <!-- STATISTIQUES -->

        <div class="stats">


            <!-- Total badges -->

            <div class="stat-card">

                <div class="stat-icon stat-blue">
                    🎫
                </div>

                <div class="stat-info">

                    <p>
                        Total des badges
                    </p>

                    <strong>
                        <?= $total_badges ?>
                    </strong>

                </div>

            </div>


            <!-- Badges actifs -->

            <div class="stat-card">

                <div class="stat-icon stat-green">
                    🟢
                </div>

                <div class="stat-info">

                    <p>
                        Badges actifs
                    </p>

                    <strong>
                        <?= $badges_actifs ?>
                    </strong>

                </div>

            </div>


            <!-- Badges bloqués -->

            <div class="stat-card">

                <div class="stat-icon stat-red">
                    🔴
                </div>

                <div class="stat-info">

                    <p>
                        Badges bloqués
                    </p>

                    <strong>
                        <?= $badges_bloques ?>
                    </strong>

                </div>

            </div>


            <!-- Accès aujourd'hui -->

            <div class="stat-card">

                <div class="stat-icon stat-orange">
                    🚪
                </div>

                <div class="stat-info">

                    <p>
                        Accès aujourd'hui
                    </p>

                    <strong>
                        <?= $acces_aujourdhui ?>
                    </strong>

                </div>

            </div>

        </div>



        <!-- DERNIERS ACCÈS -->

        <div class="section">


            <div class="section-header">

                <h3>
                    Derniers accès
                </h3>

                <a href="acces.php">
                    Voir tout →
                </a>

            </div>


            <div class="table-container">

                <?php if (count($derniers_acces) > 0): ?>

                    <table>

                        <thead>

                            <tr>

                                <th>
                                    Badge
                                </th>

                                <th>
                                    Porteur
                                </th>

                                <th>
                                    Date
                                </th>

                                <th>
                                    Heure
                                </th>

                                <th>
                                    Statut
                                </th>

                            </tr>

                        </thead>


                        <tbody>

                            <?php foreach ($derniers_acces as $acces): ?>

                                <tr>

                                    <td>

                                        <span class="badge-number">

                                            <?= htmlspecialchars(
                                                $acces['numero_badge']
                                            ) ?>

                                        </span>

                                    </td>


                                    <td>

                                        <?= htmlspecialchars(
                                            $acces['prenom'] . ' ' . $acces['nom']
                                        ) ?>

                                    </td>


                                    <td>

                                        <?= date(
                                            'd/m/Y',
                                            strtotime($acces['date_heure'])
                                        ) ?>

                                    </td>


                                    <td>

                                        <?= date(
                                            'H:i:s',
                                            strtotime($acces['date_heure'])
                                        ) ?>

                                    </td>


                                    <td>

                                        <span class="status status-active">
                                            Accès enregistré
                                        </span>

                                    </td>

                                </tr>

                            <?php endforeach; ?>

                        </tbody>

                    </table>

                <?php else: ?>

                    <div class="empty">

                        Aucun accès enregistré pour le moment.

                    </div>

                <?php endif; ?>

            </div>

        </div>

    </div>

</main>


</body>

</html>