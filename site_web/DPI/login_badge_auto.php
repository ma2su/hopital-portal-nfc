<?php
session_start();

// Rediriger directement si déjà connecté
if (isset($_SESSION['user_id'])) {
    if (($_SESSION['role'] ?? '') === 'docteur') {
        header('Location: dashboard.php');
    } else {
        header('Location: espace_patient.php');
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion par Badge — DPI</title>
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
            background: linear-gradient(135deg, #f4f5f7 0%, #eef0ff 50%, #f4f5f7 100%);
            color: #333;
        }

        .login-card {
            background: #ffffff;
            border-radius: 14px;
            padding: 40px 30px;
            width: 100%;
            max-width: 440px;
            text-align: center;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            border: 1px solid #eeeeee;
        }

        .icon-wrapper {
            width: 90px;
            height: 90px;
            margin: 0 auto 20px;
            display: flex;
            justify-content: center;
            align-items: center;
            border-radius: 50%;
            background: linear-gradient(135deg, #5c6bc0, #3f51b5);
            color: white;
            font-size: 44px;
            box-shadow: 0 6px 18px rgba(63, 81, 181, 0.3);
            animation: pulse-ring 2s infinite cubic-bezier(0.4, 0, 0.6, 1);
        }

        @keyframes pulse-ring {
            0%, 100% {
                transform: scale(1);
            }
            50% {
                transform: scale(1.06);
            }
        }

        h2 {
            margin: 0 0 8px;
            font-size: 22px;
            color: #2c3e50;
        }

        p.description {
            margin: 0 0 24px;
            font-size: 14px;
            color: #7f8c8d;
        }

        .status-box {
            padding: 14px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 500;
            background-color: #f8f9fa;
            border: 1px solid #e9ecef;
            color: #495057;
            min-height: 48px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
        }

        .status-box.success {
            background-color: #e8f5e9;
            border-color: #c8e6c9;
            color: #2e7d32;
        }

        .status-box.error {
            background-color: #ffebee;
            border-color: #ffcdd2;
            color: #c62828;
        }

        .classic-link {
            display: inline-block;
            margin-top: 25px;
            color: #5c6bc0;
            text-decoration: none;
            font-size: 13px;
            font-weight: 600;
        }

        .classic-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

    <div class="login-card">
        <div class="icon-wrapper">
            🪪
        </div>

        <h2>Connexion sans contact</h2>
        <p class="description">Présentez votre carte professionnelle ou patient sur le lecteur</p>

        <div id="status" class="status-box">
            En attente de détection du badge...
        </div>

        <a href="login_dpi.php" class="classic-link">
            Connexion par identifiant et mot de passe →
        </a>
    </div>

    <script>
        const statusBox = document.getElementById('status');
        let isRedirecting = false;

        async function pollBadge() {
            if (isRedirecting) return;

            try {
                const response = await fetch('check_scan_login.php');
                const data = await response.json();

                if (data.scanned) {
                    if (data.status === 'success') {
                        isRedirecting = true;
                        statusBox.className = 'status-box success';
                        statusBox.innerHTML = `✓ Bienvenue <b>${data.username}</b> (${data.role}) ! Redirection...`;
                        
                        setTimeout(() => {
                            window.location.href = data.redirect;
                        }, 1000);
                        return;
                    } else if (data.status === 'error') {
                        statusBox.className = 'status-box error';
                        statusBox.textContent = `⚠️ ${data.message}`;

                        setTimeout(() => {
                            if (!isRedirecting) {
                                statusBox.className = 'status-box';
                                statusBox.textContent = 'En attente de détection du badge...';
                            }
                        }, 3000);
                    }
                }
            } catch (err) {
                console.error("Erreur polling :", err);
            }

            // Vérification toutes les 800 ms
            setTimeout(pollBadge, 800);
        }

        // Lancement immédiat de l'écoute
        pollBadge();
    </script>

</body>
</html>
