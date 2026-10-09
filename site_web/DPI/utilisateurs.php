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
// RÉCUPÉRATION DE L'UTILISATEUR
// ==========================================

$stmt_user = $pdo->prepare("
    SELECT
        id,
        username,
        password,
        role
    FROM users
    WHERE username = :username
    LIMIT 1
");

$stmt_user->execute([
    ':username' => $username_connecte
]);

$user = $stmt_user->fetch(PDO::FETCH_ASSOC);


// Si l'utilisateur n'existe pas

if (!$user) {

    session_destroy();

    header("Location: index.php");
    exit;

}


// ==========================================
// MODIFICATION DU MOT DE PASSE
// ==========================================

if (
    $_SERVER["REQUEST_METHOD"] === "POST"
    && isset($_POST['modifier_mot_de_passe'])
) {

    $ancien_mot_de_passe = $_POST['ancien_mot_de_passe'];
    $nouveau_mot_de_passe = $_POST['nouveau_mot_de_passe'];
    $confirmation_mot_de_passe = $_POST['confirmation_mot_de_passe'];


    // Vérification de l'ancien mot de passe

    if ($ancien_mot_de_passe !== $user['password']) {

        $error_message =
            "L'ancien mot de passe est incorrect.";

    }

    // Vérification de la longueur

    elseif (strlen($nouveau_mot_de_passe) < 4) {

        $error_message =
            "Le nouveau mot de passe doit contenir au moins 4 caractères.";

    }

    // Vérification de la confirmation

    elseif (
        $nouveau_mot_de_passe !== $confirmation_mot_de_passe
    ) {

        $error_message =
            "Les deux nouveaux mots de passe ne correspondent pas.";

    }

    // Nouveau mot de passe identique à l'ancien

    elseif (
        $nouveau_mot_de_passe === $ancien_mot_de_passe
    ) {

        $error_message =
            "Le nouveau mot de passe doit être différent de l'ancien.";

    }

    else {

        // Mise à jour du mot de passe

        $stmt_update = $pdo->prepare("
            UPDATE users
            SET password = :password
            WHERE id = :id
        ");

        try {

            $stmt_update->execute([
                ':password' => $nouveau_mot_de_passe,
                ':id' => $user['id']
            ]);

            $success_message =
                "Votre mot de passe a été modifié avec succès.";

            // Mise à jour de la variable locale

            $user['password'] = $nouveau_mot_de_passe;

        } catch (PDOException $e) {

            $error_message =
                "Erreur lors de la modification du mot de passe : "
                . $e->getMessage();

        }

    }

}

?>

<!DOCTYPE html>

<html lang="fr">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Gestion des utilisateurs - DPI</title>


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
           CONTENU PRINCIPAL
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

            max-width: 1000px;

            width: 100%;

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
           CARTES
        ========================================== */

        .card {

            background-color: #ffffff;

            border-radius: 10px;

            box-shadow:
                0 1px 4px
                rgba(0, 0, 0, 0.06);

            padding: 25px;

            margin-bottom: 20px;

        }


        .card-title {

            font-size: 15px;

            color: #444;

            font-weight: 600;

            margin-bottom: 20px;

        }


        /* ==========================================
           INFORMATIONS UTILISATEUR
        ========================================== */

        .user-info {

            display: grid;

            grid-template-columns: 1fr 1fr;

            gap: 15px;

        }


        .info-box {

            background-color: #f7f7fb;

            border: 1px solid #eeeeee;

            border-radius: 8px;

            padding: 18px;

        }


        .info-label {

            font-size: 12px;

            color: #888;

            margin-bottom: 6px;

            text-transform: uppercase;

            font-weight: 600;

        }


        .info-value {

            font-size: 16px;

            color: #333;

            font-weight: 600;

        }


        .role {

            display: inline-block;

            background-color: #e8eaf6;

            color: #3f51b5;

            padding: 6px 12px;

            border-radius: 20px;

            font-size: 13px;

            font-weight: 600;

        }


        /* ==========================================
           FORMULAIRE
        ========================================== */

        .form-group {

            margin-bottom: 18px;

        }


        .form-group label {

            display: block;

            margin-bottom: 7px;

            font-size: 13px;

            color: #555;

            font-weight: 500;

        }


        .form-group input {

            width: 100%;

            padding: 11px 12px;

            border: 1px solid #ccc;

            border-radius: 6px;

            outline: none;

            font-size: 14px;

            transition: 0.2s;

        }


        .form-group input:focus {

            border-color: #5c6bc0;

            box-shadow:
                0 0 0 3px
                rgba(92, 107, 192, 0.12);

        }


        /* ==========================================
           BOUTON
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


        /* ==========================================
           ALERTES
        ========================================== */

        .alert {

            padding: 12px 15px;

            margin-bottom: 20px;

            border-radius: 6px;

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
           RETOUR
        ========================================== */

        .back-link {

            display: inline-block;

            margin-top: 5px;

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

            .user-info {

                grid-template-columns: 1fr;

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
            class="menu-item active"
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

            Gestion des utilisateurs

        </div>


        <div class="user-profile">

            👨‍⚕️

            <?php

            echo htmlspecialchars(
                $user['username']
            );

            ?>

        </div>

    </div>


    <!-- CONTENU -->

    <div class="content-wrapper">


        <h1>

            Mon compte

        </h1>


        <div class="subtitle">

            Consultez vos informations et modifiez votre mot de passe.

        </div>


        <!-- ==========================================
             MESSAGES
        ========================================== -->

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
             INFORMATIONS UTILISATEUR
        ========================================== -->

        <div class="card">

            <div class="card-title">

                Informations du compte

            </div>


            <div class="user-info">


                <div class="info-box">

                    <div class="info-label">

                        Nom d'utilisateur

                    </div>

                    <div class="info-value">

                        <?php

                        echo htmlspecialchars(
                            $user['username']
                        );

                        ?>

                    </div>

                </div>


                <div class="info-box">

                    <div class="info-label">

                        Rôle

                    </div>

                    <div class="info-value">

                        <span class="role">

                            <?php

                            echo htmlspecialchars(
                                $user['role']
                            );

                            ?>

                        </span>

                    </div>

                </div>


            </div>

        </div>


        <!-- ==========================================
             MODIFICATION MOT DE PASSE
        ========================================== -->

        <div class="card">

            <div class="card-title">

                🔑 Modifier mon mot de passe

            </div>


            <form
                method="POST"
                action=""
            >


                <div class="form-group">

                    <label for="ancien_mot_de_passe">

                        Ancien mot de passe

                    </label>

                    <input
                        type="password"
                        id="ancien_mot_de_passe"
                        name="ancien_mot_de_passe"
                        required
                    >

                </div>


                <div class="form-group">

                    <label for="nouveau_mot_de_passe">

                        Nouveau mot de passe

                    </label>

                    <input
                        type="password"
                        id="nouveau_mot_de_passe"
                        name="nouveau_mot_de_passe"
                        minlength="4"
                        required
                    >

                </div>


                <div class="form-group">

                    <label for="confirmation_mot_de_passe">

                        Confirmer le nouveau mot de passe

                    </label>

                    <input
                        type="password"
                        id="confirmation_mot_de_passe"
                        name="confirmation_mot_de_passe"
                        minlength="4"
                        required
                    >

                </div>


                <button
                    type="submit"
                    name="modifier_mot_de_passe"
                    class="btn-submit"
                >

                    Modifier le mot de passe

                </button>


            </form>

        </div>


        <a
            href="dashboard.php"
            class="back-link"
        >

            ← Retour au dashboard

        </a>


    </div>

</div>


</body>

</html>