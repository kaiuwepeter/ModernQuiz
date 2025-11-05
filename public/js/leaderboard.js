// Leaderboard Funktionen

// Demo-Leaderboard-Daten
const demoLeaderboard = [
    {
        rank: 1,
        username: 'QuizMaster',
        points: 15420,
        games: 156,
        correct: 523,
        level: 12,
        streak: 45
    },
    {
        rank: 2,
        username: 'BrainChamp',
        points: 14230,
        games: 142,
        correct: 498,
        level: 11,
        streak: 38
    },
    {
        rank: 3,
        username: 'Smarty',
        points: 13100,
        games: 135,
        correct: 467,
        level: 10,
        streak: 32
    },
    {
        rank: 4,
        username: 'WisdomSeeker',
        points: 11890,
        games: 128,
        correct: 445,
        level: 10,
        streak: 28
    },
    {
        rank: 5,
        username: 'ThinkTank',
        points: 10560,
        games: 119,
        correct: 421,
        level: 9,
        streak: 25
    },
    {
        rank: 6,
        username: 'GeniusPlayer',
        points: 9870,
        games: 112,
        correct: 398,
        level: 9,
        streak: 22
    },
    {
        rank: 7,
        username: 'KnowledgeKing',
        points: 8920,
        games: 105,
        correct: 376,
        level: 8,
        streak: 19
    },
    {
        rank: 8,
        username: 'QuizQueen',
        points: 8450,
        games: 98,
        correct: 354,
        level: 8,
        streak: 17
    },
    {
        rank: 9,
        username: 'BrainPower',
        points: 7890,
        games: 91,
        correct: 332,
        level: 7,
        streak: 15
    },
    {
        rank: 10,
        username: 'SmartCookie',
        points: 7340,
        games: 85,
        correct: 312,
        level: 7,
        streak: 13
    }
];

// Lade Leaderboard
function loadLeaderboard(type = 'all') {
    const tbody = document.getElementById('leaderboardBody');
    if (!tbody) return;

    // Update Button-Styles
    document.querySelectorAll('button').forEach(btn => {
        if (btn.textContent.includes('Alle Zeit') && type === 'all') {
            btn.className = 'btn btn-primary';
        } else if (btn.textContent.includes('Diese Woche') && type === 'weekly') {
            btn.className = 'btn btn-primary';
        } else if (btn.textContent.includes('Heute') && type === 'daily') {
            btn.className = 'btn btn-primary';
        } else if (btn.textContent.includes('Zeit') || btn.textContent.includes('Woche') || btn.textContent.includes('Heute')) {
            btn.className = 'btn';
            btn.style.background = '#6b7280';
            btn.style.color = 'white';
        }
    });

    tbody.innerHTML = demoLeaderboard.map(player => {
        let rankBadgeClass = '';
        if (player.rank === 1) rankBadgeClass = 'gold';
        else if (player.rank === 2) rankBadgeClass = 'silver';
        else if (player.rank === 3) rankBadgeClass = 'bronze';

        return `
            <tr>
                <td>
                    ${rankBadgeClass ? `<span class="rank-badge ${rankBadgeClass}">${player.rank}</span>` : `#${player.rank}`}
                </td>
                <td>
                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                        <div style="width: 32px; height: 32px; border-radius: 50%; background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%); display: flex; align-items: center; justify-content: center; color: white; font-weight: 700;">
                            ${player.username[0].toUpperCase()}
                        </div>
                        <span style="font-weight: 600;">${player.username}</span>
                    </div>
                </td>
                <td><strong>${app.formatNumber(player.points)}</strong></td>
                <td>${player.games}</td>
                <td>${player.correct}</td>
                <td>
                    <span style="background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%); color: white; padding: 0.25rem 0.75rem; border-radius: 12px; font-size: 0.9rem;">
                        Lvl ${player.level}
                    </span>
                </td>
                <td>
                    <span style="color: var(--warning);">
                        <i class="fas fa-fire"></i> ${player.streak}
                    </span>
                </td>
            </tr>
        `;
    }).join('');

    loadMyRanking();
}

// Lade meine Platzierung
function loadMyRanking() {
    if (!app.user) return;

    // Simuliere User-Ranking
    const myRank = Math.floor(Math.random() * 50) + 11;
    const myPoints = Math.floor(Math.random() * 5000) + 2000;
    const myGames = Math.floor(Math.random() * 50) + 20;
    const myStreak = Math.floor(Math.random() * 10) + 1;

    document.getElementById('myRank').textContent = myRank;
    document.getElementById('myPoints').textContent = app.formatNumber(myPoints);
    document.getElementById('myGames').textContent = myGames;
    document.getElementById('myStreak').textContent = myStreak;
}

// Initialisierung
if (window.location.pathname.includes('leaderboard.html')) {
    loadLeaderboard('all');
}
