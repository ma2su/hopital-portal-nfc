#!/bin/bash

API_URL="http://10.0.0.15:8080/api.php"
API_TOKEN="semi"

echo "Lecteur ACR122U prêt. En attente de badge NFC..."

while true; do
    output=$(nfc-poll 2>&1)
    raw_uid=$(echo "$output" | grep "UID (NFCID1)" | awk -F: '{print $2}')
    
    if [ -n "$raw_uid" ]; then
        uid=$(echo "$raw_uid" | tr -d '[:space:]')
        timestamp=$(date -u +"%Y-%m-%dT%H:%M:%SZ")
        
        echo "[+] Badge détecté ! UID : $uid"
        
        response=$(curl -s -X POST "$API_URL" \
            -H "Authorization: Bearer $API_TOKEN" \
            -H "Content-Type: application/json" \
            -d "{\"uid\": \"$uid\", \"timestamp\": \"$timestamp\"}")
            
        echo "Réponse serveur : $response"
        
        sleep 2
    fi
    sleep 0.5
done
