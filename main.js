document.addEventListener('DOMContentLoaded', () => {
    
    // ----------------------------------------------------------------------
    // FONCTIONNALITÉ 1 : Gestion du Menu Hamburger (US 2 - pour Mobile)
    // ----------------------------------------------------------------------
    const menuToggle = document.querySelector('.menu-toggle');
    const navList = document.querySelector('.nav-list');
    const breakpoint = 768; // Le point de bascule défini dans votre CSS @media (min-width: 768px)

    if (menuToggle && navList) {
        menuToggle.addEventListener('click', () => {
            // Vérifie si nous sommes en mode mobile (largeur < 768px)
            if (window.innerWidth < breakpoint) {
                
                // Bascule l'affichage du menu et change l'icône
                navList.classList.toggle('is-open'); // Ajoute/retire la classe pour afficher/cacher
                
                const icon = menuToggle.querySelector('i');
                const isOpen = navList.classList.contains('is-open');
                
                if (isOpen) {
                    icon.classList.remove('fa-bars');
                    icon.classList.add('fa-times');
                } else {
                    icon.classList.remove('fa-times');
                    icon.classList.add('fa-bars');
                }
            }
        });

        // Gestion de la transition entre mobile et desktop : 
        // Si l'utilisateur redimensionne la fenêtre au-dessus du breakpoint, 
        // on s'assure que la classe 'is-open' est retirée pour ne pas interférer avec le CSS desktop.
        window.addEventListener('resize', () => {
            if (window.innerWidth >= breakpoint && navList.classList.contains('is-open')) {
                navList.classList.remove('is-open');
                menuToggle.querySelector('i').classList.remove('fa-times');
                menuToggle.querySelector('i').classList.add('fa-bars');
            }
        });
    }

    // ----------------------------------------------------------------------
    // FONCTIONNALITÉ 2 : Geste/Animation simple sur le logo (US 1 - Accueil)
    // ----------------------------------------------------------------------
    const logoLink = document.querySelector('.logo a');
    if (logoLink) {
        // ... (le code mouseenter/mouseleave pour le logo peut rester tel quel) ...
        logoLink.addEventListener('mouseenter', () => {
            logoLink.style.color = '#388E3C'; 
            logoLink.style.transition = 'color 0.3s ease-in-out';
        });

        logoLink.addEventListener('mouseleave', () => {
            logoLink.style.color = 'var(--color-text)'; 
        });
    }
});