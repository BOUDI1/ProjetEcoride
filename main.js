document.addEventListener('DOMContentLoaded', () => {

    // --- 1. GESTION DU MENU HAMBURGER ---
    const menuToggle = document.querySelector('.menu-toggle');
    const navList = document.querySelector('.nav-list');
    const breakpoint = 768;

    if (menuToggle && navList) {
        menuToggle.addEventListener('click', () => {
            if (window.innerWidth < breakpoint) {
                navList.classList.toggle('is-open');
                const icon = menuToggle.querySelector('i');
                const isOpen = navList.classList.contains('is-open');
                icon.className = isOpen ? 'fas fa-times' : 'fas fa-bars';
            }
        });
    }

    // --- 2. ANIMATION LOGO ---
    const logoLink = document.querySelector('.logo a');
    if (logoLink) {
        logoLink.style.transition = 'color 0.3s ease';
        logoLink.addEventListener('mouseenter', () => logoLink.style.color = '#388E3C');
        logoLink.addEventListener('mouseleave', () => logoLink.style.color = '');
    }

    // --- 3. REQUÊTES ASYNC & STATISTIQUES ---
    async function initialiserDonnees() {
        try {
            console.log("Tentative de récupération des données...");
            const reponse = await fetch('data.json');
            
            if (!reponse.ok) throw new Error(`Erreur HTTP: ${reponse.status}`);

            const donnees = await reponse.json();
            console.log("Données reçues :", donnees);

            // Vérification de la présence de la clé statsEcologiques
            if (donnees && donnees.statsEcologiques) {
                afficherStats(donnees.statsEcologiques);
                creerGraphique(donnees.statsEcologiques);
            } else {
                throw new Error("Clé 'statsEcologiques' introuvable dans le JSON");
            }

        } catch (erreur) {
            console.error("Erreur lors du chargement asynchrone :", erreur);
            const container = document.getElementById('container-stats');
            if (container) container.innerHTML = "<p style='color:red;'>Erreur de chargement des statistiques.</p>";
        }
    }

    function afficherStats(stats) {
        const container = document.getElementById('container-stats');
        if (!container) return;

        container.innerHTML = stats.map(s => `
            <div class="stat-card" style="border: 1px solid #ddd; padding: 15px; border-radius: 8px; text-align: center; min-width: 150px; background: #f9f9f9;">
                <h3 style="font-size: 1rem; color: #555;">${s.label}</h3>
                <p style="font-size: 1.5rem; font-weight: bold; color: #388E3C; margin: 0;">${s.valeur}</p>
            </div>
        `).join('');
    }

    function creerGraphique(stats) {
        const ctx = document.getElementById('myChart');
        if (!ctx) return;


        if (window.myChartInstance) {
            window.myChartInstance.destroy();
        }

        window.myChartInstance = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: stats.map(s => s.label),
                datasets: [{
                    label: 'Impact Environnemental',
                    data: stats.map(s => s.valeur),
                    backgroundColor: ['#388E3C', '#81C784', '#A5D6A7'],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                scales: { y: { beginAtZero: true } }
            }
        });
    }

    // Lancer la récupération
    initialiserDonnees();
});