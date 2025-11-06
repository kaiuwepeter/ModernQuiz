/**
 * ModernQuiz Authentication
 * Connects to secure API endpoints
 */

const API_BASE = '/api';

/**
 * Make authenticated API request
 */
async function apiRequest(endpoint, options = {}) {
    const defaultOptions = {
        headers: {
            'Content-Type': 'application/json',
        },
        credentials: 'include', // Include cookies (session_token)
    };

    const mergedOptions = {
        ...defaultOptions,
        ...options,
        headers: {
            ...defaultOptions.headers,
            ...options.headers,
        },
    };

    try {
        const response = await fetch(`${API_BASE}${endpoint}`, mergedOptions);
        const data = await response.json();

        if (!response.ok) {
            throw new Error(data.error || data.message || 'Request failed');
        }

        return data;
    } catch (error) {
        console.error('API Request Error:', error);
        throw error;
    }
}

// ==========================================
// LOGIN FORM
// ==========================================

const loginForm = document.getElementById('loginForm');
if (loginForm) {
    loginForm.addEventListener('submit', async (e) => {
        e.preventDefault();

        const identifier = document.getElementById('username').value.trim();
        const password = document.getElementById('password').value;
        const errorDiv = document.getElementById('loginError');
        const submitButton = loginForm.querySelector('button[type="submit"]');

        // Validation
        if (!identifier || !password) {
            errorDiv.textContent = 'Bitte alle Felder ausfüllen';
            errorDiv.classList.remove('hidden');
            return;
        }

        // Disable button during request
        submitButton.disabled = true;
        submitButton.textContent = 'Anmeldung läuft...';
        errorDiv.classList.add('hidden');

        try {
            const response = await apiRequest('/auth/login', {
                method: 'POST',
                body: JSON.stringify({
                    identifier,
                    password
                })
            });

            if (response.success && response.user) {
                // Save user data
                app.setUser(response.user);

                // Redirect to dashboard
                window.location.href = 'index.html';
            } else {
                errorDiv.textContent = response.message || 'Login fehlgeschlagen';
                errorDiv.classList.remove('hidden');
            }
        } catch (error) {
            errorDiv.textContent = error.message || 'Login fehlgeschlagen. Bitte versuche es erneut.';
            errorDiv.classList.remove('hidden');
        } finally {
            submitButton.disabled = false;
            submitButton.textContent = 'Anmelden';
        }
    });
}

// ==========================================
// REGISTER FORM
// ==========================================

const registerForm = document.getElementById('registerForm');
if (registerForm) {
    registerForm.addEventListener('submit', async (e) => {
        e.preventDefault();

        const username = document.getElementById('username').value.trim();
        const email = document.getElementById('email').value.trim();
        const password = document.getElementById('password').value;
        const confirmPassword = document.getElementById('confirmPassword')?.value;
        const referralCode = document.getElementById('referralCode')?.value.trim();
        const errorDiv = document.getElementById('registerError');
        const submitButton = registerForm.querySelector('button[type="submit"]');

        // Validation
        if (!username || !email || !password) {
            errorDiv.textContent = 'Bitte alle Pflichtfelder ausfüllen';
            errorDiv.classList.remove('hidden');
            return;
        }

        if (confirmPassword && password !== confirmPassword) {
            errorDiv.textContent = 'Passwörter stimmen nicht überein';
            errorDiv.classList.remove('hidden');
            return;
        }

        // Disable button during request
        submitButton.disabled = true;
        submitButton.textContent = 'Registrierung läuft...';
        errorDiv.classList.add('hidden');

        try {
            const response = await apiRequest('/auth/register', {
                method: 'POST',
                body: JSON.stringify({
                    username,
                    email,
                    password,
                    referral_code: referralCode || null
                })
            });

            if (response.success) {
                // Show success message
                alert('Registrierung erfolgreich! Bitte überprüfe deine E-Mails zur Verifizierung.');

                // Redirect to login page
                window.location.href = 'login.html';
            } else {
                // Show errors
                const errorMessage = response.errors?.join(', ') || response.message || 'Registrierung fehlgeschlagen';
                errorDiv.textContent = errorMessage;
                errorDiv.classList.remove('hidden');
            }
        } catch (error) {
            errorDiv.textContent = error.message || 'Registrierung fehlgeschlagen. Bitte versuche es erneut.';
            errorDiv.classList.remove('hidden');
        } finally {
            submitButton.disabled = false;
            submitButton.textContent = 'Registrieren';
        }
    });
}

// ==========================================
// LOGOUT FUNCTION
// ==========================================

window.logout = async function() {
    try {
        await apiRequest('/auth/logout', {
            method: 'POST'
        });
    } catch (error) {
        console.error('Logout error:', error);
    } finally {
        // Clear local storage
        app.clearUser();

        // Redirect to login
        window.location.href = 'login.html';
    }
};

// ==========================================
// CHECK AUTHENTICATION STATUS
// ==========================================

async function checkAuthStatus() {
    try {
        const response = await apiRequest('/user/profile');

        if (response.success && response.user) {
            app.setUser(response.user);
            return true;
        }
    } catch (error) {
        // Not authenticated or session expired
        return false;
    }

    return false;
}

// ==========================================
// PROTECT PAGES THAT REQUIRE AUTHENTICATION
// ==========================================

const protectedPages = ['index.html', 'quiz.html', 'shop.html', 'leaderboard.html'];
const currentPage = window.location.pathname.split('/').pop();

if (protectedPages.includes(currentPage)) {
    // Check if user is authenticated
    const user = app.getUser();

    if (!user) {
        // Try to fetch user profile from API
        checkAuthStatus().then(authenticated => {
            if (!authenticated) {
                // Redirect to login if not authenticated
                window.location.href = 'login.html';
            }
        });
    }
}

// Export for use in other modules
window.auth = {
    apiRequest,
    checkAuthStatus,
    logout: window.logout
};
