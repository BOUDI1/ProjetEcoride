document.addEventListener('DOMContentLoaded', () => {
    
    // --- 1. GESTION DU MENU MOBILE ---
    const menuToggle = document.getElementById('menu-toggle');
    const navList = document.getElementById('nav-list');

    if (menuToggle && navList) {
        menuToggle.addEventListener('click', () => {
            navList.classList.toggle('is-open');
        });
    }

    // --- 2. RÉCUPÉRATION DES STATS ET GRAPHIQUE ---
    const loadStats = async () => {
        try {
            const response = await fetch('../back-end/api/get_stats.php');
            const result = await response.json();

            if (result.status === 'success') {
                const data = result.data;

                // Mise à jour des textes
                document.getElementById('nb-trajets').innerText = data.nb_trajets;
                document.getElementById('co2-save').innerText = data.co2_economise + " kg";

                // Création du graphique
                const ctx = document.getElementById('myChart');
                if (ctx) {
                    new Chart(ctx, {
                        type: 'bar',
                        data: {
                            labels: ['Trajets', 'CO2 Économisé (kg)'],
                            datasets: [{
                                label: 'Statistiques EcoRide',
                                data: [data.nb_trajets, data.co2_economise],
                                backgroundColor: ['#4CAF50', '#8BC34A'],
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
        } catch (error) {
            console.error("Erreur stats:", error);
        }
    };

    // --- 3. RECHERCHE ---
    const searchForm = document.getElementById('search-form');
    if (searchForm) {
        searchForm.addEventListener('submit', (e) => {
            e.preventDefault();
            const dep = document.getElementById('depart').value;
            const arr = document.getElementById('arrivee').value;
            window.location.href = `covoiturages.html?depart=${dep}&arrivee=${arr}`;
        });
    }

    loadStats();
});