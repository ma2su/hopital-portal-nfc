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

    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

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


// Si aucun docteur ne correspond au username

if (!$docteur) {

    die("Erreur : aucun docteur ne correspond à l'utilisateur connecté.");

}

$docteur_id = $docteur['id'];


// ==========================================
// AJOUT D'UN PATIENT
// ==========================================

if (
    $_SERVER["REQUEST_METHOD"] == "POST"
    && isset($_POST['ajouter_patient'])
) {

    $nom = trim($_POST['nom']);
    $prenom = trim($_POST['prenom']);
    $maladie = trim($_POST['maladie']);
    $chambre = intval($_POST['chambre']);

    // Nom du docteur saisi dans le formulaire
    $nom_docteur = trim($_POST['docteur']);


    // Recherche du docteur grâce à son nom

    $stmt_recherche_docteur = $pdo->prepare("
        SELECT id
        FROM docteur
        WHERE nom = :nom
        LIMIT 1
    ");

    $stmt_recherche_docteur->execute([
        ':nom' => $nom_docteur
    ]);

    $docteur_trouve = $stmt_recherche_docteur->fetchColumn();


    // Le docteur n'existe pas

    if (!$docteur_trouve) {

        $error_message =
            "Aucun docteur trouvé avec le nom : "
            . htmlspecialchars($nom_docteur);

    } else {

        // Ajout du patient

        $sql = "
            INSERT INTO patient
            (nom, prenom, maladie, chambre, docteur_id)
            VALUES
            (:nom, :prenom, :maladie, :chambre, :docteur_id)
        ";

        $stmt = $pdo->prepare($sql);

        try {

            $stmt->execute([
                ':nom' => $nom,
                ':prenom' => $prenom,
                ':maladie' => $maladie,
                ':chambre' => $chambre,
                ':docteur_id' => $docteur_trouve
            ]);

            $success_message = "Patient ajouté avec succès.";

        } catch (PDOException $e) {

            $error_message =
                "Erreur lors de l'ajout : "
                . $e->getMessage();

        }

    }

}


// ==========================================
// SUPPRESSION D'UN PATIENT
// ==========================================

if (
    $_SERVER["REQUEST_METHOD"] == "POST"
    && isset($_POST['supprimer_patient'])
) {

    $nom = trim($_POST['nom_supprimer']);
    $prenom = trim($_POST['prenom_supprimer']);


    /*
     * On vérifie également le docteur_id.
     *
     * Cela évite qu'un docteur puisse supprimer
     * un patient appartenant à un autre docteur.
     */

    $sql = "
        DELETE FROM patient
        WHERE nom = :nom
        AND prenom = :prenom
        AND docteur_id = :docteur_id
    ";

    $stmt = $pdo->prepare($sql);

    try {

        $stmt->execute([
            ':nom' => $nom,
            ':prenom' => $prenom,
            ':docteur_id' => $docteur_id
        ]);

        if ($stmt->rowCount() > 0) {

            $success_message =
                "Patient supprimé avec succès.";

        } else {

            $error_message =
                "Aucun patient trouvé avec ce nom et ce prénom pour ce docteur.";

        }

    } catch (PDOException $e) {

        $error_message =
            "Erreur lors de la suppression : "
            . $e->getMessage();

    }

}


// ==========================================
// RÉCUPÉRATION DES PATIENTS
// ==========================================

$stmt = $pdo->query("
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

$patients = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>

<html lang="fr">

<head>

    <meta charset="UTF-8">

    <title>Accueil - Mon dashboard</title>


    <style>

        /* ==========================================
           GLOBAL
        ========================================== */

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

            height: 100vh;

        }


        /* ==========================================
           SIDEBAR
        ========================================== */

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
           DÉCONNEXION
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


        .search-bar {

            background-color: #f4f5f7;

            border: none;

            padding: 10px 20px;

            border-radius: 4px;

            width: 300px;

            outline: none;

        }


        .search-bar:focus {

            box-shadow:
                0 0 0 2px
                rgba(92, 107, 192, 0.2);

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

            font-size: 20px;

            color: #333;

            margin-bottom: 20px;

            font-weight: 600;

        }


        .dashboard-grid {

            display: grid;

            grid-template-columns: 2fr 1fr;

            gap: 20px;

            margin-bottom: 20px;

        }


        .card {

            background-color: #ffffff;

            border-radius: 8px;

            box-shadow:
                0 1px 3px
                rgba(0, 0, 0, 0.05);

            padding: 25px;

        }


        .card-title {

            font-size: 13px;

            color: #666;

            margin-bottom: 20px;

            font-weight: bold;

        }


        /* ==========================================
           LISTE DES PATIENTS
        ========================================== */

        .patient-list {

            max-height: 400px;

            overflow-y: auto;

            overflow-x: auto;

            border-radius: 6px;

        }


        /* En-tête du tableau qui reste visible */

        .patient-list thead th {

            position: sticky;

            top: 0;

            background-color: #ffffff;

            z-index: 2;

        }


        /* Scrollbar */

        .patient-list::-webkit-scrollbar {

            width: 8px;

            height: 8px;

        }


        .patient-list::-webkit-scrollbar-track {

            background: #f1f1f1;

            border-radius: 10px;

        }


        .patient-list::-webkit-scrollbar-thumb {

            background: #b0b0b0;

            border-radius: 10px;

        }


        .patient-list::-webkit-scrollbar-thumb:hover {

            background: #888;

        }


        /* ==========================================
           TABLE
        ========================================== */

        table {

            width: 100%;

            border-collapse: collapse;

            text-align: left;

            font-size: 13px;

        }


        th {

            border-bottom: 2px solid #eee;

            padding-bottom: 12px;

            color: #888;

            text-transform: uppercase;

            font-weight: 600;

            font-size: 11px;

        }


        td {

            padding: 15px 0;

            border-bottom: 1px solid #eee;

            color: #444;

        }


        /* ==========================================
           FORMULAIRE
        ========================================== */

        .form-group {

            margin-bottom: 15px;

        }


        .form-group label {

            display: block;

            margin-bottom: 5px;

            font-size: 13px;

            color: #555;

        }


        .form-group input {

            width: 100%;

            padding: 8px;

            border: 1px solid #ccc;

            border-radius: 4px;

            box-sizing: border-box;

            outline: none;

        }


        .form-group input:focus {

            border-color: #5c6bc0;

            box-shadow:
                0 0 0 2px
                rgba(92, 107, 192, 0.15);

        }


        /* ==========================================
           BOUTONS
        ========================================== */

        .btn-submit {

            background:
                linear-gradient(
                    135deg,
                    #5c6bc0,
                    #3f51b5
                );

            color: white;

            border: none;

            padding: 11px 20px;

            border-radius: 8px;

            cursor: pointer;

            font-size: 14px;

            font-weight: 600;

            transition: all 0.3s ease;

            box-shadow:
                0 3px 8px
                rgba(63, 81, 181, 0.25);

        }


        .btn-submit:hover {

            background:
                linear-gradient(
                    135deg,
                    #3f51b5,
                    #303f9f
                );

            transform: translateY(-3px);

            box-shadow:
                0 6px 15px
                rgba(63, 81, 181, 0.35);

        }


        .btn-submit:active {

            transform: translateY(-1px);

        }


        .btn-delete {

            background:
                linear-gradient(
                    135deg,
                    #e53935,
                    #c62828
                );

            box-shadow:
                0 3px 8px
                rgba(198, 40, 40, 0.25);

        }


        .btn-delete:hover {

            background:
                linear-gradient(
                    135deg,
                    #c62828,
                    #b71c1c
                );

            box-shadow:
                0 6px 15px
                rgba(198, 40, 40, 0.35);

        }


        /* ==========================================
           ALERTES
        ========================================== */

        .alert {

            padding: 10px;

            margin-bottom: 15px;

            border-radius: 4px;

            font-size: 14px;

        }


        .alert-success {

            background-color: #d4edda;

            color: #155724;

            border: 1px solid #c3e6cb;

        }


        .alert-danger {

            background-color: #f8d7da;

            color: #721c24;

            border: 1px solid #f5c6cb;

        }


        /* ==========================================
           LIENS
        ========================================== */

        .icon-btn {

            color: #5c6bc0;

            cursor: pointer;

            text-decoration: none;

        }


        .icon-btn:hover {

            text-decoration: underline;

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
        class="menu-item active"
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
        class="menu-item"
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

        <input
            type="text"
            class="search-bar"
            placeholder="Rechercher..."
        >


        <div class="user-profile">

            👨‍⚕️

            <?php

            echo htmlspecialchars(
                $docteur['prenom']
                . ' '
                . $docteur['nom']
            );

            ?>

        </div>

    </div>


    <!-- CONTENU -->

    <div class="content-wrapper">


        <h1>

            Accueil - Mon dashboard

        </h1>


        <!-- MESSAGES -->

        <?php if (isset($success_message)): ?>

            <div class="alert alert-success">

                <?php
                echo htmlspecialchars($success_message);
                ?>

            </div>

        <?php endif; ?>


        <?php if (isset($error_message)): ?>

            <div class="alert alert-danger">

                <?php
                echo htmlspecialchars($error_message);
                ?>

            </div>

        <?php endif; ?>


        <!-- ==========================================
             PATIENTS + AJOUT
        ========================================== -->

        <div class="dashboard-grid">


            <!-- LISTE DES PATIENTS -->

            <div class="card">

                <div class="card-title">

                    Liste des patients

                </div>


                <div class="patient-list">

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
                                    style="text-align:center;"
                                >

                                    Aucun patient trouvé.

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
                                            $p['docteur_nom']
                                            . ' '
                                            . $p['docteur_prenom']
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


            <!-- ==========================================
                 AJOUTER UN PATIENT
            ========================================== -->

            <div class="card">

                <div class="card-title">

                    Ajouter un patient

                </div>


                <form
                    method="POST"
                    action=""
                >


                    <div class="form-group">

                        <label for="nom">

                            Nom

                        </label>

                        <input
                            type="text"
                            id="nom"
                            name="nom"
                            required
                        >

                    </div>


                    <div class="form-group">

                        <label for="prenom">

                            Prénom

                        </label>

                        <input
                            type="text"
                            id="prenom"
                            name="prenom"
                            required
                        >

                    </div>


                    <div class="form-group">

                        <label for="maladie">

                            Maladie

                        </label>

                        <input
                            type="text"
                            id="maladie"
                            name="maladie"
                            required
                        >

                    </div>


                    <div class="form-group">

                        <label for="chambre">

                            Chambre

                        </label>

                        <input
                            type="number"
                            id="chambre"
                            name="chambre"
                            required
                        >

                    </div>


                    <div class="form-group">

                        <label for="docteur">

                            Docteur

                        </label>

                        <input
                            type="text"
                            id="docteur"
                            name="docteur"
                            placeholder="Nom du docteur"
                            required
                        >

                    </div>


                    <button
                        type="submit"
                        name="ajouter_patient"
                        class="btn-submit"
                    >

                        Ajouter

                    </button>


                </form>

            </div>

        </div>


        <!-- ==========================================
             SUPPRIMER UN PATIENT
        ========================================== -->

        <div
            class="card"
            style="margin-bottom: 20px;"
        >

            <div class="card-title">

                Retirer un patient

            </div>


            <form
                method="POST"
                action=""
            >


                <div class="form-group">

                    <label for="nom_supprimer">

                        Nom

                    </label>

                    <input
                        type="text"
                        id="nom_supprimer"
                        name="nom_supprimer"
                        required
                    >

                </div>


                <div class="form-group">

                    <label for="prenom_supprimer">

                        Prénom

                    </label>

                    <input
                        type="text"
                        id="prenom_supprimer"
                        name="prenom_supprimer"
                        required
                    >

                </div>


                <button
                    type="submit"
                    name="supprimer_patient"
                    class="btn-submit btn-delete"
                    onclick="return confirm('Voulez-vous vraiment supprimer ce patient ?');"
                >

                    Retirer

                </button>


            </form>

        </div>


        <!-- ==========================================
             LIENS UTILES
        ========================================== -->

        <div class="card">

            <div class="card-title">

                Liens utiles

            </div>


            <table>

                <thead>

                    <tr>

                        <th>Service</th>

                        <th>URL</th>

                    </tr>

                </thead>


                <tbody>


                    <tr>

                        <td>

                            Dossier Patient Informatisé

                        </td>

                        <td>

                            <a
                                href="#"
                                class="icon-btn"
                            >

                                🌐 Accéder

                            </a>

                        </td>

                    </tr>


                    <tr>

                        <td>

                            Dossier Patient Administratif

                        </td>

                        <td>

                            <a
                                href="#"
                                class="icon-btn"
                            >

                                🌐 Accéder

                            </a>

                        </td>

                    </tr>


                    <tr>

                        <td>

                            Ministère de la santé

                        </td>

                        <td>

                            <a
                                href="#"
                                class="icon-btn"
                            >

                                🌐 Accéder

                            </a>

                        </td>

                    </tr>


                </tbody>

            </table>

        </div>


    </div>

</div>


</body>

</html>