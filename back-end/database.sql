-- Création de la base de données
CREATE DATABASE IF NOT EXISTS ecoride_db;
USE ecoride_db;

-- 1. Table des Utilisateurs (US 7, 8, 13)
CREATE TABLE utilisateurs (
    id_utilisateur INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(50) NOT NULL,
    prenom VARCHAR(50) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    mot_de_passe VARCHAR(255) NOT NULL,
    credits INT DEFAULT 20, -- Les 20 crédits offerts à l'inscription (US 10)
    role ENUM('passager', 'chauffeur', 'employe', 'admin') DEFAULT 'passager',
    photo_profil VARCHAR(255)
);

-- 2. Table des Voitures (US 4 - Aspect Écologique)
CREATE TABLE voitures (
    id_voiture INT AUTO_INCREMENT PRIMARY KEY,
    id_utilisateur INT,
    modele VARCHAR(50),
    immatriculation VARCHAR(20),
    energie ENUM('electrique', 'thermique') NOT NULL, -- Crucial pour le filtre éco
    couleur VARCHAR(30),
    date_premiere_immat DATE,
    FOREIGN KEY (id_utilisateur) REFERENCES utilisateurs(id_utilisateur) ON DELETE CASCADE
);

-- 3. Table des Covoiturages (US 1, 3, 4)
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

-- 4. Table des Réservations (US 9)
CREATE TABLE reservations (
    id_reservation INT AUTO_INCREMENT PRIMARY KEY,
    id_covoiturage INT,
    id_passager INT,
    nb_places INT DEFAULT 1,
    statut_reservation ENUM('valide', 'annule') DEFAULT 'valide',
    FOREIGN KEY (id_covoiturage) REFERENCES covoiturages(id_covoiturage) ON DELETE CASCADE,
    FOREIGN KEY (id_passager) REFERENCES utilisateurs(id_utilisateur) ON DELETE CASCADE
);

-- Insertion d'un jeu d'essai pour tester (Optionnel)
INSERT INTO utilisateurs (nom, prenom, email, mot_de_passe, role) 
VALUES ('Admin', 'Ecoride', 'admin@ecoride.fr', 'hash_password_ici', 'admin');