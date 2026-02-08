# 🌿 Projet EcoRide 

EcoRide est une plateforme web de covoiturage conçue dans le cadre de l'examen du Titre Professionnel de Développeur Web et Web Mobile (DWWM).

L'objectif principal est de **réduire l'impact environnemental des déplacements** en favorisant le covoiturage. Le site se distingue par une charte graphique écologique et un ensemble de fonctionnalités couvrant la recherche de trajets, la gestion des comptes et l'administration des données.

## ✨ Fonctionnalités Clés Implémentées
Le développement intègre les fonctionnalités critiques du cahier des charges :

* **US 1 & 2 :** Interface d'accueil et navigation complète (Accueil, Covoiturages, Connexion, Contact).
* **US 3 & 4 :** Barre de recherche d'itinéraires et filtres de résultats (Prix, Note, **Aspect Écologique**).
* **US 6 :** Gestion de l'interactivité du menu hamburger (Mobile-First) via JavaScript.
* **US 13 (Dynamisation) :** Affichage asynchrone des statistiques d'impact écologique via l'**API Fetch** et visualisation graphique par **Chart.js**.
* **Architecture Multi-BDD :** Utilisation conjointe de **MySQL** (données relationnelles pour les trajets/crédits) et **MongoDB** (données non-relationnelles pour les avis et logs).

---

## 🛠 Environnement Technique & Technologies

| Domaine | Technologie / Outil | Justification du Choix |
| :--- | :--- | :--- |
| **Serveur Local** | **Docker** | Isolation des services (PHP, MySQL, MongoDB) et portabilité totale (Remplace XAMPP). |
| **Front-End** | **HTML5 / CSS3 / JS** | Utilisation de Flexbox (Sticky Footer) et de l'API Fetch pour l'asynchronisme. |
| **Back-End** | **PHP 8.2 (PDO)** | Langage serveur robuste avec requêtes préparées pour la sécurité SQL. |
| **Base de Données** | **MySQL & MongoDB** | Système hybride SQL et NoSQL selon les exigences de l'énoncé. |
| **Graphisme** | **Figma** | Réalisation de 12 designs (Wireframes & Mockups) en version Desktop et Mobile. |
| **Gestion de Projet** | **Trello & Git** | Méthode Agile (Kanban) et stratégie de branches GitHub Flow. |

---

## ⚙️ Installation et Lancement (Docker)

Le projet est entièrement conteneurisé pour garantir un environnement de développement identique à la production :

1.  **Cloner le dépôt :**
    ```bash
    git clone [https://github.com/BOUDI1/ProjetEcoride.git](https://github.com/BOUDI1/ProjetEcoride.git)
    cd ProjetEcoride
    ```

2.  **Lancer les conteneurs :**
    ```bash
    docker-compose up -d --build
    ```

3.  **Accéder à l'application :**
    Ouvrez votre navigateur sur [http://localhost:8080](http://localhost:8080).

---

## 🔒 Sécurité et Audit
La sécurité est intégrée dès la conception de l'application EcoRide :

* **Sécurité SQL :** Utilisation systématique de **PHP PDO avec requêtes préparées** pour neutraliser les injections SQL.
* **Protection XSS :** Échappement des données via `htmlspecialchars()` avant affichage dans le DOM.
* **Audit Actif :** Veille effectuée selon le guide **OWASP Testing Guide** et scans de vulnérabilités via **HostedScan**.
* **Mots de Passe :** Hachage sécurisé (Argon2id/bcrypt) pour la protection des comptes utilisateurs.

---

## 🌐 Déploiement et Accès
L'application est déployée en ligne pour permettre une évaluation en conditions réelles :

* **Hébergeur :** alwaysdata
* **URL de Déploiement :** [Ecoridefrance.alwaysdata.net](https://ecoridefrance.alwaysdata.net)
* **Sécurité Déploiement :** Certificat **SSL/TLS** activé avec forçage du **HTTPS** via le fichier `.htaccess`.

---

## 👥 Auteur
**Abdallah EL ASSAAD** - Étudiant Développeur Web et Web Mobile