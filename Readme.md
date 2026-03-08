EcoRide est une application web Full Stack conçue pour faciliter le covoiturage tout en minimisant l'impact écologique. Ce projet a été développé pour répondre aux besoins de José, fondateur d'EcoRide, en mettant l'accent sur la transparence (avis), la sécurité et le suivi de l'économie de CO2.

🛠️ Architecture Technique & Stack
Le projet suit une architecture découplée avec une séparation nette entre le client et le serveur.

Front-End : - HTML5 / CSS3 (Design responsive sans framework pour une performance optimale).

JavaScript (ES6+) : Utilisation intensive de l'API Fetch pour les appels asynchrones.

Chart.js : Visualisation des données écologiques.

Back-End : - PHP 8.2 : Architecture orientée API pour le traitement des requêtes.

Bases de Données :

MariaDB (SQL) : Gestion relationnelle des trajets, utilisateurs et réservations.

MongoDB (NoSQL) : Stockage des logs techniques et audit de performance.

Infrastructure :

Docker : Orchestration complète via docker-compose.

Serveur : Apache (inclus dans le Dockerfile).

📂 Structure du Dépôt
L'analyse du dépôt montre une organisation modulaire :

Plaintext
├── back-end/
│   ├── api/            # Endpoints (connexion, recherche, stats, avis, etc.)
│   ├── config/         # Fichiers de connexion DB (SQL & NoSQL)
│   └── database.sql    # Schéma d'initialisation MariaDB
├── front-end/
│   ├── main.js         # Moteur de l'application (Fetch API, DOM manipulation)
│   ├── style.css       # Design System (Variables CSS, Flexbox)
│   └── *.html          # Les 8 vues de l'application (index, connexion, etc.)
├── assets/             # Documentation complète (Maquettes Figma, Diagrammes)
├── images/             # Ressources graphiques
├── .env                # Variables d'environnement (non versionné en production)
├── Dockerfile          # Image personnalisée PHP/Apache
└── docker-compose.yml  # Orchestration des services
🚀 Installation et Utilisation (Docker)
Pré-requis
Docker Desktop installé.

Lancement
Clonez le dépôt :

Bash
git clone https://github.com/BOUDI1/ProjetEcoride.git
Lancez l'environnement :

Bash
docker-compose up -d --build
Accédez à l'application : http://localhost:8080

🛡️ Focus Sécurité (Veille OWASP)
Le projet intègre des mesures de sécurité strictes identifiées lors de la phase de veille :

Authentification : Hachage des mots de passe avec BCRYPT (password_hash).

Injections SQL : Utilisation systématique de requêtes préparées via PDO.

Sécurité API : Validation des données entrantes côté PHP et encodage côté JS (encodeURIComponent).

Infrastructure : Utilisation d'un fichier .htaccess pour sécuriser les accès et forcer le HTTPS en production.

🌐 Déploiement
L'application est configurée pour un déploiement hybride :

Développement : Environnement conteneurisé Docker.

Production : Hébergement Alwaysdata (URL : https://ecoridefrance.alwaysdata.net/).

Staging : Stratégie de mise en ligne via le répertoire /v2 pour tests avant production.

👨‍💻 Développeur
Abdallah EL ASSAAD Projet réalisé dans le cadre du Titre Professionnel Développeur Web et Web Mobile (Studi).