// Login Form
const loginForm = document.getElementById('loginForm');
if (loginForm) {
    loginForm.addEventListener('submit', async (e) => {
        e.preventDefault();

        const username = document.getElementById('username').value;
        const password = document.getElementById('password').value;
        const errorDiv = document.getElementById('loginError');

        // Demo-Login (später mit echter API ersetzen)
        if (username && password) {
            // Simuliere erfolgreichen Login
            const userData = {
                id: 1,
                username: username,
                email: `${username}@example.com`,
                coins: 100,
                level: 1,
                points: 0
            };

            app.setUser(userData);
            window.location.href = 'index.html';
        } else {
            errorDiv.textContent = 'Bitte alle Felder ausfüllen';
            errorDiv.classList.remove('hidden');
        }
    });
}

// Register Form
const registerForm = document.getElementById('registerForm');
if (registerForm) {
    registerForm.addEventListener('submit', async (e) => {
        e.preventDefault();

        const username = document.getElementById('username').value;
        const email = document.getElementById('email').value;
        const password = document.getElementById('password').value;
        const confirmPassword = document.getElementById('confirmPassword').value;
        const errorDiv = document.getElementById('registerError');

        if (password !== confirmPassword) {
            errorDiv.textContent = 'Passwörter stimmen nicht überein';
            errorDiv.classList.remove('hidden');
            return;
        }

        // Demo-Registrierung (später mit echter API ersetzen)
        if (username && email && password) {
            // Simuliere erfolgreiche Registrierung
            const userData = {
                id: 1,
                username: username,
                email: email,
                coins: 100,
                level: 1,
                points: 0
            };

            app.setUser(userData);
            window.location.href = 'index.html';
        } else {
            errorDiv.textContent = 'Bitte alle Felder ausfüllen';
            errorDiv.classList.remove('hidden');
        }
    });
}
