#!/bin/bash

API_URL="http://10.0.0.15:8080/register_dpi_badge.php"
API_TOKEN="semi"

echo "=== ATTRIBUTION D'UN BADGE AU PORTAIL DPI ==="
read -p "Nom d'utilisateur (username) : " username

if [ -z "$username" ]; then
    echo "[ERREUR] Le nom d'utilisateur est obligatoire."
    exit 1
fi

echo "Sélectionnez le rôle :"
echo "  1) docteur"
echo "  2) patient"
read -p "Choix [1/2] (défaut: 2) : " role_choice

case "$role_choice" in
    1) role="docteur" ;;
    *) role="patient" ;;
esac

echo ""
echo "[*] Rôle sélectionné : $role"
echo "[*] Posez le badge sur le lecteur ACR122U..."

while true; do
    output=$(nfc-poll 2>&1)
    raw_uid=$(echo "$output" | grep "UID (NFCID1)" | awk -F: '{print $2}')

    if [ -n "$raw_uid" ]; then
        uid=$(echo "$raw_uid" | tr -d '[:space:]')
        echo "[+] Badge détecté ! UID : $uid"

        response=$(curl -s -X POST "$API_URL" \
            -H "Authorization: Bearer $API_TOKEN" \
            -H "Content-Type: application/json" \
            -d "{\"username\": \"$username\", \"role\": \"$role\", \"uid\": \"$uid\"}")

        echo "Réponse du serveur : $response"
        break
    fi

    sleep 0.5
done
