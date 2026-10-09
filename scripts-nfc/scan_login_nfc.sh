#!/bin/bash

# Configuration de l'API dédiée à la connexion DPI
API_URL="http://10.0.0.15:8080/DPI/api_login_badge.php"
API_TOKEN="semi"

echo "=================================================="
echo "  Lecteur ACR122U en écoute pour le portail DPI   "
echo "  En attente d'un badge NFC/RFID...               "
echo "=================================================="

while true; do
    # Lance la détection NFC (bloque jusqu'à présentation d'un badge ou timeout)
    output=$(nfc-poll 2>&1)

    # Extraction de l'UID (compatible Mifare Classic, Ultralight, NTAG...)
    raw_uid=$(echo "$output" | grep "UID (NFCID1)" | awk -F: '{print $2}')

    if [ -n "$raw_uid" ]; then
        # Suppression des espaces éventuels dans l'UID
        uid=$(echo "$raw_uid" | tr -d '[:space:]')
        timestamp=$(date -u +"%Y-%m-%dT%H:%M:%SZ")

        echo ""
        echo "[+] Badge détecté ! UID : $uid ($timestamp)"

        # Envoi de l'UID au serveur web en POST JSON
        response=$(curl -s -X POST "$API_URL" \
            -H "Authorization: Bearer $API_TOKEN" \
            -H "Content-Type: application/json" \
            -d "{\"uid\": \"$uid\", \"timestamp\": \"$timestamp\"}")

        echo "[*] Réponse du serveur : $response"

        # Pause de 2 secondes pour éviter les lectures multiples du même badge
        sleep 2
        echo "[*] Prêt pour le prochain scan..."
    fi

    # Pause courte entre deux vérifications
    sleep 0.5
done
