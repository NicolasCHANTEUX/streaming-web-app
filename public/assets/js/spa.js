/**
 * SPA (Single Page Application) Navigation
 * Permet de naviguer entre les pages sans recharger, pour garder la musique active
 */

document.addEventListener('DOMContentLoaded', () => {
    console.log('🚀 SPA Navigation initialized');

    // 1. Intercepter tous les clics sur les liens
    document.body.addEventListener('click', e => {
        const link = e.target.closest('a');
        
        // Si ce n'est pas un lien, ou si c'est un téléchargement/lien externe, on ne fait rien
        if (!link || link.hasAttribute('download') || link.target === '_blank') return;

        const url = link.href;
        
        // Si c'est un lien interne à notre site
        if (url && url.startsWith(window.location.origin)) {
            e.preventDefault(); // STOP ! On empêche le rechargement brutal
            console.log('📄 Loading page via SPA:', url);
            loadPage(url);
        }
    });

    // 2. Gérer le bouton "Retour" du navigateur
    window.addEventListener('popstate', () => {
        console.log('⬅️ Browser back/forward button');
        loadPage(window.location.href, false);
    });
});

async function loadPage(url, push = true) {
    try {
        console.log('⏳ Fetching:', url);
        
        // Petit effet visuel (optionnel)
        const container = document.getElementById('mainContent');
        if (!container) {
            console.error('❌ Main content container not found!');
            window.location.href = url;
            return;
        }
        
        container.style.opacity = '0.5';
        container.style.transition = 'opacity 0.2s ease-in-out';

        // On va chercher la nouvelle page en arrière-plan
        const response = await fetch(url);
        
        if (!response.ok) {
            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        }
        
        const html = await response.text();

        // On convertit le texte reçu en vrai code HTML
        const parser = new DOMParser();
        const doc = parser.parseFromString(html, 'text/html');

        // On récupère juste le NOUVEAU contenu central
        const newContent = doc.getElementById('mainContent');
        const newTitle = doc.title;

        if (!newContent) {
            throw new Error('Main content not found in response');
        }

        // On remplace l'ancien par le nouveau
        container.innerHTML = newContent.innerHTML;
        document.title = newTitle;

        // On met à jour l'URL dans la barre d'adresse
        if (push) {
            window.history.pushState({}, '', url);
        }

        // Mettre à jour les liens actifs dans la navigation
        updateActiveLinks(url);

        // IMPORTANT : Ré-exécuter les scripts de la nouvelle page
        executePageScripts(container);

        // Restaurer l'opacité
        container.style.opacity = '1';
        
        // Scroll en haut de page
        window.scrollTo({ top: 0, behavior: 'smooth' });

        console.log('✅ Page loaded successfully');

    } catch (error) {
        console.error('❌ SPA loading error:', error);
        // Si ça plante, on recharge normalement en secours
        window.location.href = url;
    }
}

/**
 * Met à jour les liens actifs dans la navigation
 */
function updateActiveLinks(currentUrl) {
    const path = new URL(currentUrl).pathname;
    
    // Supprimer toutes les classes actives
    document.querySelectorAll('nav a').forEach(link => {
        link.classList.remove('text-primary');
        link.classList.add('text-text-sub');
    });
    
    // Ajouter la classe active au lien correspondant
    document.querySelectorAll('nav a').forEach(link => {
        const linkPath = new URL(link.href).pathname;
        
        // Correspondance exacte ou préfixe pour les sous-pages
        if (path === linkPath || (linkPath !== '/' && path.startsWith(linkPath))) {
            link.classList.remove('text-text-sub');
            link.classList.add('text-primary');
        }
    });
}

/**
 * Ré-exécute les scripts présents dans le contenu chargé
 */
function executePageScripts(container) {
    const scripts = container.querySelectorAll('script');
    
    scripts.forEach(oldScript => {
        const newScript = document.createElement('script');
        
        // Copier les attributs (src, type, etc.)
        Array.from(oldScript.attributes).forEach(attr => {
            newScript.setAttribute(attr.name, attr.value);
        });
        
        // Copier le contenu inline
        if (oldScript.textContent) {
            newScript.textContent = oldScript.textContent;
        }
        
        // Remplacer l'ancien script par le nouveau pour l'exécuter
        oldScript.parentNode.replaceChild(newScript, oldScript);
    });
    
    console.log(`🔄 Executed ${scripts.length} page script(s)`);
}
