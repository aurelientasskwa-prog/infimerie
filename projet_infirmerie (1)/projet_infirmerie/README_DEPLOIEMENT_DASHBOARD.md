# Déploiement – Dashboard Infirmerie + Rapport PDF (ISST)

Ce dossier contient un **dashboard** connecté à la base MySQL `infirmerie_scolaire` et un module de **génération de rapport PDF** (format administration) via **mPDF**.

## 1) Prérequis
- PHP 8.x (recommandé)
- MySQL / MariaDB
- Composer (obligatoire pour installer mPDF)

## 2) Base de données
1. Créer la base (si nécessaire) et importer le dump SQL `infirmerie_scolaire.sql`.
2. **Attention (important)** : si l'import échoue à cause de `dateNaiss` (NOT NULL) alors que les inserts mettent `NULL`, applique ce correctif :

```sql
ALTER TABLE etudiant MODIFY dateNaiss DATE NULL;
```

## 3) Configuration de la connexion
Le fichier `config.php` contient la connexion :

- host: `localhost`
- db: `infirmerie_scolaire`
- user: `root`
- password: *(vide)*

Adapte si besoin selon ton environnement.

## 4) Installation des dépendances (mPDF)
Dans ce dossier, exécuter :

```bash
composer install
```

Cela crée le dossier `vendor/` (NE PAS le commit si possible).

## 5) Ajouter le logo
Ajoute le logo ISST en local **dans le même dossier** et nomme-le exactement :

- `logo_isst.png`

Ce fichier est nécessaire pour que le PDF s'affiche correctement.

## 6) Lancer en local
Dans ce dossier, lancer :

```bash
php -S localhost:8000
```

## 7) Accès aux pages
- Dashboard :
  - http://localhost:8000/dashboard.php

- Rapport PDF (mois en cours) :
  - http://localhost:8000/rapport_general.php

- Rapport PDF avec période :
  - http://localhost:8000/rapport_general.php?from=2025-11-01&to=2025-11-30

Depuis le dashboard, le bouton **Rapport PDF** ouvre automatiquement le rapport avec la période sélectionnée.

## 8) Fonctionnement (résumé)
Le dashboard affiche :
- total des étudiants (`etudiant`)
- consultations aujourd'hui (`consultation.dateConsultation = CURDATE()`)
- consultations sur la période (du/au)
- consultations **à traiter** (traitement vide/NULL)
- alerte stock (médicaments avec `stockDisponible <= 10`)
- les 10 dernières consultations (jointure consultation + étudiant + infirmière)

Le PDF (`rapport_general.php`) génère :
- un entête institutionnel ISST (logo + contacts)
- des statistiques (KPI)
- top 5 motifs
- un tableau détaillé des consultations (limité à 200 pour éviter un PDF trop lourd)
- zones de signature (Infirmier(e) / Visa administration)

## 9) Dépannage rapide
### A) Erreur : `vendor/autoload.php` introuvable
Tu n'as pas exécuté `composer install` dans le bon dossier.

### B) Erreur : logo introuvable
Ajoute le fichier `logo_isst.png` au même niveau que `rapport_general.php`.

### C) Erreurs SQL
- Vérifie que la base s'appelle bien `infirmerie_scolaire`
- Vérifie que les tables existent (import du dump OK)
- Applique le correctif `dateNaiss` si besoin
