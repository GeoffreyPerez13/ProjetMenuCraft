/**
 * MenuCraft — Tour Guide (Driver.js)
 * Guided tours for admin pages
 */

(function() {
'use strict';

const isMobile = window.innerWidth < 768;

const tourSteps = {
    dashboard: [
        {
            element: '.admin-main > .card:first-of-type',
            popover: {
                title: 'Votre restaurant',
                description: 'Statut de votre site (en ligne / hors ligne). Vous pouvez le basculer directement depuis ce bouton.',
                side: isMobile ? 'bottom' : 'bottom',
                align: 'center'
            }
        },
        {
            element: '.grid-3',
            popover: {
                title: 'Indicateurs',
                description: 'Abonnement, réservations en attente et statut du site en un coup d\'œil.',
                side: 'bottom',
                align: 'center'
            }
        },
        {
            element: '.quick-actions',
            popover: {
                title: 'Accès rapides',
                description: 'Raccourcis vers les fonctions principales : carte, contact, logo, services, template et paramètres.',
                side: 'top',
                align: 'center'
            }
        },
        {
            element: '#adminSidebar .sidebar-nav',
            popover: {
                title: 'Navigation',
                description: 'Le menu latéral regroupe toutes les pages. Les sections Premium sont indiquées séparément.',
                side: 'right',
                align: 'start'
            }
        }
    ],

    'edit-card': [
        {
            element: '.admin-main > div:first-child .badge',
            popover: {
                title: 'Mode de carte',
                description: '"Éditable" = gestion plat par plat. "Images" = upload de photos de votre carte physique.',
                side: 'bottom',
                align: 'start'
            }
        },
        {
            element: '#categoriesList',
            popover: {
                title: 'Catégories & Plats',
                description: 'Organisez vos plats par catégorie. Glissez-déposez pour réordonner. Cliquez sur "+" pour ajouter un plat.',
                side: 'top',
                align: 'center'
            }
        },
        {
            element: '.category-header',
            popover: {
                title: 'Actions par catégorie',
                description: 'Ajoutez des plats, utilisez l\'ajout rapide (icône liste), modifiez ou supprimez la catégorie.',
                side: 'bottom',
                align: 'center'
            }
        },
        {
            element: '.card:has(#newMenuForm)',
            popover: {
                title: 'Menus du jour / Formules',
                description: 'Créez vos menus du jour ou formules avec entrée, plat, dessert et prix. Ils s\'affichent en avant sur votre site pour attirer les clients.',
                side: 'top',
                align: 'center'
            }
        },
        {
            element: '[href*="view-card"]',
            popover: {
                title: 'Prévisualiser',
                description: 'Visualisez votre carte telle qu\'elle apparaît aux visiteurs de votre site.',
                side: 'bottom',
                align: 'end'
            }
        }
    ],

    'edit-contact': [
        {
            element: '#contactForm',
            popover: {
                title: 'Informations de contact',
                description: 'Ces informations s\'affichent dans le pied de page et la section contact de votre site.',
                side: 'bottom',
                align: 'center'
            }
        },
        {
            element: '#telephone',
            popover: {
                title: 'Téléphone',
                description: 'Numéro cliquable sur mobile pour appeler directement votre restaurant.',
                side: 'bottom',
                align: 'start'
            }
        },
        {
            element: '#adresse',
            popover: {
                title: 'Adresse',
                description: 'Utilisée pour la carte Google Maps intégrée dans le pied de page de votre site.',
                side: 'bottom',
                align: 'start'
            }
        },
        {
            element: '#horaires',
            popover: {
                title: 'Horaires',
                description: 'Écrivez une ligne par jour. Ex: "Lundi - Vendredi : 12h - 14h30 / 19h - 22h30".',
                side: 'top',
                align: 'start'
            }
        }
    ],

    'edit-logo-banner': [
        {
            element: '.grid-2 > .card:first-child',
            popover: {
                title: 'Logo',
                description: 'Apparaît dans l\'en-tête du site. Formats : JPG, PNG, WebP. Max 5 Mo.',
                side: isMobile ? 'bottom' : 'right',
                align: 'center'
            }
        },
        {
            element: '.grid-2 > .card:last-child',
            popover: {
                title: 'Bannière',
                description: 'Grande image d\'accueil en haut de votre site. Privilégiez une photo HD en format paysage.',
                side: isMobile ? 'bottom' : 'left',
                align: 'center'
            }
        },
        {
            element: '.upload-area',
            popover: {
                title: 'Upload simplifié',
                description: 'Cliquez sur la zone ou glissez-déposez votre image. Le remplacement est instantané.',
                side: 'top',
                align: 'center'
            }
        }
    ],

    'edit-services': [
        {
            element: '#services-grid',
            popover: {
                title: 'Services proposés',
                description: 'Activez les services de votre restaurant. Ils s\'affichent sous forme d\'icônes sur votre site.',
                side: 'bottom',
                align: 'center'
            }
        },
        {
            element: '#payments-grid',
            popover: {
                title: 'Moyens de paiement',
                description: 'Indiquez tous les moyens de paiement acceptés. Vos clients le verront clairement.',
                side: 'bottom',
                align: 'center'
            }
        },
        {
            element: '.grid-2',
            popover: {
                title: 'Réseaux sociaux',
                description: 'Collez les liens de vos profils sociaux. Les icônes apparaîtront automatiquement sur votre site.',
                side: 'top',
                align: 'center'
            }
        }
    ],

    'edit-template': [
        {
            element: '.template-palettes-grid',
            popover: {
                title: 'Palette de couleurs',
                description: 'Choisissez parmi 7 palettes prédéfinies ou créez votre propre palette personnalisée en sélectionnant "Personnalisé" pour définir vos couleurs (couleur principale + fond).',
                side: 'bottom',
                align: 'center'
            }
        },
        {
            element: '.template-layouts-grid',
            popover: {
                title: 'Disposition (Layout)',
                description: 'Change la structure du site. "Bistro" met l\'accent sur les photos, "Océan" ajoute des éléments visuels aquatiques.',
                side: 'top',
                align: 'center'
            }
        },
        {
            element: 'a[href*="display"][target="_blank"]',
            popover: {
                title: 'Prévisualiser votre site',
                description: 'Cliquez sur ce bouton pour ouvrir votre site public et vérifier le rendu de vos changements.',
                side: 'bottom',
                align: 'end'
            }
        }
    ],

    reservations: [
        {
            element: '.resa-stats',
            popover: {
                title: 'Vue d\'ensemble',
                description: 'Nombre de réservations en attente, confirmées aujourd\'hui et total confirmées.',
                side: 'bottom',
                align: 'center'
            }
        },
        {
            element: '.resa-filters',
            popover: {
                title: 'Filtres',
                description: 'Filtrez par statut (en attente, confirmée, terminée, no-show) ou par date précise.',
                side: 'bottom',
                align: 'start'
            }
        },
        {
            element: '.card:has(.card-header)',
            popover: {
                title: 'Gestion des réservations',
                description: 'C\'est ici que vous validez ou refusez les demandes de réservation. Confirmez pour attribuer une table, ou déclinez si le créneau est complet.',
                side: 'top',
                align: 'center'
            }
        }
    ],

    stats: [
        {
            element: '#statsPeriod',
            popover: {
                title: 'Période',
                description: 'Changez la période d\'analyse : 7 jours, 30 jours ou 90 jours.',
                side: 'bottom',
                align: 'end'
            }
        },
        {
            element: '#statsCards',
            popover: {
                title: 'Indicateurs clés',
                description: 'Visites totales, visiteurs uniques, pourcentage mobile et pages les plus vues.',
                side: 'bottom',
                align: 'center'
            }
        },
        {
            element: '.stats-chart-main',
            popover: {
                title: 'Graphique de visites',
                description: 'Évolution jour par jour des visites sur la période sélectionnée.',
                side: 'top',
                align: 'center'
            }
        },
        {
            element: '.stats-charts-row',
            popover: {
                title: 'Détails',
                description: 'Répartition par appareil et pages les plus consultées de votre site.',
                side: 'top',
                align: 'center'
            }
        }
    ],

    'floor-plan': [
        {
            element: '#roomTabs',
            popover: {
                title: 'Salles',
                description: 'Créez plusieurs salles (Terrasse, Salle 1, Salon privé...). Les icônes sur chaque onglet permettent de renommer ou supprimer.',
                side: 'bottom',
                align: 'start'
            }
        },
        {
            element: '.fp-toolbar',
            popover: {
                title: 'Barre d\'outils',
                description: 'Ajoutez des tables ou des éléments structurels (portes, murs, escaliers, bar, WC). Sauvegardez quand vous avez terminé.',
                side: 'bottom',
                align: 'center'
            }
        },
        {
            element: '.fp-add-elements',
            popover: {
                title: 'Éléments structurels',
                description: 'Ajoutez portes, escaliers, murs, bar ou WC pour avoir un visuel complet de votre restaurant. Pivotez-les librement.',
                side: 'bottom',
                align: 'start'
            }
        },
        {
            element: '#floorPlanArea',
            popover: {
                title: 'Zone de plan',
                description: 'Glissez les objets pour les positionner. La zone s\'agrandit automatiquement si vous placez un élément plus loin — scrollez pour voir tout le plan.',
                side: 'top',
                align: 'center'
            }
        },
        {
            element: '.fp-sidebar',
            popover: {
                title: 'Propriétés',
                description: 'Sélectionnez une table ou un élément pour modifier ses propriétés : taille, rotation, zone, etc.',
                side: isMobile ? 'top' : 'left',
                align: 'start'
            }
        },
        {
            element: '.fp-save-reminder',
            popover: {
                title: 'Sauvegarde',
                description: 'N\'oubliez pas de sauvegarder ! Si vous quittez la page sans sauvegarder, une confirmation vous sera demandée.',
                side: 'top',
                align: 'center'
            }
        }
    ]
};

/**
 * Get current page name from URL
 */
function getCurrentPage() {
    const params = new URLSearchParams(window.location.search);
    return params.get('page') || 'dashboard';
}

/**
 * Filter steps to only include those with valid elements on the page
 */
function getValidSteps(steps) {
    return steps.filter(step => {
        if (!step.element) return true;
        // Handle comma-separated selectors (pick first match)
        const selectors = step.element.split(',').map(s => s.trim());
        for (const sel of selectors) {
            if (document.querySelector(sel)) {
                step.element = sel;
                return true;
            }
        }
        return false;
    });
}

/**
 * Start the tour for the current page
 */
window.startPageTour = function() {
    const page = getCurrentPage();
    const steps = tourSteps[page];

    if (!steps || steps.length === 0) return;

    const validSteps = getValidSteps(JSON.parse(JSON.stringify(steps)));
    if (validSteps.length === 0) return;

    const driver = window.driver.js.driver;

    const driverObj = driver({
        showProgress: true,
        animate: true,
        smoothScroll: true,
        stagePadding: 10,
        stageRadius: 10,
        allowClose: true,
        overlayColor: 'rgba(0, 0, 0, 0.55)',
        popoverClass: 'menucraft-tour',
        nextBtnText: 'Suivant →',
        prevBtnText: '← Précédent',
        doneBtnText: 'Terminer ✓',
        progressText: '{{current}} / {{total}}',
        steps: validSteps,
        onDestroyStarted: () => {
            driverObj.destroy();
        }
    });

    // On mobile, close sidebar first
    if (isMobile) {
        const sidebar = document.getElementById('adminSidebar');
        if (sidebar) sidebar.classList.remove('open');
        const overlay = document.getElementById('sidebarOverlay');
        if (overlay) overlay.classList.remove('active');
    }

    driverObj.drive();
};

})();
