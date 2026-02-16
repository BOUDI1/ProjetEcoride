-- 1. On s'assure que la base "ecoride" existe et on l'utilise
CREATE DATABASE IF NOT EXISTS ecoride;
USE ecoride;

-- 2. On nettoie les anciennes tables si elles existent
SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS reservations, covoiturages, voitures, utilisateurs;
SET FOREIGN_KEY_CHECKS = 1;

-- 3. Table des Utilisateurs
CREATE TABLE utilisateurs (
    id_utilisateur INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(50) NOT NULL,
    prenom VARCHAR(50) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    mot_de_passe VARCHAR(255) NOT NULL,
    credits INT DEFAULT 20,
    role ENUM('passager', 'chauffeur', 'employe', 'admin') DEFAULT 'passager',
    photo_profil VARCHAR(255)
);

-- 4. Table des Voitures
CREATE TABLE voitures (
    id_voiture INT AUTO_INCREMENT PRIMARY KEY,
    id_utilisateur INT,
    modele VARCHAR(50),
    immatriculation VARCHAR(20),
    energie ENUM('electrique', 'thermique') NOT NULL,
    couleur VARCHAR(30),
    date_premiere_immat DATE,
    FOREIGN KEY (id_utilisateur) REFERENCES utilisateurs(id_utilisateur) ON DELETE CASCADE
);

-- 5. Table des Covoiturages
CREATE TABLE covoiturages (
    id_covoiturage INT AUTO_INCREMENT PRIMARY KEY,
    id_chauffeur INT,
    date_depart DATE NOT NULL,
    heure_depart TIME NOT NULL,
    lieu_depart VARCHAR(100) NOT NULL,
    lieu_arrivee VARCHAR(100) NOT NULL,
    places_disponibles INT NOT NULL,
    prix_personne DECIMAL(10, 2) NOT NULL,
    statut ENUM('en_cours', 'termine', 'annule') DEFAULT 'en_cours',
    FOREIGN KEY (id_chauffeur) REFERENCES utilisateurs(id_utilisateur) ON DELETE CASCADE
);

-- 6. Insertion d'un jeu de test pour vérifier que les stats s'affichent
INSERT INTO utilisateurs (nom, prenom, email, mot_de_passe, role) 
VALUES ('Admin', 'Ecoride', 'admin@ecoride.fr', 'admin123', 'admin'),
       ('Jean', 'Eco', 'jean@ecoride.fr', 'jean123', 'chauffeur');

INSERT INTO voitures (id_utilisateur, modele, immatriculation, energie)
VALUES (2, 'Tesla Model 3', 'ECO-2024', 'electrique');

INSERT INTO covoiturages (id_chauffeur, date_depart, heure_depart, lieu_depart, lieu_arrivee, places_disponibles, prix_personne)
VALUES (2, CURDATE(), '08:00:00', 'Paris', 'Lyon', 3, 25.00);