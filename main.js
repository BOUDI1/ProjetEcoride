document.addEventListener('DOMContentLoaded', () => {

    // --- FONCTIONNALITÉ 1 : Gestion du Menu Hamburger ---
    const menuToggle = document.querySelector('.menu-toggle');
    const navList = document.querySelector('.nav-list');
    const breakpoint = 768;

    if (menuToggle && navList) {
        menuToggle.addEventListener('click', () => {
            if (window.innerWidth < breakpoint) {
                navList.classList.toggle('is-open');
                const icon = menuToggle.querySelector('i');
                const isOpen = navList.classList.contains('is-open');
                
                icon.classList.toggle('fa-bars', !isOpen);
                icon.classList.toggle('fa-times', isOpen);
            }
        });

        window.addEventListener('resize', () => {
            if (window.innerWidth >= breakpoint && navList.classList.contains('is-open')) {
                navList.classList.remove('is-open');
                menuToggle.querySelector('i').className = 'fas fa-bars';
            }
        });
    }

    // --- FONCTIONNALITÉ 2 : Animation Logo ---
    const logoLink = document.querySelector('.logo a');
    if (logoLink) {
        logoLink.style.transition = 'color 0.3s ease-in-out';
        logoLink.addEventListener('mouseenter', () => logoLink.style.color = '#388E3C');
        logoLink.addEventListener('mouseleave', () => logoLink.style.color = 'var(--color-text)');
    }

    // --- NOUVELLE FONCTIONNALITÉ 3 : Requêtes Asynchrones & ChartJS ---

    async function initialiserDonnees() {
        try {
            // Requête asynchrone (attente de la réponse du conteneur Docker)
            const reponse = await fetch('data.json');
            if (!reponse.ok) throw new Error("Erreur de récupération des données");

            const donnees = await reponse.json();

            // 1. Manipulation du DOM : Afficher les chiffres
            afficherStats(donnees.statsEcologiques);

            // 2. Graphique : Dessiner avec Chart.js
            creerGraphique(donnees.statsEcologiques);

        } catch (erreur) {
            console.error("Erreur lors du chargement asynchrone :", erreur);
        }
    }

    function afficherStats(stats) {
        const container = document.getElementById('container-stats');
        if (!container) return;

        container.innerHTML = stats.map(s => `
            <div class="stat-card">
                <h3>${s.label}</h3>
                <p>${s.valeur}</p>
            </div>
        `).join('');
    }

    function creerGraphique(stats) {
        const ctx = document.getElementById('myChart');
        if (!ctx) return;

        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: stats.map(s => s.label),
                datasets: [{
                    label: 'Impact Environnemental Ecoride',
                    data: stats.map(s => s.valeur),
                    backgroundColor: ['#388E3C', '#81C784', '#A5D6A7'],
                    borderRadius: 5
                }]
            },
            options: { responsive: true }
        });
    }

    // Lancement de la logique asynchrone
    initialiserDonnees();
});