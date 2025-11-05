// API Base URL
const API_BASE = '/api';

// Gemeinsame Funktionen
class ModernQuizApp {
    constructor() {
        this.user = null;
        this.init();
    }

    init() {
        // Lade User-Daten aus Session/LocalStorage
        const userData = localStorage.getItem('modernquiz_user');
        if (userData) {
            this.user = JSON.parse(userData);
            this.updateUserUI();
        }
    }

    async apiCall(endpoint, method = 'GET', data = null) {
        const options = {
            method,
            headers: {
                'Content-Type': 'application/json'
            }
        };

        if (data) {
            options.body = JSON.stringify(data);
        }

        try {
            const response = await fetch(API_BASE + endpoint, options);
            const result = await response.json();
            return result;
        } catch (error) {
            console.error('API Error:', error);
            return { success: false, message: error.message };
        }
    }

    setUser(userData) {
        this.user = userData;
        localStorage.setItem('modernquiz_user', JSON.stringify(userData));
        this.updateUserUI();
    }

    updateUserUI() {
        if (!this.user) return;

        const elements = {
            userName: document.getElementById('userName'),
            userAvatar: document.getElementById('userAvatar'),
            userCoins: document.getElementById('userCoins'),
            headerCoins: document.getElementById('headerCoins')
        };

        if (elements.userName) {
            elements.userName.textContent = this.user.username || 'Benutzer';
        }

        if (elements.userAvatar) {
            elements.userAvatar.textContent = (this.user.username || 'U')[0].toUpperCase();
        }

        if (elements.userCoins) {
            elements.userCoins.textContent = this.user.coins || 100;
        }

        if (elements.headerCoins) {
            elements.headerCoins.textContent = this.user.coins || 100;
        }
    }

    logout() {
        localStorage.removeItem('modernquiz_user');
        window.location.href = 'login.html';
    }

    formatNumber(num) {
        return new Intl.NumberFormat('de-DE').format(num);
    }

    showNotification(message, type = 'info') {
        // Einfache Notification (kann später erweitert werden)
        alert(message);
    }
}

// Globale App-Instanz
const app = new ModernQuizApp();

// Auth-Check
function checkAuth() {
    const publicPages = ['login.html', 'register.html'];
    const currentPage = window.location.pathname.split('/').pop();

    if (!publicPages.includes(currentPage) && !app.user) {
        window.location.href = 'login.html';
    }
}

// Prüfe Auth beim Laden
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', checkAuth);
} else {
    checkAuth();
}
