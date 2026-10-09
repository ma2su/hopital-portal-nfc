<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Hôpital — Portail</title>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            min-height: 100vh;
            background: linear-gradient(135deg, #eef2ff, #f8fafc);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #1e293b;
        }

        .container {
            width: 100%;
            max-width: 1200px;
            padding: 40px 25px;
            text-align: center;
        }

        .logo {
            width: 80px;
            height: 80px;
            margin: 0 auto 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #6366f1, #4f46e5);
            border-radius: 22px;
            color: white;
            font-size: 40px;
            box-shadow: 0 15px 35px rgba(79, 70, 229, 0.25);
        }

        h1 {
            font-size: 36px;
            margin-bottom: 10px;
            color: #111827;
        }

        .subtitle {
            font-size: 17px;
            color: #64748b;
            margin-bottom: 50px;
        }

        .applications {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 25px;
        }

        .application {
            background: white;
            border-radius: 20px;
            padding: 40px 28px;
            text-decoration: none;
            color: inherit;
            border: 1px solid #e2e8f0;
            box-shadow: 0 10px 30px rgba(15, 23, 42, 0.08);
            transition: all 0.25s ease;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .application:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 40px rgba(15, 23, 42, 0.15);
            border-color: #c7d2fe;
        }

        .icon {
            width: 85px;
            height: 85px;
            margin: 0 auto 20px;
            border-radius: 22px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 42px;
        }

        .dpi-icon {
            background: #eef2ff;
        }

        .badge-icon {
            background: #ecfdf5;
        }

        .security-icon {
            background: #fef2f2;
        }

        .application h2 {
            font-size: 22px;
            margin-bottom: 12px;
            color: #111827;
        }

        .application p {
            color: #64748b;
            font-size: 15px;
            line-height: 1.6;
            min-height: 70px;
        }

        .button {
            display: inline-block;
            margin-top: 25px;
            padding: 12px 22px;
            border-radius: 10px;
            color: white;
            font-weight: bold;
            font-size: 14px;
            transition: all 0.2s ease;
        }

        .dpi-button {
            background: linear-gradient(135deg, #6366f1, #4f46e5);
        }

        .badge-button {
            background: linear-gradient(135deg, #10b981, #059669);
        }

        .security-button {
            background: linear-gradient(135deg, #ef4444, #dc2626);
        }

        .application:hover .button {
            transform: scale(1.03);
        }

        .footer {
            margin-top: 45px;
            color: #94a3b8;
            font-size: 13px;
        }

        @media (max-width: 950px) {
            .applications {
                grid-template-columns: 1fr;
            }

            h1 {
                font-size: 30px;
            }

            .subtitle {
                margin-bottom: 30px;
            }

            .application {
                padding: 30px 20px;
            }

            .application p {
                min-height: auto;
            }
        }
    </style>
</head>

<body>

<div class="container">

    <div class="logo">
        🏥
    </div>

    <h1>Portail de l'Hôpital</h1>

    <p class="subtitle">
        Sélectionnez l'application ou le mode de connexion souhaité
    </p>

    <div class="applications">

        <!-- DPI Classique -->
        <a href="DPI/login_dpi.php" class="application">
            <div>
                <div class="icon dpi-icon">
                    🩺
                </div>
                <h2>DPI — Identifiants</h2>
                <p>
                    Connexion manuelle au Dossier Patient Informatisé avec nom d'utilisateur et mot de passe.
                </p>
            </div>
            <span class="button dpi-button">
                Se connecter (MDP) →
            </span>
        </a>

        <!-- DPI par Badge NFC -->
        <a href="DPI/login_badge_auto.php" class="application">
            <div>
                <div class="icon badge-icon">
                    🪪
                </div>
                <h2>Connexion Badge</h2>
                <p>
                    Authentification sans contact via lecteur ACR122U pour médecins et patients.
                </p>
            </div>
            <span class="button badge-button">
                Scanner un badge →
            </span>
        </a>

        <!-- Contrôle d'accès -->
        <a href="control-access/login_securite.php" class="application">
            <div>
                <div class="icon security-icon">
                    🔐
                </div>
                <h2>Contrôle d'accès</h2>
                <p>
                    Gestion des badges, des autorisations matérielles et consultation du journal des passages.
                </p>
            </div>
            <span class="button security-button">
                Accéder à la sécurité →
            </span>
        </a>

    </div>

    <div class="footer">
        Système informatique de l'Hôpital
    </div>

</div>

</body>
</html>
