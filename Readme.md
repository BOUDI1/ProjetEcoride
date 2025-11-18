# Projet EcoRide 
EcoRide est une plateforme web de covoiturage conçue dans le cadre de l'examen du Titre Professionnel de Développeur Web et Web Mobile (DWWM).

L'objectif principal est de **réduire l'impact environnemental des déplacements** en favorisant le covoiturage. Le site se distingue par une **charte graphique écologique** et un ensemble de fonctionnalités (13 User Stories) couvrant la recherche de trajets, la gestion des comptes (passagers, chauffeurs) et l'administration des données (Employé, Administrateur).

La solution est conçue sur une architecture Full Stack nécessitant l'intégration future de bases de données Relationnelle et Non Relationnelle.
## ✨ Fonctionnalités Clés Implémentées (Front-End)

Le développement actuel se concentre sur les fonctionnalités Front-End critiques :

* **US 1 & 2 :** Interface d'accueil, Navigation complète (Accueil, Covoiturages, Connexion, Contact) et Mentions Légales.
* **US 3 & 4 :** Barre de Recherche d'itinéraires et Filtres de résultats (Prix, Note, **Aspect Écologique**).
* **US 5 :** Affichage de la Vue Détaillée d'un covoiturage.
* **US 7 :** Formulaire de Création de Compte avec exigence de **Mot de Passe Sécurisé**.
* **US 6 :** Gestion de l'interactivité du menu hamburger (Mobile-First) via JavaScript.

---
Domaine | Technologie / Outil | Justification du Choix |
| :--- | :--- | :--- |
| **Structure** | HTML5 | Base de tout développement web. |
| **Style & Design** | CSS3 Natif & Media Queries | Mise en œuvre de la charte graphique et gestion de la **responsivité Mobile-First**. |
| **Interactivité** | JavaScript Natif | Gestion des événements client-side (menu hamburger, validation de formulaire). |
| **Maquettage** | **Figma** | Outil professionnel pour la conception des **Wireframes et Mockups** (3 Desktop, 3 Mobile) fidèles au CSS. |
| **Gestion de Projet** | **Trello** | Utilisation d'un **Kanban partagé** pour la planification et le suivi des 13 US. |
| **Versionning** | Git / GitHub | Traçabilité des modifications et hébergement du code source. |
| **Serveur Local** | XAMPP | Environnement de test pour le Back-End (Apache, PHP, MySQL) et la BDD Relationnelle future. |

---
## ⚙️ Guide d'Installation et Environnement de Travail

Pour contribuer ou tester le projet localement, suivez les étapes de configuration de l'environnement de travail :

### 1. Prérequis et Configuration Logicielle

| Logiciel | Rôle | Installation Requise |
| :--- | :--- | :--- |
| **VS Code** | Éditeur de code (avec extensions Live Server, etc.) | Indispensable pour l'édition et le prévisualisation rapide. |
| **XAMPP** | Environnement de serveur local | Requis pour simuler le Back-End (PHP/MySQL) lors de la Phase II. |
| **Git** | Outil de gestion de version | Configuré avec `user.name BOUDI1` et l'email associé pour la traçabilité.
---
### 2. Démarrage Local du Projet

1.  **Clonage du Dépôt :**
    ```bash
    git clone [https://docs.github.com/fr/repositories/creating-and-managing-repositories/about-repositories](https://docs.github.com/fr/repositories/creating-and-managing-repositories/about-repositories)
    cd ecoride
    ```
2.  **Lancement du Front-End :** Ouvrez le fichier `index.html` dans VS Code et utilisez l'extension **Live Server** pour visualiser le site dans votre navigateur.
3.  **Lancement du Back-End (Futur) :** Démarrez les modules Apache et MySQL via le panneau de contrôle XAMPP.

---
