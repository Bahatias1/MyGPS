# 🚀 MyGPS / SYSLOC - Version Compatible Vercel

Cette version de l'application **MyGPS** (Système de Géolocalisation d'Enfants) a été migrée pour être **100% compatible avec l'hébergement Vercel**.

## 📌 Fonctionnalités & APIs
* **Zero Breaking Change** : Les contrats d'API sont identiques aux scripts PHP d'origine.
* **API Bracelet SOS (`/api/etat`)** : Reçoit `idEnfant` et `etat` en HTTP POST/JSON.
* **API Cartographie (`/api/localiser`)** : Reçoit `id` en GET et alimente la carte Leaflet centrée sur **Goma, RDC**.
* **Mode Démo In-Memory Intégré** : L'application fonctionne immédiatement sur Vercel même avant de configurer la base de données cloud !

---

## 🛠️ Déploiement en 1 minute sur Vercel

### Option 1 : Déploiement direct via Vercel CLI
Ouvrez votre terminal dans le dossier `MyGPS-Vercel` et exécutez :
```bash
npx vercel
```
Suivez les instructions en appuyant sur Entrée. Votre site sera immédiatement en ligne sur une URL HTTPS du type `https://mygps-vercel.vercel.app`.

### Option 2 : Déploiement via GitHub
1. Créez un dépôt GitHub et déposez-y le contenu de ce dossier `MyGPS-Vercel`.
2. Connectez votre compte GitHub sur [vercel.com](https://vercel.com).
3. Cliquez sur **New Project** -> Sélectionnez votre dépôt -> Cliquez sur **Deploy**.

---

## 🗄️ Connexion à une base de données MySQL Cloud (Optionnel)

Par défaut, l'application utilise un jeu de données de démonstration en mémoire. 
Pour connecter votre vraie base MySQL (ex: Supabase, Aiven, PlanetScale) :
1. Dans le tableau de bord Vercel, allez dans **Settings -> Environment Variables**.
2. Ajoutez les variables suivantes :
   * `DB_HOST` : hôte de votre MySQL Cloud
   * `DB_USER` : nom d'utilisateur MySQL
   * `DB_PASSWORD` : mot de passe MySQL
   * `DB_NAME` : `localisation`
   * `DB_PORT` : `3306` (ou port fourni)
