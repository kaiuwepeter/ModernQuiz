// Dashboard Funktionen

// Lade Dashboard-Daten
async function loadDashboard() {
    loadJackpots();
    loadUserStats();
}

// Lade Jackpots
async function loadJackpots() {
    const container = document.getElementById('jackpotsContainer');
    if (!container) return;

    // Demo-Daten (später von API laden)
    const jackpots = [
        {
            type: 'bronze',
            name: 'Bronze Jackpot',
            amount: 523.50,
            icon: 'fa-medal',
            color: '#CD7F32'
        },
        {
            type: 'silver',
            name: 'Silber Jackpot',
            amount: 2145.75,
            icon: 'fa-trophy',
            color: '#C0C0C0'
        },
        {
            type: 'gold',
            name: 'Gold Jackpot',
            amount: 10892.00,
            icon: 'fa-crown',
            color: '#FFD700'
        },
        {
            type: 'diamond',
            name: 'Diamant Jackpot',
            amount: 51234.50,
            icon: 'fa-gem',
            color: '#B9F2FF'
        }
    ];

    container.innerHTML = jackpots.map(jackpot => `
        <div class="jackpot-card ${jackpot.type} pulse">
            <div class="jackpot-icon">
                <i class="fas ${jackpot.icon}" style="color: ${jackpot.color}"></i>
            </div>
            <div class="jackpot-name">${jackpot.name}</div>
            <div class="jackpot-amount">${app.formatNumber(jackpot.amount)} Coins</div>
            <div style="color: #6b7280; font-size: 0.9rem;">
                <i class="fas fa-arrow-up"></i> Steigt mit jeder richtigen Antwort
            </div>
        </div>
    `).join('');
}

// Lade User-Statistiken
async function loadUserStats() {
    // Demo-Daten (später von API laden)
    const stats = {
        totalGames: 42,
        correctAnswers: 156,
        totalPoints: 2450,
        currentStreak: 8
    };

    const elements = {
        totalGames: document.getElementById('totalGames'),
        correctAnswers: document.getElementById('correctAnswers'),
        totalPoints: document.getElementById('totalPoints'),
        currentStreak: document.getElementById('currentStreak')
    };

    if (elements.totalGames) elements.totalGames.textContent = stats.totalGames;
    if (elements.correctAnswers) elements.correctAnswers.textContent = stats.correctAnswers;
    if (elements.totalPoints) elements.totalPoints.textContent = app.formatNumber(stats.totalPoints);
    if (elements.currentStreak) elements.currentStreak.textContent = stats.currentStreak;
}

// Zeige Jackpots Modal
function showJackpots() {
    alert('Jackpot-Details werden geladen...');
}

// Lade Dashboard beim Seitenload
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', loadDashboard);
} else {
    loadDashboard();
}
