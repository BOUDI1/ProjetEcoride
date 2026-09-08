EcoRide — Plateforme de covoiturage écologique
Dépôt GitHub officiel : https://github.com/BOUDI1/ProjetEcoride

EcoRide est une application web Full Stack conçue pour faciliter le covoiturage tout en minimisant l'impact écologique. Ce projet a été développé pour répondre aux besoins de José, fondateur d'EcoRide, en mettant l'accent sur la transparence (avis modérés), la sécurité des transactions et le suivi de l'économie de CO₂.

🛠️ Architecture technique et stack
Le projet adopte une architecture découplée avec une séparation nette entre le client et le serveur (API REST).

Front-End :

HTML5 / CSS3 : conception responsive sans framework lourd pour une performance optimale et un temps de chargement réduit.

JavaScript (ES6+) : utilisation intensive de l'API Fetch pour les communications asynchrones.

Chart.js : visualisation dynamique des statistiques et des bilans d'émissions de CO₂ évitées.

Back-End :

PHP 8.2 : architecture orientée API pour le traitement métier et le contrôle d'accès.

PDO (PHP Data Objects) : requêtes préparées systématiques et pilotage des transactions ACID avec verrous pessimistes (FOR UPDATE).

Bases de données (persistance hybride) :

MariaDB (SQL) : gestion relationnelle et intégrité référentielle des utilisateurs, véhicules, trajets et réservations.

MongoDB (NoSQL) : stockage orienté documents pour les journaux d'audit technique, l'historique des actions et le suivi des incidents.

Tests et qualité d'API :

Postman : suite de tests d'intégration pour valider chaque point d'accès de manière autonome, simuler les cas limites (sur-réservation, insolvabilité, auto-réservation) et contrôler les codes de statut HTTP (200, 400, 402, 409).

Infrastructure et déploiement :

Docker et Docker Compose : conteneurisation complète de l'environnement de développement (Apache, PHP 8.2, MariaDB, MongoDB).

Production : hébergement sur Alwaysdata avec déploiement continu via pipeline GitHub Actions.

📂 Structure du dépôt
Plaintext
├── back-end/
│   ├── api/             # Points d'accès REST (connexion, recherche, reserver, avis, etc.)
│   ├── config/          # Connexions aux bases de données (db_sql.php, db_nosql.php)
│   └── database.sql     # Schéma relationnel et données initiales MariaDB
├── front-end/
│   ├── main.js          # Moteur applicatif client (Fetch, manipulation du DOM)
│   ├── style.css        # Système de design (variables CSS, Flexbox, Grid)
│   └── *.html           # Vues de l'application (index, connexion, espace, etc.)
├── assets/              # Livrables de conception (maquettes Figma, diagrammes UML)
├── images/              # Ressources graphiques
├── .env.example         # Gabarit des variables d'environnement (le fichier .env réel est ignoré)
├── .gitignore           # Exclusion des secrets et des volumes de données locaux
├── Dockerfile           # Image conteneurisée personnalisée PHP 8.2 / Apache
└── docker-compose.yml   # Orchestration multi-conteneurs des services
🚀 Installation et démarrage local (Docker)
Prérequis : Docker Desktop installé et actif, Git.

Cloner le dépôt officiel :

Bash
git clone https://github.com/BOUDI1/ProjetEcoride.git
cd ProjetEcoride
Créer le fichier de configuration locale à partir du modèle :

Bash
cp .env.example .env
Construire et démarrer les conteneurs :

Bash
docker-compose up -d --build
Accéder à l'application : http://localhost:8080

🛡️ Sécurité et conformité OWASP
Le projet intègre un ensemble de contre-mesures techniques alignées sur le standard OWASP Top 10 :

A01:2021 — Contrôle d'accès défaillant : vérification systématique des rôles (visiteur, passager, chauffeur, employé, administrateur) et des sessions côté serveur avant l'exécution de toute action sensible.

A02:2021 — Défaillances cryptographiques : hachage des mots de passe utilisateurs à l'aide de l'algorithme BCRYPT (password_hash / password_verify).

A03:2021 — Injection : neutralisation intégrale des injections SQL grâce à l'usage exclusif de requêtes préparées paramétrées via PDO.

A04:2021 — Conception non sécurisée : gestion atomique des réservations sous transactions SQL strictes (beginTransaction, commit, rollBack) pour empêcher toute incohérence de solde ou dépassement de capacité.

Validation et recette via Postman : test unitaire des flux HTTP par envoi direct de charges utiles JSON malformées, garantissant la résilience du back-end indépendamment de l'interface graphique.

Protection de l'infrastructure : chiffrement forcé via HTTPS, configuration des en-têtes de sécurité dans .htaccess et exclusion stricte des secrets d'authentification (.env non versionné).

🌐 Environnements
Dépôt Git : https://github.com/BOUDI1/ProjetEcoride

Développement local : conteneurs Docker (http://localhost:8080)

Production hébergée : https://ecoridefrance.alwaysdata.net/

👨‍💻 Auteur
Abdallah EL ASSAAD

Projet présenté pour le Titre Professionnel Développeur Web et Web Mobile (DWWM).