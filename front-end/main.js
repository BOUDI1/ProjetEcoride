document.addEventListener('DOMContentLoaded', () => {
    
    // --- 1. CHARGEMENT DES STATISTIQUES (Accueil) ---
    const loadStats = async () => {
        try {
            // On appelle l'API PHP qui compte les trajets dans MySQL
            const response = await fetch('../back-end/api/get_stats.php');
            const result = await response.json();

            if (result.status === 'success') {
                const data = result.data;
                
                // Mise à jour des compteurs sur la page d'accueil
                const nbTrajetsElem = document.getElementById('nb-trajets');
                const co2SaveElem = document.getElementById('co2-save');

                if (nbTrajetsElem) nbTrajetsElem.innerText = data.nb_trajets;
                if (co2SaveElem) co2SaveElem.innerText = data.co2_economise + " kg";
                
                // Initialisation du graphique si l'élément existe
                if (document.getElementById('myChart')) {
                    initChart(data.labels, data.stats_hebdo);
                }
            }
        } catch (error) {
            console.error("Erreur lors du chargement des statistiques :", error);
        }
    };

    // --- 2. MOTEUR DE RECHERCHE (Page Covoiturages) ---
    const searchForm = document.getElementById('search-form');
    if (searchForm) {
        searchForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const depart = document.getElementById('depart').value;
            const arrivee = document.getElementById('arrivee').value;
            const eco = document.getElementById('eco-filter') ? document.getElementById('eco-filter').checked : false;

            try {
                // On envoie les paramètres à search_trajets.php
                const response = await fetch(`../back-end/api/search_trajets.php?depart=${depart}&arrivee=${arrivee}&eco=${eco}`);
                const data = await response.json();

                if (data.status === 'success') {
                    displayResults(data.results);
                }
            } catch (error) {
                console.error("Erreur lors de la recherche :", error);
            }
        });
    }

    // --- 3. FONCTION D'AFFICHAGE DES RÉSULTATS ---
    function displayResults(results) {
        const container = document.getElementById('results-container');
        if (!container) return;
        
        container.innerHTML = ''; // On vide les anciens résultats

        if (results.length === 0) {
            container.innerHTML = '<p class="no-result">Aucun trajet trouvé pour cette sélection.</p>';
            return;
        }

        results.forEach(trajet => {
            container.innerHTML += `
                <div class="trajet-card">
                    <div class="trajet-info">
                        <h3>${trajet.lieu_depart} ➔ ${trajet.lieu_arrivee}</h3>
                        <p><i class="fas fa-user"></i> Chauffeur : ${trajet.prenom}</p>
                        <p><i class="fas fa-coins"></i> Prix : ${trajet.prix_personne} crédits</p>
                    </div>
                    <div class="trajet-eco">
                        ${trajet.energie === 'electrique' ? '<span class="badge-eco">🌿 Écologique</span>' : ''}
                    </div>
                </div>
            `;
        });