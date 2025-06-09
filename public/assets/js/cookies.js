/**
 * Script pro správu a zobrazení cookie banneru
 */
document.addEventListener('DOMContentLoaded', function() {
    const cookieBanner = document.getElementById('cookie-banner');
    
    // Kontrola, zda již existuje souhlas s cookies
    function hasCookieConsent() {
        return document.cookie.split(';').some(item => item.trim().startsWith('cookie_consent='));
    }

    // **PŘESUNUTO NAHORU** - Kontrola, zda existuje souhlas pro konkrétní typ cookies
    function hasConsentFor(cookieType) {
        if (!hasCookieConsent()) {
            return false;
        }
        
        try {
            const cookieValue = document.cookie
                .split('; ')
                .find(row => row.startsWith('cookie_consent='))
                .split('=')[1];
                
            const consent = JSON.parse(decodeURIComponent(cookieValue));
            return consent[cookieType] === true;
        } catch (e) {
            console.error('Chyba při čtení cookie souhlasu:', e);
            return false;
        }
    }
    
    // Zobrazení cookie banneru, pokud ještě neexistuje souhlas
    function showCookieBanner() {       
        if (!hasCookieConsent() && cookieBanner) {
            cookieBanner.classList.remove('hidden', 'translate-y-full');
        }
    }
    
    // Aktivace zlatých přepínačů na stránce nastavení cookies (pokud jsme na této stránce)
    function activateGoldSwitches() {
        const checkboxes = document.querySelectorAll('input[type="checkbox"]');
        
        checkboxes.forEach(checkbox => {
            // Zjištění, zda se jedná o přepínač cookie
            if (!checkbox.classList.contains('sr-only') || !checkbox.nextElementSibling) return;
            
            // Nastavení počátečního stavu
            if (checkbox.checked) {
                const switchElement = checkbox.nextElementSibling;
                if (switchElement) {
                    // Použití přímého nastavení barvy místo třídy
                    switchElement.style.backgroundColor = 'bg-black';
                    switchElement.classList.remove('bg-gray-200');
                }
            }
            
            // Přidání event listeneru pro změnu barvy při přepnutí
            checkbox.addEventListener('change', function() {
                const switchElement = this.nextElementSibling;
                if (switchElement) {
                    if (this.checked) {
                        switchElement.style.backgroundColor = 'bg-black';
                        switchElement.classList.remove('bg-gray-200');
                    } else {
                        switchElement.style.backgroundColor = '';
                        switchElement.classList.add('bg-gray-200');
                    }
                }
            });
        });
    }

    // Reload stránky po změně cookie nastavení
    function reloadPageAfterConsent() {
        // Najdi všechny cookie akční linky
        const cookieLinks = document.querySelectorAll('a[href*="/cookies/"]');
        
        cookieLinks.forEach(link => {
            link.addEventListener('click', function(e) {
                const href = this.getAttribute('href');
                
                // Pokud se jedná o akci (ne settings), proveď reload po krátkém čekání
                if (href.includes('/accept-all') || href.includes('/reject-all')) {
                    setTimeout(() => {
                        window.location.reload();
                    }, 100);
                }
            });
        });

        // Pro formulář na settings stránce
        const cookieForm = document.querySelector('form[action*="/cookies/save"]');
        if (cookieForm) {
            cookieForm.addEventListener('submit', function(e) {
                setTimeout(() => {
                    window.location.reload();
                }, 100);
            });
        }
    }

    // Dynamické načtení Google Analytics pokud je souhlas
    function loadGoogleAnalytics() {
        if (hasConsentFor('analytics') && !document.querySelector('script[src*="googletagmanager"]')) {
            console.log('Načítám Google Analytics dynamicky...');
            
            // Načti gtag script
            const script = document.createElement('script');
            script.async = true;
            script.src = 'https://www.googletagmanager.com/gtag/js?id=G-M95Q53XWQX';
            document.head.appendChild(script);

            script.onload = function() {
                if (window.gtag) {
                    gtag('js', new Date());
                    gtag('config', 'G-M95Q53XWQX');
                    console.log('Google Analytics načten dynamicky');
                }
            };
        }
    }
    
    // Zobrazení banneru, pokud ještě není souhlas
    showCookieBanner();
    
    // Aktivace zlatých přepínačů (pokud jsme na stránce nastavení)
    if (document.getElementById('cookie-settings')) {
        activateGoldSwitches();
    }

    // Přidej event listenery pro reload
    reloadPageAfterConsent();

    // Zkus načíst GA pokud je souhlas
    loadGoogleAnalytics();
    
    // Globální funkce pro kontrolu souhlasu (pro externí použití)
    window.hasConsentFor = hasConsentFor;

    // Pomocná funkce pro načtení externích skriptů na základě souhlasu
    window.loadConditionalScript = function(url, cookieType) {
        if (hasConsentFor(cookieType)) {
            const script = document.createElement('script');
            script.src = url;
            document.head.appendChild(script);
        }
    };
});