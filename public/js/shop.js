// Shop Funktionen

// Demo-Powerups
const shopPowerups = [
    {
        id: 1,
        name: '50:50',
        description: 'Entfernt 2 falsche Antworten',
        price: 50,
        icon: 'fa-balance-scale',
        type: '50_50'
    },
    {
        id: 2,
        name: 'Frage überspringen',
        description: 'Überspringe eine schwierige Frage',
        price: 75,
        icon: 'fa-forward',
        type: 'skip_question'
    },
    {
        id: 3,
        name: 'Extra Zeit',
        description: '+15 Sekunden für die aktuelle Frage',
        price: 60,
        icon: 'fa-clock',
        type: 'extra_time'
    },
    {
        id: 4,
        name: 'Doppelte Punkte',
        description: 'Verdopple die Punkte für die nächste Frage',
        price: 100,
        icon: 'fa-star',
        type: 'double_points'
    },
    {
        id: 5,
        name: 'Zeit einfrieren',
        description: 'Friert den Timer für 10 Sekunden ein',
        price: 80,
        icon: 'fa-snowflake',
        type: 'freeze_time'
    },
    {
        id: 6,
        name: 'Hinweis anzeigen',
        description: 'Zeigt einen Hinweis zur richtigen Antwort',
        price: 40,
        icon: 'fa-lightbulb',
        type: 'reveal_hint'
    }
];

// Lade Shop
function loadShop() {
    const container = document.getElementById('shopContainer');
    if (!container) return;

    container.innerHTML = shopPowerups.map(powerup => `
        <div class="powerup-card">
            <div class="powerup-icon">
                <i class="fas ${powerup.icon}" style="color: var(--primary);"></i>
            </div>
            <div class="powerup-name">${powerup.name}</div>
            <div class="powerup-description">${powerup.description}</div>
            <div class="powerup-price">
                <i class="fas fa-coins"></i> ${powerup.price}
            </div>
            <button class="btn btn-primary btn-block" onclick="buyPowerup(${powerup.id}, ${powerup.price})">
                <i class="fas fa-shopping-cart"></i> Kaufen
            </button>
        </div>
    `).join('');

    loadInventory();
}

// Kaufe Powerup
function buyPowerup(powerupId, price) {
    if (!app.user) {
        alert('Bitte melde dich an!');
        return;
    }

    if (app.user.coins < price) {
        alert('Nicht genügend Coins!');
        return;
    }

    const powerup = shopPowerups.find(p => p.id === powerupId);
    if (!powerup) return;

    // Ziehe Coins ab
    app.user.coins -= price;
    app.setUser(app.user);

    // Füge zum Inventar hinzu (Demo)
    let inventory = JSON.parse(localStorage.getItem('powerup_inventory') || '[]');
    const existing = inventory.find(item => item.id === powerupId);

    if (existing) {
        existing.quantity += 1;
    } else {
        inventory.push({
            id: powerupId,
            name: powerup.name,
            icon: powerup.icon,
            type: powerup.type,
            quantity: 1
        });
    }

    localStorage.setItem('powerup_inventory', JSON.stringify(inventory));

    alert(`${powerup.name} erfolgreich gekauft!`);
    loadInventory();
}

// Lade Inventar
function loadInventory() {
    const container = document.getElementById('inventoryContainer');
    if (!container) return;

    const inventory = JSON.parse(localStorage.getItem('powerup_inventory') || '[]');

    if (inventory.length === 0) {
        container.innerHTML = '<p style="text-align: center; color: #6b7280; padding: 2rem; grid-column: 1/-1;">Dein Inventar ist leer. Kaufe Powerups, um loszulegen!</p>';
        return;
    }

    container.innerHTML = inventory.map(item => `
        <div class="powerup-card">
            <div class="powerup-icon">
                <i class="fas ${item.icon}" style="color: var(--success);"></i>
            </div>
            <div class="powerup-name">${item.name}</div>
            <div style="font-size: 1.5rem; font-weight: 700; color: var(--primary); margin: 1rem 0;">
                Anzahl: ${item.quantity}
            </div>
            <button class="btn btn-success btn-block" disabled>
                <i class="fas fa-check"></i> Im Besitz
            </button>
        </div>
    `).join('');
}

// Initialisierung
if (window.location.pathname.includes('shop.html')) {
    loadShop();
}
