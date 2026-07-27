#include <TinyGPSPlus.h>
#include <SoftwareSerial.h>
#include <ESP8266WiFi.h>
#include <ESP8266HTTPClient.h>
#include <WiFiClientSecure.h>
#include <ArduinoJson.h>

// === CONFIGURATION WiFi ===
const char *ssid     = "VirungAI";
const char *password = "123456789000";

// === SUPABASE REST API ===
// Le bracelet envoie directement à Supabase sans passer par Vercel
const char* SUPABASE_HOST = "qrsukiatatwsyitcuuql.supabase.co";
const char* SUPABASE_KEY  = "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6InFyc3VraWF0YXR3c3lpdGN1dXFsIiwicm9sZSI6InNlcnZpY2Vfcm9sZSIsImlhdCI6MTc4NTEyNjE1MCwiZXhwIjoyMTAwNzAyMTUwfQ.BQe2omWFo6mcGEZ71CGvyuu7FzfP3cpvInSNgbl3z5w";

// GPS Software Serial sur D2 (RX) et D3 (TX)
static const int RXPin = D2, TXPin = D3;
static const uint32_t GPSBaud = 9600;

TinyGPSPlus gps;
SoftwareSerial ss(D2, D3);

// === VARIABLES GLOBALES ===
String latitude;
String longitude;
String etatalerte;
int nombreappuie = 0;
int idEnfantActif = -1;  // -1 = non encore récupéré depuis Supabase

// === SETUP ===
void setup() {
  pinMode(D5, INPUT);   // Bouton SOS
  pinMode(D0, OUTPUT);  // LED Rouge (Alerte)
  pinMode(D1, OUTPUT);  // LED Verte (Succès envoi)

  Serial.begin(9600);
  ss.begin(GPSBaud);

  Serial.println(F("=== CS MAMA MULZEI - Bracelet GPS ESP8266 ==="));
  Serial.println(F("Initialisation..."));

  connectToWiFi();

  // Récupérer dynamiquement le dernier élève inscrit depuis Supabase
  idEnfantActif = getDernierEnfantId();

  if (idEnfantActif > 0) {
    Serial.print(F("Bracelet assigné à l'élève ID : "));
    Serial.println(idEnfantActif);
  } else {
    Serial.println(F("AVERTISSEMENT: Aucun élève trouvé - utilisation ID=1 par défaut"));
    idEnfantActif = 1;
  }
}

// === LOOP PRINCIPAL ===
void loop() {
  // Alimentation du GPS
  smartDelay(1000);

  // Lecture de la position GPS
  latitude  = String(gps.location.lat(), 6);
  longitude = String(gps.location.lng(), 6);

  Serial.print(F("GPS -> Lat: "));
  Serial.print(latitude);
  Serial.print(F(" | Lng: "));
  Serial.println(longitude);

  // Gestion du bouton SOS (D5)
  if (digitalRead(D5) == LOW) {
    delay(400);
    nombreappuie++;
  }

  if (nombreappuie == 0) {
    etatalerte = "0";
    digitalWrite(D0, LOW);
  } else if (nombreappuie == 1) {
    etatalerte = "1";
    digitalWrite(D0, HIGH);
    Serial.println(F("!!! BOUTON SOS PRESSÉ !!!"));
  } else if (nombreappuie >= 2) {
    nombreappuie = 0;
    digitalWrite(D0, LOW);
    Serial.println(F("SOS désactivé."));
  }

  // Vérification connexion WiFi
  if (!WiFi.isConnected()) {
    Serial.println(F("WiFi perdu - reconnexion..."));
    connectToWiFi();
  }

  // Si pas encore d'ID élève valide, réessayer
  if (idEnfantActif <= 0) {
    idEnfantActif = getDernierEnfantId();
    if (idEnfantActif <= 0) idEnfantActif = 1;
  }

  // Envoi de la position vers Supabase
  envoyerPosition();

  if (millis() > 5000 && gps.charsProcessed() < 10) {
    Serial.println(F("AVERTISSEMENT: Aucune donnée GPS - vérifiez le câblage du module GPS"));
  }
}

// === RÉCUPÉRATION DYNAMIQUE DU DERNIER ÉLÈVE INSCRIT ===
int getDernierEnfantId() {
  WiFiClientSecure client;
  HTTPClient http;
  client.setInsecure();
  client.setBufferSizes(1024, 1024);

  // Requête Supabase : SELECT * FROM enfant ORDER BY idenfant DESC LIMIT 1
  String url = "/rest/v1/enfant?select=idenfant&order=idenfant.desc&limit=1";

  http.setTimeout(10000);
  http.begin(client, SUPABASE_HOST, 443, url);
  http.addHeader("apikey",         SUPABASE_KEY);
  http.addHeader("Authorization",  String("Bearer ") + SUPABASE_KEY);
  http.addHeader("Content-Type",   "application/json");

  int httpCode = http.GET();
  Serial.print(F("Récupération dernier élève - HTTP: "));
  Serial.println(httpCode);

  if (httpCode == 200) {
    String payload = http.getString();
    Serial.print(F("Réponse Supabase: "));
    Serial.println(payload);
    http.end();

    // Parsage JSON simple : [{"idenfant":3}]
    // On cherche le premier nombre après "idenfant":
    int colonIdx = payload.indexOf("\"idenfant\":");
    if (colonIdx != -1) {
      int start = colonIdx + 11; // sauter "idenfant":
      int end   = start;
      while (end < payload.length() && (isDigit(payload[end]) || payload[end] == '-')) end++;
      String idStr = payload.substring(start, end);
      idStr.trim();
      int id = idStr.toInt();
      if (id > 0) {
        Serial.print(F("Dernier élève inscrit ID: "));
        Serial.println(id);
        return id;
      }
    }
  }
  http.end();
  return -1;
}

// === ENVOI POSITION GPS VERS SUPABASE ===
void envoyerPosition() {
  WiFiClientSecure client;
  HTTPClient http;
  client.setInsecure();
  client.setBufferSizes(1024, 1024);

  // Payload JSON pour Supabase REST API
  String jsonPayload = "{\"idenfant\":" + String(idEnfantActif) +
                       ",\"latitude\":"  + latitude +
                       ",\"longitude\":" + longitude +
                       ",\"etat\":"      + etatalerte + "}";

  Serial.print(F("Envoi Supabase: "));
  Serial.println(jsonPayload);

  http.setTimeout(15000);
  http.begin(client, SUPABASE_HOST, 443, "/rest/v1/position");
  http.addHeader("Content-Type",  "application/json");
  http.addHeader("apikey",        SUPABASE_KEY);
  http.addHeader("Authorization", String("Bearer ") + SUPABASE_KEY);
  http.addHeader("Prefer",        "resolution=merge-duplicates");

  int httpCode = http.POST(jsonPayload);
  Serial.print(F("HTTP Code: "));
  Serial.println(httpCode);

  // 201 Created, 200 OK, 409 Conflict/Merge = Succès
  if (httpCode == 201 || httpCode == 200 || httpCode == 409) {
    Serial.println(F("✓ Position envoyée avec succès !"));
    digitalWrite(D1, HIGH);
    delay(300);
    digitalWrite(D1, LOW);
  } else {
    Serial.print(F("✗ Échec envoi. Code HTTP: "));
    Serial.println(httpCode);
    // Clignotement LED rouge pour erreur
    for (int i = 0; i < 3; i++) {
      digitalWrite(D0, HIGH); delay(150);
      digitalWrite(D0, LOW);  delay(150);
    }
  }
  http.end();
}

// === SMART DELAY - Alimente le GPS pendant l'attente ===
static void smartDelay(unsigned long ms) {
  unsigned long start = millis();
  do {
    while (ss.available())
      gps.encode(ss.read());
  } while (millis() - start < ms);
}

// === CONNEXION WIFI ===
void connectToWiFi() {
  WiFi.mode(WIFI_OFF);
  delay(500);
  WiFi.mode(WIFI_STA);
  Serial.print(F("Connexion WiFi au réseau : "));
  Serial.println(ssid);
  WiFi.begin(ssid, password);

  int attempts = 0;
  while (WiFi.status() != WL_CONNECTED && attempts < 30) {
    digitalWrite(D0, HIGH); digitalWrite(D1, HIGH);
    delay(300);
    digitalWrite(D0, LOW);  digitalWrite(D1, LOW);
    delay(300);
    Serial.print(".");
    attempts++;
  }

  if (WiFi.isConnected()) {
    Serial.println();
    Serial.println(F("✓ WiFi Connecté !"));
    Serial.print(F("Adresse IP : "));
    Serial.println(WiFi.localIP());
    // Signal WiFi connecté = clignoter LED verte 3x
    for (int i = 0; i < 3; i++) {
      digitalWrite(D1, HIGH); delay(200);
      digitalWrite(D1, LOW);  delay(200);
    }
  } else {
    Serial.println();
    Serial.println(F("✗ Échec connexion WiFi après 30 tentatives"));
    // Signal erreur = LED rouge fixe 2s
    digitalWrite(D0, HIGH);
    delay(2000);
    digitalWrite(D0, LOW);
  }
}
