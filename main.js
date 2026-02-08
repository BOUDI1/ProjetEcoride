/**
 * Projet EcoRide - Script Principal (main.js)
 * Gestion du menu, des animations et des requêtes asynchrones (Fetch API)
 */

document.addEventListener('DOMContentLoaded', () => {

    // --- 1. GESTION DU MENU HAMBURGER (Responsive) ---
    const menuToggle = document.querySelector('.menu-toggle');
    const navList = document.querySelector('.nav-list');
    const breakpoint = 768;

    if (menuToggle && navList) {
        menuToggle.addEventListener('click', () => {
            if (window.innerWidth < breakpoint) {
                navList.classList.toggle('is-open');
                const icon = menuToggle.querySelector('i');
                const isOpen = navList.classList.contains('is-open');
                
                // Changement d'icône (Barres / Croix)
                icon.className = isOpen ? 'fas fa-times' : 'fas fa-bars';
            }
        });

        // Fermeture automatique du menu si on agrandit la fenêtre
        window.addEventListener('resize', () => {
            if (window.innerWidth >= breakpoint && navList.classList.contains('is-open')) {
                navList.classList.remove('is-open');
                menuToggle.querySelector('i').className = 'fas fa-bars';
            }
        });
    }

    // --- 2. LOGIQUE ASYNCHRONE (Fetch API) & GRAPHIQUES ---
    
    /**
     * Récupère les données depuis le serveur (Back-end PHP)
     */
    async function initialiserDonnees() {
        const container = document.getElementById('container-stats');
        
        try {
            console.log("Tentative de fetch vers le serveur PHP...");
            
            // CHANGEMENT MAJEUR : On appelle le script PHP au lieu du JSON statique
            const reponse = await fetch('get_stats.php');
            
            if (!reponse.ok) {
                throw new Error(`Erreur serveur : ${reponse.status}`);
            }

            const donnees = await reponse.json();
            console.log("Données reçues du Back-end :", donnees);

            if (donnees && donnees.statsEcologiques) {
                afficherStats(donnees.statsEcologiques);
                creerGraphique(donnees.statsEcologiques);
            }

        } catch (erreur) {
            console.error("Erreur lors du chargement des données :", erreur);
            if (container) {
                container.innerHTML = `<p style="color:red;">Erreur de connexion au serveur de données.</p>`;
            }
        }
    }

    /**
     * Affiche les chiffres clés dans le DOM
     */
    function afficherStats(stats) {
        const container = document.getElementById('container-stats');
        if (!container) return;

        container.innerHTML = stats.map(s => `
            <div class="stat-card">
                <h3>${s.label}</h3>
                <p class="stat-value">${s.valeur}</p>
            </div>
        `).join('');
    }

    /**
     * Génère le graphique avec Chart.js
     */
    function creerGraphique(stats) {
        const ctx = document.getElementById('myChart');
        if (!ctx) return;

        // Destruction de l'instance précédente si elle existe
        if (window.myChartInstance) {
            window.myChartInstance.destroy();
        }

        window.myChartInstance = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: stats.map(s => s.label),
                datasets: [{
                    label: 'Impact Environnemental EcoRide',
                    data: stats.map(s => s.valeur),
                    backgroundColor: ['#4CAF50', '#81C784', '#A5D6A7'],
                    borderRadius: 5
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: { beginAtZero: true }
                }
            }
        });
    }

    // --- 3. LANCEMENT ---
    initialiserDonnees();
});