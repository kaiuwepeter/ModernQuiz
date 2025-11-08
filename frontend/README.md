# ModernQuiz Frontend

Vue.js 3 Frontend für ModernQuiz

## 🚀 Quick Start (Windows XAMPP)

### 1. Dependencies installieren

```bash
cd C:\xampp\htdocs\ModernQuiz\frontend
npm install
```

### 2. Dev-Server starten

```bash
npm run dev
```

**Öffnet automatisch:** http://localhost:5173/

### 3. Vite Proxy konfigurieren

**Datei:** `vite.config.js`

**Für XAMPP mit VirtualHost:**
```javascript
proxy: {
  '/api': {
    target: 'http://modernquiz.local',
    changeOrigin: true
  }
}
```

**Für XAMPP OHNE VirtualHost:**
```javascript
proxy: {
  '/api': {
    target: 'http://localhost/ModernQuiz/public',
    changeOrigin: true
  }
}
```

## 📁 Projekt-Struktur

```
frontend/
├── public/              # Statische Assets
├── src/
│   ├── assets/         # CSS, Bilder
│   ├── components/     # Wiederverwendbare Komponenten
│   ├── views/          # Seiten-Komponenten
│   ├── router/         # Vue Router Konfiguration
│   ├── store/          # Pinia State Management
│   ├── utils/          # Helper-Funktionen, API-Client
│   ├── App.vue         # Haupt-App-Komponente
│   └── main.js         # Entry Point
├── index.html          # HTML Template
├── vite.config.js      # Vite Konfiguration
├── tailwind.config.js  # Tailwind CSS Konfiguration
└── package.json        # Dependencies
```

## 🛠️ Verfügbare Scripts

```bash
# Development Server (mit Hot-Reload)
npm run dev

# Production Build
npm run build

# Preview Production Build
npm run preview
```

## 📦 Technologie-Stack

- **Vue.js 3** - Progressive JavaScript Framework
- **Vue Router** - Official Router
- **Pinia** - State Management
- **Vite** - Build Tool (super schnell!)
- **Tailwind CSS** - Utility-First CSS
- **Axios** - HTTP Client

## 🎨 Styling

**Tailwind CSS** ist konfiguriert. Nutze Utility-Classes:

```vue
<button class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg">
  Klick mich
</button>
```

**Custom Classes** in `src/assets/main.css`:
- `.btn-primary`
- `.btn-secondary`
- `.card`
- `.input`

## 🔐 Authentication

**Pinia Store:** `src/store/auth.js`

```javascript
import { useAuthStore } from '@/store/auth'

const authStore = useAuthStore()

// Login
await authStore.login('username', 'password')

// Logout
await authStore.logout()

// Check if authenticated
authStore.isAuthenticated
```

## 🌐 API Calls

**Helper:** `src/utils/api.js`

```javascript
import { quizAPI, shopAPI } from '@/utils/api'

// Quiz API
const categories = await quizAPI.getCategories()
const session = await quizAPI.startSession(1)

// Shop API
const powerups = await shopAPI.getPowerups()
```

## 🚧 Routing

**Protected Routes:** Require authentication

```javascript
{
  path: '/quiz',
  name: 'Quiz',
  component: () => import('../views/Quiz.vue'),
  meta: { requiresAuth: true }  // ← Protected!
}
```

**Guest Routes:** Only accessible when NOT logged in

```javascript
{
  path: '/login',
  name: 'Login',
  component: () => import('../views/Login.vue'),
  meta: { guest: true }  // ← Guests only!
}
```

## 📱 Responsive Design

**Mobile-First!** Alle Komponenten sind responsive.

**Tailwind Breakpoints:**
- `sm:` - ≥640px
- `md:` - ≥768px
- `lg:` - ≥1024px
- `xl:` - ≥1280px

```vue
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4">
  <!-- 1 Spalte auf Mobile, 2 auf Tablet, 4 auf Desktop -->
</div>
```

## 🐛 Debugging

**Browser Console öffnen:** F12

**Vue DevTools installieren:**
- Chrome: https://chrome.google.com/webstore (suche "Vue.js devtools")
- Firefox: https://addons.mozilla.org/firefox/ (suche "Vue.js devtools")

**Network Tab:** Siehe API-Requests

## 📦 Production Build

```bash
# Build für Production
npm run build

# Output: ../public/dist/
```

**Deployment:**
1. Build ausführen
2. Dateien in `public/dist/` werden erstellt
3. Auf Server hochladen
4. Apache/Nginx auf `public/` zeigen lassen

## 🔧 Konfiguration

**Backend-URL ändern:**

**Development:** `vite.config.js`
```javascript
proxy: {
  '/api': {
    target: 'http://deine-url.com',  // ← Ändern
    changeOrigin: true
  }
}
```

**Production:** Wird automatisch verwendet (da gleiche Domain)

## ❓ FAQ

**Q: Hot-Reload funktioniert nicht?**
A: Server neu starten (`Ctrl+C` dann `npm run dev`)

**Q: API-Calls schlagen fehl?**
A:
1. Backend läuft? (XAMPP Apache & MySQL)
2. Proxy richtig konfiguriert? (vite.config.js)
3. CORS Headers gesetzt? (Backend index.php)

**Q: Weiße Seite?**
A: Browser Console öffnen (F12), Fehler anschauen

**Q: npm install schlägt fehl?**
A:
```bash
npm cache clean --force
npm install
```

## 📚 Weiterführende Links

- Vue.js Docs: https://vuejs.org/
- Vite Docs: https://vitejs.dev/
- Tailwind CSS: https://tailwindcss.com/
- Pinia: https://pinia.vuejs.org/

## 🤝 Contributing

1. Feature-Branch erstellen
2. Änderungen machen
3. Committen
4. Pull Request erstellen

---

**Happy Coding! 🚀**
