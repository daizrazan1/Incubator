<?php
$translations = [
    'en' => [
        'home' => 'Home',
        'browse_parts' => 'Browse Parts',
        'build_pc' => 'Build PC',
        'my_profile' => 'My Profile',
        'contact' => 'Contact',
        'login' => 'Login',
        'register' => 'Register',
        'logout' => 'Logout',
        'settings' => 'Settings',
        'language' => 'Language',
        'currency' => 'Currency',
        'theme' => 'Theme',
        'light' => 'Light',
        'dark' => 'Dark',
        'save_settings' => 'Save Settings',
        'featured_builds' => 'Featured Builds',
        'trending_parts' => 'Trending Parts',
        'search_placeholder' => 'Search for parts (e.g., RTX 5090, AMD Ryzen...)'
    ],
    'es' => [
        'home' => 'Inicio',
        'browse_parts' => 'Explorar Partes',
        'build_pc' => 'Construir PC',
        'my_profile' => 'Mi Perfil',
        'contact' => 'Contacto',
        'login' => 'Iniciar Sesión',
        'register' => 'Registrarse',
        'logout' => 'Cerrar Sesión',
        'settings' => 'Configuración',
        'language' => 'Idioma',
        'currency' => 'Moneda',
        'theme' => 'Tema',
        'light' => 'Claro',
        'dark' => 'Oscuro',
        'save_settings' => 'Guardar Configuración',
        'featured_builds' => 'Equipos Destacados',
        'trending_parts' => 'Partes Tendencia',
        'search_placeholder' => 'Buscar partes (ej. RTX 5090, AMD Ryzen...)'
    ],
    'fr' => [
        'home' => 'Accueil',
        'browse_parts' => 'Parcourir les pièces',
        'build_pc' => 'Monter un PC',
        'my_profile' => 'Mon Profil',
        'contact' => 'Contact',
        'login' => 'Connexion',
        'register' => 'S\'inscrire',
        'logout' => 'Déconnexion',
        'settings' => 'Paramètres',
        'language' => 'Langue',
        'currency' => 'Devise',
        'theme' => 'Thème',
        'light' => 'Clair',
        'dark' => 'Sombre',
        'save_settings' => 'Enregistrer les paramètres',
        'featured_builds' => 'Configurations en vedette',
        'trending_parts' => 'Pièces populaires',
        'search_placeholder' => 'Rechercher des pièces (ex. RTX 5090, AMD Ryzen...)'
    ],
    'hy' => [
        'home' => 'Գլխավոր',
        'browse_parts' => 'Դիտել մասերը',
        'build_pc' => 'Հավաքել PC',
        'my_profile' => 'Իմ պրոֆիլը',
        'contact' => 'Կապ',
        'login' => 'Մուտք',
        'register' => 'Գրանցվել',
        'logout' => 'Ելք',
        'settings' => 'Կարգավորումներ',
        'language' => 'Լեզու',
        'currency' => 'Արժույթ',
        'theme' => 'Թեմա',
        'light' => 'Լուսավոր',
        'dark' => 'Մութ',
        'save_settings' => 'Պահպանել կարգավորումները',
        'featured_builds' => 'Առաջարկվող հավաքածուներ',
        'trending_parts' => 'Թրենդային մասեր',
        'search_placeholder' => 'Որոնել մասեր (օր. RTX 5090, AMD Ryzen...)'
    ]
];

function __($key) {
    global $translations;
    $lang = $_SESSION['lang'] ?? 'en';
    return $translations[$lang][$key] ?? $translations['en'][$key] ?? $key;
}

function formatCurrency($amount) {
    $currency = $_SESSION['currency'] ?? 'USD';
    $rates = [
        'USD' => ['symbol' => '$', 'rate' => 1.0],
        'EUR' => ['symbol' => '€', 'rate' => 0.92],
        'GBP' => ['symbol' => '£', 'rate' => 0.79],
        'JPY' => ['symbol' => '¥', 'rate' => 150.0],
        'CAD' => ['symbol' => 'C$', 'rate' => 1.35],
        'AUD' => ['symbol' => 'A$', 'rate' => 1.52]
    ];
    
    $rate = $rates[$currency]['rate'] ?? 1.0;
    $symbol = $rates[$currency]['symbol'] ?? '$';
    
    $converted = $amount * $rate;
    
    if ($currency === 'JPY') {
        return $symbol . number_format($converted, 0);
    }
    return $symbol . number_format($converted, 2);
}
