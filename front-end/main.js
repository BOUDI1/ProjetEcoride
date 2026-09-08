// --- GESTION CENTRALISÉE DE L'APPLICATION ECORIDE ---

function applySessionToDOM() {
    const userData = localStorage.getItem('user');
    const menuConnexion = document.getElementById('menu-connexion');
    const menuDeconnexion = document.getElementById('menu-deconnexion');
    const menuProposer = document.getElementById('menu-proposer');
    const menuAdmin = document.getElementById('menu-admin');
    const menuEmploye = document.getElementById('menu-employe');
    const menuModeration = document.getElementById('menu-moderation');
    const menuEspace = document.getElementById('menu-espace');

    if (userData) {
        try {
            const user = JSON.parse(userData);
            const roleId = parseInt(user.id_role);

            // Masquer connexion et afficher déconnexion
            if (menuConnexion) {
                menuConnexion.classList.add('hidden');
                menuConnexion.style.display = 'none';
            }
            if (menuDeconnexion) {
                menuDeconnexion.classList.remove('hidden');
                menuDeconnexion.style.display = 'block';
            }

            // Adaptation dynamique du lien "Mon Espace" selon le rôle
            if (menuEspace) {
                const linkEspace = menuEspace.querySelector('a');
                if (linkEspace) {
                    if (roleId === 4) { // Employé (US 12)
                        linkEspace.href = "employe.html";
                        linkEspace.innerHTML = '<i class="fas fa-user-check"></i> Espace Employé';
                    } else if (roleId === 1) { // Admin (US 13)
                        linkEspace.href = "admin.html";
                        linkEspace.innerHTML = '<i class="fas fa-user-shield"></i> Administration';
                    } else { // Passager / Chauffeur
                        linkEspace.href = "espace.html";
                        linkEspace.innerHTML = '<i class="fas fa-user"></i> Mon Espace';
                    }
                }
            }

            // Affichage spécifique selon le rôle
            if (roleId === 1) { 
                // 1 = Administrateur (US 13)
                if (menuAdmin) {
                    menuAdmin.classList.remove('hidden');
                    menuAdmin.style.display = 'block';
                }
                if (menuEmploye) {
                    menuEmploye.classList.remove('hidden');
                    menuEmploye.style.display = 'block';
                }
                if (menuModeration) {
                    menuModeration.classList.remove('hidden');
                    menuModeration.style.display = 'block';
                }
            } else if (roleId === 4) { 
                // 4 = Employé (US 12)
                if (menuEmploye) {
                    menuEmploye.classList.remove('hidden');
                    menuEmploye.style.display = 'block';
                }
                if (menuAdmin) {
                    menuAdmin.classList.add('hidden');
                    menuAdmin.style.display = 'none';
                }
                if (menuModeration) {
                    menuModeration.classList.remove('hidden');
                    menuModeration.style.display = 'block';
                }
            } else if (roleId === 3) { 
                // 3 = Chauffeur
                if (menuProposer) {
                    menuProposer.classList.remove('hidden');
                    menuProposer.style.display = 'block';
                }
            }
        } catch (e) {
            console.error("Erreur de lecture de la session :", e);
        }
    } else {
        // Non connecté
        if (menuConnexion) {
            menuConnexion.classList.remove('hidden');
            menuConnexion.style.display = 'block';
        }
        if (menuDeconnexion) {
            menuDeconnexion.classList.add('hidden');
            menuDeconnexion.style.display = 'none';
        }
        if (menuAdmin) {
            menuAdmin.classList.add('hidden');
            menuAdmin.style.display = 'none';
        }
        if (menuEmploye) {
            menuEmploye.classList.add('hidden');
            menuEmploye.style.display = 'none';
        }
        if (menuProposer) {
            menuProposer.classList.add('hidden');
            menuProposer.style.display = 'none';
        }
    }
}

function initApp() {
    // 1. Appliquer les droits et la navbar
    applySessionToDOM();

    // 2. Bouton Déconnexion
    const btnLogout = document.getElementById('btn-logout');
    if (btnLogout) {
        btnLogout.onclick = (e) => {
            e.preventDefault();
            if (confirm("Voulez-vous vraiment vous déconnecter ?")) {
                localStorage.removeItem('user');
                window.location.href = 'index.html';
            }
        };
    }

    // 3. Menu Mobile Hamburger
    const menuToggle = document.getElementById('menu-toggle');
    const navList = document.getElementById('nav-list');
    if (menuToggle && navList) {
        menuToggle.onclick = () => {
            navList.classList.toggle('is-open');
        };
    }

    // 4. Formulaire de recherche d'itinéraire
    const searchForm = document.getElementById('search-form');
    if (searchForm) {
        searchForm.onsubmit = (e) => {
            e.preventDefault();
            const dep = document.getElementById('depart').value;
            const arr = document.getElementById('arrivee').value;
            window.location.href = `covoiturages.html?depart=${encodeURIComponent(dep)}&arrivee=${encodeURIComponent(arr)}`;
        };
    }

    // 5. Statistiques écologiques globales
    loadStats();
}

// Chargement des statistiques
async function loadStats() {
    const trajetEl = document.getElementById('nb-trajets');
    const co2El = document.getElementById('co2-save');
    const ctx = document.getElementById('myChart');

    if (!trajetEl && !ctx) return;

    try {
        const response = await fetch('../back-end/api/get_stats.php');
        const result = await response.json();
        
        if (result.status === 'success') {
            const data = result.data;
            if (trajetEl) trajetEl.innerText = data.nb_trajets;
            if (co2El) co2El.innerText = data.co2_economise + " kg";

            if (ctx && typeof Chart !== 'undefined') {
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
                    options: { responsive: true, plugins: { legend: { display: false } } }
                });
            }
        }
    } catch (err) {
        console.error("Erreur chargement statistiques :", err);
    }
}

// Exécution immédiate ou à l'événement DOMContentLoaded
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initApp);
} else {
    initApp();
}