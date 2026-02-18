document.addEventListener('DOMContentLoaded', () => {
    
    // --- 1. GESTION DE LA SESSION (LocalStorage) ---
    const userData = localStorage.getItem('user');
    const menuConnexion = document.getElementById('menu-connexion');
    const menuDeconnexion = document.getElementById('menu-deconnexion');
    const menuProposer = document.getElementById('menu-proposer');
    const menuModeration = document.getElementById('menu-moderation');

    if (userData) {
        try {
            const user = JSON.parse(userData);
            const roleId = parseInt(user.id_role);
            
            console.log("Session active. Rôle ID :", roleId);

            // Affichage des menus connectés
            if(menuConnexion) menuConnexion.classList.add('hidden');
            if(menuDeconnexion) menuDeconnexion.classList.remove('hidden');

            // Affichage spécifique selon le rôle
            if (roleId === 1) { // ADMINISTRATEUR
                if(menuModeration) menuModeration.classList.remove('hidden');
            } else if (roleId === 3) { // CHAUFFEUR
                if(menuProposer) menuProposer.classList.remove('hidden');
            }
        } catch (e) {
            console.error("Erreur de lecture de la session :", e);
            localStorage.removeItem('user'); // Nettoyage en cas d'erreur
        }
    }

    // --- 2. BOUTON DÉCONNEXION ---
    const btnLogout = document.getElementById('btn-logout');
    if(btnLogout) {
        btnLogout.addEventListener('click', (e) => {
            e.preventDefault();
            if(confirm("Voulez-vous vraiment vous déconnecter ?")) {
                localStorage.removeItem('user');
                window.location.href = 'index.html';
            }
        });
    }

    // --- 3. MENU MOBILE ---
    const menuToggle = document.getElementById('menu-toggle');
    const navList = document.getElementById('nav-list');
    if (menuToggle && navList) {
        menuToggle.addEventListener('click', () => {
            navList.classList.toggle('is-open');
        });
    }

    // --- 4. STATISTIQUES ET GRAPHIQUE ---
    const loadStats = async () => {
        const trajetEl = document.getElementById('nb-trajets');
        const co2El = document.getElementById('co2-save');
        const ctx = document.getElementById('myChart');

        // On ne lance l'API que si on est sur la page d'accueil (présence des IDs)
        if (!trajetEl && !ctx) return;

        try {
            const response = await fetch('../back-end/api/get_stats.php');
            const result = await response.json();
            
            if (result.status === 'success') {
                const data = result.data;
                
                // Mise à jour des chiffres
                if(trajetEl) trajetEl.innerText = data.nb_trajets;
                if(co2El) co2El.innerText = data.co2_economise + " kg";

                // Création du graphique Chart.js
                if (ctx) {
                    new Chart(ctx, {
                        type: 'bar',
                        data: {
                            labels: ['Trajets Validés', 'CO2 Économisé (kg)'],
                            datasets: [{
                                label: 'EcoRide Impact',
                                data: [data.nb_trajets, data.co2_economise],
                                backgroundColor: ['#2ecc71', '#27ae60'],
                                borderRadius: 5
                            }]
                        },
                        options: { 
                            responsive: true, 
                            plugins: { legend: { display: false } }
                        }
                    });
                }
            }
        } catch (err) {
            console.error("Erreur chargement statistiques :", err);
        }
    };

    // --- 5. FORMULAIRE DE RECHERCHE ---
    const searchForm = document.getElementById('search-form');
    if (searchForm) {
        searchForm.addEventListener('submit', (e) => {
            e.preventDefault();
            const dep = document.getElementById('depart').value;
            const arr = document.getElementById('arrivee').value;
            window.location.href = `covoiturages.html?depart=${encodeURIComponent(dep)}&arrivee=${encodeURIComponent(arr)}`;
        });
    }

    loadStats();
});