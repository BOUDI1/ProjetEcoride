-- 1. On s'assure que la base "ecoride" existe et on l'utilise
CREATE DATABASE IF NOT EXISTS ecoride;
USE ecoride;

-- 2. On nettoie les anciennes tables si elles existent
SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS reservations, covoiturages, voitures, utilisateurs, roles;
SET FOREIGN_KEY_CHECKS = 1;

-- Table des Rôles (MCD)
CREATE TABLE roles (
    id_role INT PRIMARY KEY,
    libelle VARCHAR(50) NOT NULL
);

INSERT INTO roles (id_role, libelle) VALUES 
(1, 'Administrateur'),
(2, 'Passager'),
(3, 'Chauffeur'),
(4, 'Employé');

-- 3. Table des Utilisateurs
CREATE TABLE utilisateurs (
    id_utilisateur INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(50) NOT NULL,
    prenom VARCHAR(50) NOT NULL,
    pseudo VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    mot_de_passe VARCHAR(255) NOT NULL,
    telephone VARCHAR(20) NULL,
    adresse VARCHAR(255) NULL,
    date_naissance DATE NULL,
    credits DECIMAL(10,2) DEFAULT 20.00,
    id_role INT DEFAULT 2, -- FK vers roles
    photo_profil VARCHAR(255) NULL,
    statut_compte ENUM('actif', 'suspendu') DEFAULT 'actif',
    is_suspended TINYINT DEFAULT 0,
    FOREIGN KEY (id_role) REFERENCES roles(id_role)
);

-- 4. Table des Voitures
CREATE TABLE voitures (
    id_voiture INT AUTO_INCREMENT PRIMARY KEY,
    id_utilisateur INT,
    marque VARCHAR(50) NOT NULL,
    modele VARCHAR(50) NOT NULL,
    immatriculation VARCHAR(20) UNIQUE NOT NULL,
    energie ENUM('electrique', 'thermique', 'hybride', 'essence', 'diesel') NOT NULL,
    couleur VARCHAR(30) NULL,
    date_premiere_immat DATE NULL,
    nb_places INT DEFAULT 4,
    pref_fumeur TINYINT DEFAULT 0,
    pref_animaux TINYINT DEFAULT 0,
    pref_custom VARCHAR(255) NULL,
    FOREIGN KEY (id_utilisateur) REFERENCES utilisateurs(id_utilisateur) ON DELETE CASCADE
);

-- 5. Table des Covoiturages
CREATE TABLE covoiturages (
    id_covoiturage INT AUTO_INCREMENT PRIMARY KEY,
    id_chauffeur INT,
    id_voiture INT NULL,
    date_depart DATE NOT NULL,
    heure_depart TIME NOT NULL,
    date_arrivee DATE NULL,
    heure_arrivee TIME NULL,
    lieu_depart VARCHAR(100) NOT NULL,
    lieu_arrivee VARCHAR(100) NOT NULL,
    places_disponibles INT NOT NULL,
    prix_personne DECIMAL(10, 2) NOT NULL,
    statut ENUM('en_cours', 'termine', 'annule', 'valide', 'refuse') DEFAULT 'en_cours',
    FOREIGN KEY (id_chauffeur) REFERENCES utilisateurs(id_utilisateur) ON DELETE CASCADE,
    FOREIGN KEY (id_voiture) REFERENCES voitures(id_voiture) ON DELETE SET NULL
);

-- 6. Table des Réservations
CREATE TABLE reservations (
    id_reservation INT AUTO_INCREMENT PRIMARY KEY,
    id_covoiturage INT,
    id_utilisateur INT,
    nb_places INT DEFAULT 1,
    statut ENUM('confirmé', 'annulé', 'litige') DEFAULT 'confirmé',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_covoiturage) REFERENCES covoiturages(id_covoiturage) ON DELETE CASCADE,
    FOREIGN KEY (id_utilisateur) REFERENCES utilisateurs(id_utilisateur) ON DELETE CASCADE
);

-- 7. Insertion d'un jeu de test
-- Mots de passe cryptés par défaut (par ex: admin123, jean123) pour tester avec le nouveau système
-- Note: les mots de passe sont hashés en BCRYPT pour correspondre à password_hash / password_verify
INSERT INTO utilisateurs (nom, prenom, pseudo, email, mot_de_passe, id_role, credits) 
VALUES ('Admin', 'EcoRide', 'admin', 'admin@ecoride.fr', '$2y$10$KOkIUnululYzCG/g.QsoreQwwE5UopUQsS1pa4PZOGW8YAfaAwwku', 1, 100.00), -- admin123
       ('Employe', 'EcoRide', 'employe', 'employe@ecoride.fr', '$2y$10$CWHMheG3x1BAXdkbtfZyE.yzS7UnjwUDJwoV61bCEkIVY/6lYO8.m', 4, 0.00), -- employe123
       ('Jean', 'Chauffeur', 'jean_driver', 'jean@ecoride.fr', '$2y$10$Jp5R70qIzTG0yZF6r2/MP.AQN2pw2z.1YRAH75gRvYxkKY.iU88lq', 3, 20.00), -- jean123
       ('Alice', 'Passager', 'alice_user', 'alice@ecoride.fr', '$2y$10$Jp5R70qIzTG0yZF6r2/MP.AQN2pw2z.1YRAH75gRvYxkKY.iU88lq', 2, 50.00); -- jean123

INSERT INTO voitures (id_utilisateur, marque, modele, immatriculation, energie, nb_places, pref_fumeur, pref_animaux, pref_custom)
VALUES (2, 'Tesla', 'Model 3', 'ECO-2024', 'electrique', 4, 0, 1, 'Musique douce acceptée');

INSERT INTO covoiturages (id_chauffeur, id_voiture, date_depart, heure_depart, date_arrivee, heure_arrivee, lieu_depart, lieu_arrivee, places_disponibles, prix_personne, statut)
VALUES (2, 1, CURDATE(), '08:00:00', CURDATE(), '12:00:00', 'Paris', 'Lyon', 3, 25.00, 'valide');