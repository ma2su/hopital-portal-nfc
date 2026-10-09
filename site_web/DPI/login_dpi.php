<?php

// Configuration sécurisée des cookies de session avant le démarrage
session_set_cookie_params([
    'lifetime' => 0,
    'path'     => '/',
    'domain'   => '',
    'secure'   => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on',
    'httponly' => true,
    'samesite' => 'Strict',
]);

session_start();

// Génération du token CSRF
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Déconnexion
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    $_SESSION = [];

    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params["path"],
            $params["domain"],
            $params["secure"],
            $params["httponly"]
        );
    }

    session_destroy();
    header('Location: login_dpi.php');
    exit;
}

// Redirection si déjà connecté
if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}

$error = null;

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $submittedToken = $_POST['csrf_token'] ?? '';
    $username       = trim($_POST['username'] ?? '');
    $password       = $_POST['password'] ?? '';

    // Vérification anti-CSRF
    if (!hash_equals($_SESSION['csrf_token'], $submittedToken)) {
        $error = "Session de formulaire invalide. Veuillez rafraîchir la page.";
    } elseif ($username === '' || $password === '') {
        $error = "Veuillez remplir tous les champs.";
    } else {
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

        try {
            $mysqli = new mysqli('db', 'sven', 'sven', 'DPI', 3306);
            $mysqli->set_charset('utf8mb4');

            $stmt = $mysqli->prepare("
                SELECT id, username, password
                FROM users
                WHERE username = ?
                LIMIT 1
            ");
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();
            $user = $result->fetch_assoc();

            $stmt->close();
            $mysqli->close();

            // Hash fictif pour contrer l'énumération d'utilisateurs par analyse de temps (timing attack)
            $dummyHash = '$2y$10$abcdefghijklmnopqrstuuABCDEFGHIJKLMNOPQRSTUVWXYZ012';
            $hashToCheck = $user ? $user['password'] : $dummyHash;

            if ($user && password_verify($password, $hashToCheck)) {
                session_regenerate_id(true);

                $_SESSION['user_id']  = $user['id'];
                $_SESSION['username'] = $user['username'];

                // Renouvellement du jeton CSRF après connexion
                $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

                header('Location: dashboard.php');
                exit;
            }

            $error = "Identifiant ou mot de passe incorrect.";

        } catch (mysqli_sql_exception $e) {
            // Ne pas exposer les détails techniques de l'erreur SQL à l'utilisateur
            $error = "Erreur de connexion au service d'authentification.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - DPI</title>
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 0;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(
                135deg,
                #f4f5f7 0%,
                #eef0ff 50%,
                #f4f5f7 100%
            );
            color: #333;
        }

        .login-container {
            width: 100%;
            max-width: 430px;
            padding: 20px;
        }

        .login-card {
            background: #ffffff;
            border-radius: 14px;
            padding: 40px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            border: 1px solid #eeeeee;
        }

        .logo {
            text-align: center;
            margin-bottom: 30px;
        }

        .logo-icon {
            width: 65px;
            height: 65px;
            margin: 0 auto 15px;
            display: flex;
            justify-content: center;
            align-items: center;
            border-radius: 14px;
            background: linear-gradient(
                135deg,
                #5c6bc0,
                #3f51b5
            );
            color: white;
            font-size: 30px;
            box-shadow: 0 5px 15px rgba(63, 81, 181, 0.25);
        }

        .logo h1 {
            margin: 0;
            font-size: 26px;
            color: #333;
            font-weight: 700;
        }

        .logo p {
            margin-top: 7px;
            margin-bottom: 0;
            color: #888;
            font-size: 14px;
        }

        .login-title {
            margin-bottom: 25px;
        }

        .login-title h2 {
            margin: 0;
            font-size: 20px;
            color: #333;
        }

        .login-title p {
            margin-top: 6px;
            margin-bottom: 0;
            font-size: 13px;
            color: #888;
        }

        .form-group {
            margin-bottom: 18px;
        }

        .form-group label {
            display: block;
            margin-bottom: 7px;
            font-size: 13px;
            font-weight: 600;
            color: #555;
        }

        .input-wrapper {
            position: relative;
        }

        .input-icon {
            position: absolute;
            left: 13px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 16px;
            color: #999;
        }

        .form-group input {
            width: 100%;
            padding: 12px 14px 12px 42px;
            border: 1px solid #d8d8d8;
            border-radius: 8px;
            outline: none;
            font-size: 14px;
            background-color: #fafafa;
            transition: all 0.25s ease;
        }

        .form-group input:focus {
            background-color: #ffffff;
            border-color: #5c6bc0;
            box-shadow: 0 0 0 3px rgba(92, 107, 192, 0.12);
        }

        .form-group input::placeholder {
            color: #aaa;
        }

        .alert {
            padding: 12px 14px;
            margin-bottom: 20px;
            border-radius: 8px;
            font-size: 13px;
        }

        .alert-danger {
            background-color: #fff0f0;
            color: #c62828;
            border: 1px solid #ffcdd2;
        }

        .btn-login {
            width: 100%;
            padding: 13px 20px;
            margin-top: 5px;
            border: none;
            border-radius: 8px;
            background: linear-gradient(
                135deg,
                #5c6bc0,
                #3f51b5
            );
            color: white;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 10px rgba(63, 81, 181, 0.25);
        }

        .btn-login:hover {
            background: linear-gradient(
                135deg,
                #3f51b5,
                #303f9f
            );
            transform: translateY(-2px);
            box-shadow: 0 7px 16px rgba(63, 81, 181, 0.35);
        }

        .btn-login:active {
            transform: translateY(0);
            box-shadow: 0 3px 8px rgba(63, 81, 181, 0.25);
        }

        .login-footer {
            margin-top: 25px;
            padding-top: 20px;
            border-top: 1px solid #eeeeee;
            text-align: center;
            color: #999;
            font-size: 12px;
        }

        @media (max-width: 500px) {
            .login-container {
                padding: 15px;
            }
            .login-card {
                padding: 30px 25px;
            }
        }
    </style>
</head>
<body>

    <div class="login-container">
        <div class="login-card">

            <div class="logo">
                <div class="logo-icon">📁</div>
                <h1>DPI</h1>
                <p>Dossier Patient Informatisé</p>
            </div>

            <div class="login-title">
                <h2>Connexion</h2>
                <p>Connectez-vous à votre espace professionnel</p>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-danger">
                    ⚠️ <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="login_dpi.php">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>">

                <div class="form-group">
                    <label for="username">Identifiant</label>
                    <div class="input-wrapper">
                        <span class="input-icon">👤</span>
                        <input
                            type="text"
                            id="username"
                            name="username"
                            placeholder="Votre identifiant"
                            autocomplete="username"
                            required
                        >
                    </div>
                </div>

                <div class="form-group">
                    <label for="password">Mot de passe</label>
                    <div class="input-wrapper">
                        <span class="input-icon">🔒</span>
                        <input
                            type="password"
                            id="password"
                            name="password"
                            placeholder="Votre mot de passe"
                            autocomplete="current-password"
                            required
                        >
                    </div>
                </div>

                <button type="submit" class="btn-login">
                    Se connecter
                </button>
            </form>

            <div class="login-footer">
                DPI — Espace professionnel
            </div>

        </div>
    </div>

</body>
</html>
