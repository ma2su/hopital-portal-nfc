#!/bin/bash

# Configuration
API_URL="http://10.0.0.15:8080/acces.php"
API_TOKEN="semi"

echo "=== MODE CONTRÔLE D'ACCÈS ACTIF ==="
echo "Lecteur ACR122U prêt. En attente de badge..."
echo "Appuyez sur Ctrl+C pour quitter."
echo ""

while true; do
    # Lance nfc-poll et capture la sortie
    output=$(nfc-poll 2>&1)
    raw_uid=$(echo "$output" | grep "UID (NFCID1)" | awk -F: '{print $2}')
    
    if [ -n "$raw_uid" ]; then
        uid=$(echo "$raw_uid" | tr -d '[:space:]')
        timestamp=$(date -u +"%Y-%m-%dT%H:%M:%SZ")
        
        echo "[+] Badge détecté ! UID : $uid"
        
        # Envoi de l'UID et du timestamp au serveur PHP
        response=$(curl -s -X POST "$API_URL" \
            -H "Authorization: Bearer $API_TOKEN" \
            -H "Content-Type: application/json" \
            -d "{\"uid\": \"$uid\", \"timestamp\": \"$timestamp\"}")
            
        echo "Réponse serveur : $response"
        echo "----------------------------------------"
        
        # Petite pause pour éviter de relire 50 fois la même carte en une seconde
        sleep 2
    fi
    
    sleep 0.5
done
