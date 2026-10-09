<?php

session_start();

// ==========================================
// CONNEXION À LA BASE DE DONNÉES
// ==========================================

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

    $pdo->setAttribute(
        PDO::ATTR_ERRMODE,
        PDO::ERRMODE_EXCEPTION
    );

} catch (PDOException $e) {

    die("Erreur de connexion : " . $e->getMessage());

}


// ==========================================
// VÉRIFICATION DE LA CONNEXION
// ==========================================

if (!isset($_SESSION['username'])) {

    header("Location: index.php");
    exit;

}

$username_connecte = $_SESSION['username'];


// ==========================================
// RÉCUPÉRATION DU DOCTEUR CONNECTÉ
// ==========================================

$stmt_docteur = $pdo->prepare("
    SELECT id, nom, prenom
    FROM docteur
    WHERE nom = :nom
    LIMIT 1
");

$stmt_docteur->execute([
    ':nom' => $username_connecte
]);

$docteur = $stmt_docteur->fetch(PDO::FETCH_ASSOC);


// ==========================================
// RÉCUPÉRATION DE TOUS LES PATIENTS
// ==========================================

$stmt_patients = $pdo->query("
    SELECT
        patient.id,
        patient.nom,
        patient.prenom,
        patient.maladie,
        patient.chambre,
        docteur.nom AS docteur_nom,
        docteur.prenom AS docteur_prenom

    FROM patient

    LEFT JOIN docteur
        ON patient.docteur_id = docteur.id

    ORDER BY patient.id DESC
");

$patients = $stmt_patients->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>

<html lang="fr">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Patients - DPI</title>


    <style>

        /* ==========================================
           GLOBAL
        ========================================== */

        * {
            box-sizing: border-box;
        }

        body {

            font-family:
                'Segoe UI',
                Tahoma,
                Geneva,
                Verdana,
                sans-serif;

            background-color: #f4f5f7;

            margin: 0;

            padding: 0;

            display: flex;

            min-height: 100vh;

        }


        /* ==========================================
           SIDEBAR
        ========================================== */

        .sidebar {

            width: 250px;

            background-color: #ffffff;

            border-right: 1px solid #e0e0e0;

            padding: 20px 0;

            flex-shrink: 0;

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

            transition: 0.3s;

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


        /* ==========================================
           LOGOUT
        ========================================== */

        .logout {

            margin-top: 20px;

            color: #e53935 !important;

            border-top: 1px solid #eee;

        }


        .logout:hover {

            background-color: #ffebee;

            color: #c62828 !important;

        }


        /* ==========================================
           MAIN
        ========================================== */

        .main-content {

            flex-grow: 1;

            display: flex;

            flex-direction: column;

            overflow-y: auto;

        }


        /* ==========================================
           TOPBAR
        ========================================== */

        .topbar {

            min-height: 60px;

            background-color: #ffffff;

            border-bottom: 1px solid #e0e0e0;

            display: flex;

            justify-content: space-between;

            align-items: center;

            padding: 0 30px;

        }


        .page-title {

            font-size: 15px;

            font-weight: 600;

            color: #444;

        }


        .user-profile {

            display: flex;

            align-items: center;

            gap: 10px;

            font-weight: 600;

            color: #444;

        }


        /* ==========================================
           CONTENU
        ========================================== */

        .content-wrapper {

            padding: 30px;

        }


        h1 {

            font-size: 22px;

            color: #333;

            margin: 0 0 8px 0;

        }


        .subtitle {

            color: #777;

            font-size: 14px;

            margin-bottom: 25px;

        }


        /* ==========================================
           CARD
        ========================================== */

        .card {

            background-color: #ffffff;

            border-radius: 10px;

            box-shadow:
                0 1px 4px
                rgba(0, 0, 0, 0.06);

            padding: 25px;

        }


        .card-title {

            font-size: 15px;

            color: #444;

            font-weight: 600;

            margin-bottom: 20px;

        }


        /* ==========================================
           RECHERCHE
        ========================================== */

        .search-container {

            margin-bottom: 20px;

        }


        .search-input {

            width: 100%;

            max-width: 400px;

            padding: 11px 15px;

            border: 1px solid #ccc;

            border-radius: 7px;

            outline: none;

            font-size: 14px;

            transition: 0.2s;

        }


        .search-input:focus {

            border-color: #5c6bc0;

            box-shadow:
                0 0 0 3px
                rgba(92, 107, 192, 0.12);

        }


        /* ==========================================
           TABLE
        ========================================== */

        .table-container {

            max-height: 600px;

            overflow-y: auto;

            overflow-x: auto;

            border-radius: 6px;

        }


        table {

            width: 100%;

            border-collapse: collapse;

            text-align: left;

            font-size: 13px;

            min-width: 750px;

        }


        thead th {

            position: sticky;

            top: 0;

            background-color: #ffffff;

            z-index: 2;

            border-bottom: 2px solid #eee;

            padding: 13px 10px;

            color: #888;

            text-transform: uppercase;

            font-weight: 600;

            font-size: 11px;

        }


        tbody td {

            padding: 15px 10px;

            border-bottom: 1px solid #eee;

            color: #444;

        }


        tbody tr {

            transition: 0.2s;

        }


        tbody tr:hover {

            background-color: #f8f8ff;

        }


        /* ==========================================
           BADGES
        ========================================== */

        .patient-id {

            font-weight: 600;

            color: #5c6bc0;

        }


        .room {

            display: inline-block;

            background-color: #f0f0ff;

            color: #5c6bc0;

            padding: 5px 9px;

            border-radius: 6px;

            font-weight: 600;

        }


        .doctor {

            font-weight: 500;

        }


        /* ==========================================
           SCROLLBAR
        ========================================== */

        .table-container::-webkit-scrollbar {

            width: 8px;

            height: 8px;

        }


        .table-container::-webkit-scrollbar-track {

            background: #f1f1f1;

            border-radius: 10px;

        }


        .table-container::-webkit-scrollbar-thumb {

            background: #b0b0b0;

            border-radius: 10px;

        }


        .table-container::-webkit-scrollbar-thumb:hover {

            background: #888;

        }


        /* ==========================================
           PATIENT VIDE
        ========================================== */

        .empty {

            text-align: center;

            padding: 40px;

            color: #888;

        }


        /* ==========================================
           RETOUR
        ========================================== */

        .back-link {

            display: inline-block;

            margin-top: 20px;

            color: #5c6bc0;

            text-decoration: none;

            font-size: 14px;

        }


        .back-link:hover {

            text-decoration: underline;

        }


        /* ==========================================
           RESPONSIVE
        ========================================== */

        @media (max-width: 700px) {

            .sidebar {

                width: 200px;

            }

            .content-wrapper {

                padding: 20px;

            }

        }

    </style>

</head>


<body>


<!-- ==========================================
     SIDEBAR
========================================== -->

<div class="sidebar">


    <div class="sidebar-logo">

        📁 DPI

    </div>


    <a
        href="dashboard.php"
        class="menu-item"
    >

        Accueil

    </a>


    <a
        href="patients.php"
        class="menu-item"
    >

        Mes patients

    </a>


    <a
        href="patient_detail.php"
        class="menu-item active"
    >

        Tous les patients

    </a>


    <div class="menu-section">


        <span style="padding-left: 20px;">

            ADMINISTRATION

        </span>


        <a
            href="utilisateurs.php"
            class="menu-item"
            style="margin-top: 10px;"
        >

            Gestion des utilisateurs

        </a>


        <a
            href="logout.php"
            class="menu-item logout"
        >

            🚪 Se déconnecter

        </a>


    </div>

</div>


<!-- ==========================================
     CONTENU PRINCIPAL
========================================== -->

<div class="main-content">


    <!-- TOPBAR -->

    <div class="topbar">


        <div class="page-title">

            Liste des patients

        </div>


        <div class="user-profile">

            👨‍⚕️

            <?php

            if ($docteur) {

                echo htmlspecialchars(
                    $docteur['prenom']
                    . ' '
                    . $docteur['nom']
                );

            } else {

                echo htmlspecialchars(
                    $username_connecte
                );

            }

            ?>

        </div>


    </div>


    <!-- CONTENU -->

    <div class="content-wrapper">


        <h1>

            Tous les patients

        </h1>


        <div class="subtitle">

            Liste complète des patients enregistrés dans le Dossier Patient Informatisé.

        </div>


        <div class="card">


            <div class="card-title">

                👥 Patients enregistrés

            </div>


            <!-- RECHERCHE -->

            <div class="search-container">

                <input
                    type="text"
                    id="searchPatient"
                    class="search-input"
                    placeholder="🔎 Rechercher un patient..."
                    onkeyup="rechercherPatient()"
                >

            </div>


            <!-- TABLEAU -->

            <div class="table-container">


                <table id="patientsTable">


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
                                class="empty"
                            >

                                Aucun patient enregistré.

                            </td>

                        </tr>


                    <?php else: ?>


                        <?php foreach ($patients as $p): ?>


                            <tr>


                                <td class="patient-id">

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

                                    <span class="room">

                                        <?php

                                        echo htmlspecialchars(
                                            $p['chambre']
                                        );

                                        ?>

                                    </span>

                                </td>


                                <td class="doctor">

                                    <?php

                                    if (
                                        !empty($p['docteur_nom'])
                                    ) {

                                        echo htmlspecialchars(
                                            $p['docteur_prenom']
                                            . ' '
                                            . $p['docteur_nom']
                                        );

                                    } else {

                                        echo "Aucun docteur";

                                    }

                                    ?>

                                </td>


                            </tr>


                        <?php endforeach; ?>


                    <?php endif; ?>


                    </tbody>

                </table>


            </div>


        </div>


        <a
            href="dashboard.php"
            class="back-link"
        >

            ← Retour au dashboard

        </a>


    </div>

</div>


<!-- ==========================================
     RECHERCHE JAVASCRIPT
========================================== -->

<script>

function rechercherPatient() {

    const input =
        document.getElementById("searchPatient");

    const filter =
        input.value.toLowerCase();

    const table =
        document.getElementById("patientsTable");

    const rows =
        table.getElementsByTagName("tbody")[0]
             .getElementsByTagName("tr");


    for (let i = 0; i < rows.length; i++) {

        const text =
            rows[i].textContent.toLowerCase();

        if (text.includes(filter)) {

            rows[i].style.display = "";

        } else {

            rows[i].style.display = "none";

        }

    }

}

</script>


</body>

</html>