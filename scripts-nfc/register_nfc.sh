#!/bin/bash

# Configuration de votre API d'enregistrement
API_URL="http://10.0.0.15:8080/register.php"
API_TOKEN="semi"

echo "=== ENREGISTREMENT D'UN NOUVEAU BADGE ==="
read -p "Entrez le prénom : " prenom
read -p "Entrez le nom : " nom

if [ -z "$prenom" ] || [ -z "$nom" ]; then
    echo "[ERREUR] Le prénom et le nom sont obligatoires."
    exit 1
fi

echo "Lecteur ACR122U prêt. Veuillez poser le badge sur le lecteur..."

while true; do
    # Lance nfc-poll et capture la sortie
    output=$(nfc-poll 2>&1)
    raw_uid=$(echo "$output" | grep "UID (NFCID1)" | awk -F: '{print $2}')
    
    if [ -n "$raw_uid" ]; then
        uid=$(echo "$raw_uid" | tr -d '[:space:]')
        echo "[+] Badge détecté ! UID : $uid"
        
        # Envoi des données au serveur via curl au format JSON
        response=$(curl -s -X POST "$API_URL" \
            -H "Authorization: Bearer $API_TOKEN" \
            -H "Content-Type: application/json" \
            -d "{\"prenom\": \"$prenom\", \"nom\": \"$nom\", \"uid\": \"$uid\"}")
            
        echo "Réponse du serveur : $response"
        break
    fi
    
    sleep 0.5
done
