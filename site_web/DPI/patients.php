<?php

session_start();

// ==============================
// Connexion à la base de données
// ==============================

$host = 'db';
$dbname = 'DPI';
$username = 'sven';
$password = 'sven';

try {

    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $username,
        $password
    );

    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

} catch (PDOException $e) {

    die("Erreur de connexion : " . $e->getMessage());

}


// ==============================
// Vérification de la connexion
// ==============================

if (!isset($_SESSION['username'])) {

    die("Vous devez être connecté pour accéder à cette page.");

}

$username_connecte = $_SESSION['username'];


// ==============================
// Recherche du docteur connecté
// ==============================

$stmt = $pdo->prepare("
    SELECT 
        docteur.id,
        docteur.nom,
        docteur.prenom
    FROM docteur
    INNER JOIN users
        ON users.username = docteur.nom
    WHERE users.username = :username
    LIMIT 1
");

$stmt->execute([
    ':username' => $username_connecte
]);

$docteur = $stmt->fetch(PDO::FETCH_ASSOC);


// ==============================
// Vérification du docteur
// ==============================

if (!$docteur) {

    die("Aucun docteur correspondant à cet utilisateur n'a été trouvé.");

}


// ==============================
// Récupération des patients
// ==============================

$stmt = $pdo->prepare("
    SELECT
        patient.id,
        patient.nom,
        patient.prenom,
        patient.maladie,
        patient.chambre,
        docteur.nom AS docteur_nom,
        docteur.prenom AS docteur_prenom
    FROM patient
    INNER JOIN docteur
        ON patient.docteur_id = docteur.id
    WHERE patient.docteur_id = :docteur_id
    ORDER BY patient.id DESC
");

$stmt->execute([
    ':docteur_id' => $docteur['id']
]);

$patients = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>

<html lang="fr">

<head>

    <meta charset="UTF-8">

    <title>Mes patients - DPI</title>

    <style>

        * {
            box-sizing: border-box;
        }

        body {

            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;

            background-color: #f4f5f7;

            margin: 0;

            padding: 0;

            display: flex;

            height: 100vh;

        }


        /* ==============================
           SIDEBAR
           ============================== */

        .sidebar {

            width: 250px;

            background-color: #ffffff;

            border-right: 1px solid #e0e0e0;

            padding: 20px 0;

            overflow-y: auto;

        }


        .sidebar-logo {

            display: flex;

            align-items: center;

            padding: 0 20px;

            margin-bottom: 30px;

            font-weight: bold;

            color: #f57c00;

            font-size: 18px;

        }


        .menu-item {

            padding: 12px 20px;

            color: #555;

            text-decoration: none;

            display: block;

            font-size: 14px;

            transition: all 0.25s ease;

        }


        .menu-item:hover {

            background-color: #f0f0ff;

            color: #5c6bc0;

            padding-left: 25px;

        }


        .menu-item.active {

            background-color: #f0f0ff;

            color: #5c6bc0;

            border-right: 3px solid #5c6bc0;

            font-weight: bold;

        }


        .menu-section {

            margin-top: 20px;

            border-top: 1px solid #e0e0e0;

            padding-top: 10px;

            font-size: 11px;

            text-transform: uppercase;

            color: #aaa;

        }


        .menu-section .menu-item {

            text-transform: none;

            color: #555;

            font-size: 14px;

        }


        /* ==============================
           CONTENU PRINCIPAL
           ============================== */

        .main-content {

            flex-grow: 1;

            display: flex;

            flex-direction: column;

            overflow-y: auto;

        }


        /* ==============================
           TOPBAR
           ============================== */

        .topbar {

            min-height: 60px;

            background-color: #ffffff;

            border-bottom: 1px solid #e0e0e0;

            display: flex;

            justify-content: space-between;

            align-items: center;

            padding: 0 30px;

        }


        .search-bar {

            background-color: #f4f5f7;

            border: none;

            padding: 10px 20px;

            border-radius: 4px;

            width: 300px;

        }


        .user-profile {

            display: flex;

            align-items: center;

            gap: 10px;

            cursor: pointer;

            color: #555;

            font-size: 14px;

        }


        /* ==============================
           CONTENU
           ============================== */

        .content-wrapper {

            padding: 30px;

        }


        h1 {

            font-size: 20px;

            color: #333;

            margin-bottom: 20px;

            font-weight: 600;

        }


        .doctor-info {

            color: #777;

            font-size: 14px;

            margin-top: -10px;

            margin-bottom: 25px;

        }


        /* ==============================
           CARD
           ============================== */

        .card {

            background-color: #ffffff;

            border-radius: 8px;

            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);

            padding: 25px;

        }


        .card-title {

            font-size: 13px;

            color: #666;

            margin-bottom: 20px;

            font-weight: bold;

        }


        /* ==============================
           TABLEAU
           ============================== */

        table {

            width: 100%;

            border-collapse: collapse;

            text-align: left;

            font-size: 13px;

        }


        th {

            border-bottom: 2px solid #eee;

            padding: 12px;

            color: #888;

            text-transform: uppercase;

            font-weight: 600;

            font-size: 11px;

        }


        td {

            padding: 15px 12px;

            border-bottom: 1px solid #eee;

            color: #444;

        }


        tbody tr {

            transition: background-color 0.2s ease;

        }


        tbody tr:hover {

            background-color: #f8f8ff;

        }


        /* ==============================
           MESSAGE AUCUN PATIENT
           ============================== */

        .no-patient {

            text-align: center;

            padding: 30px;

            color: #888;

        }


        /* ==============================
           RESPONSIVE
           ============================== */

        @media (max-width: 900px) {

            .sidebar {

                width: 200px;

            }

            .search-bar {

                width: 200px;

            }

            .content-wrapper {

                padding: 20px;

            }

            table {

                font-size: 12px;

            }

        }
        .logout {
            margin-top: 20px;
            color: #e53935 !important;
            border-top: 1px solid #eee;
        }

        .logout:hover {
            background-color: #ffebee;
            color: #c62828 !important;
            padding-left: 25px;
        }


    </style>

</head>


<body>


    <!-- ==============================
         SIDEBAR
         ============================== -->

    <div class="sidebar">

        <div class="sidebar-logo">
            📁 DPI
        </div>


        <a href="dashboard.php" class="menu-item">
            Accueil
        </a>


        <a href="patients.php" class="menu-item active">
            Mes patients
        </a>


        <a href="patient_detail.php" class="menu-item">
            Tous les patients
        </a>


        <div class="menu-section">

            <span style="padding-left: 20px;">
                ADMINISTRATION
            </span>


            <a href="utilisateurs.php" class="menu-item" style="margin-top: 10px;">
                Gestion des utilisateurs
            </a>
            <a href="logout.php" class="menu-item logout">🚪 Se déconnecter</a>

        </div>

    </div>



    <!-- ==============================
         CONTENU PRINCIPAL
         ============================== -->

    <div class="main-content">


        <!-- TOPBAR -->

        <div class="topbar">

            <input
                type="text"
                class="search-bar"
                placeholder="Rechercher..."
            >


            <div class="user-profile">

                👨‍⚕️

                <?php
                echo htmlspecialchars(
                    $docteur['prenom'] . ' ' . $docteur['nom']
                );
                ?>

            </div>

        </div>



        <!-- CONTENU -->

        <div class="content-wrapper">


            <h1>
                Mes patients
            </h1>


            <div class="doctor-info">

                Docteur connecté :
                
                <strong>
                    <?php
                    echo htmlspecialchars(
                        $docteur['prenom'] . ' ' . $docteur['nom']
                    );
                    ?>
                </strong>

            </div>



            <!-- LISTE DES PATIENTS -->

            <div class="card">


                <div class="card-title">
                    Liste de mes patients
                </div>


                <table>


                    <thead>

                        <tr>

                            <th>ID</th>

                            <th>Nom</th>

                            <th>Prénom</th>

                            <th>Maladie</th>

                            <th>Chambre</th>

                            <th>Docteur</th>

                        </tr>

                    </thead>



                    <tbody>


                        <?php if (empty($patients)): ?>


                            <tr>

                                <td
                                    colspan="6"
                                    class="no-patient"
                                >

                                    Aucun patient ne vous est attribué.

                                </td>

                            </tr>


                        <?php else: ?>


                            <?php foreach ($patients as $p): ?>


                                <tr>


                                    <td>

                                        <?php
                                        echo htmlspecialchars(
                                            $p['id']
                                        );
                                        ?>

                                    </td>


                                    <td>

                                        <?php
                                        echo htmlspecialchars(
                                            $p['nom']
                                        );
                                        ?>

                                    </td>


                                    <td>

                                        <?php
                                        echo htmlspecialchars(
                                            $p['prenom']
                                        );
                                        ?>

                                    </td>


                                    <td>

                                        <?php
                                        echo htmlspecialchars(
                                            $p['maladie']
                                        );
                                        ?>

                                    </td>


                                    <td>

                                        <?php
                                        echo htmlspecialchars(
                                            $p['chambre']
                                        );
                                        ?>

                                    </td>


                                    <td>

                                        <?php
                                        echo htmlspecialchars(
                                            $p['docteur_prenom']
                                            . ' '
                                            . $p['docteur_nom']
                                        );
                                        ?>

                                    </td>


                                </tr>


                            <?php endforeach; ?>


                        <?php endif; ?>


                    </tbody>


                </table>


            </div>


        </div>


    </div>


</body>

</html>