// Quiz Funktionen

let currentSession = null;
let currentQuestion = null;
let questionNumber = 0;
let timeLeft = 30;
let timerInterval = null;
let sessionPoints = 0;
let correctCount = 0;
let askedQuestions = [];

// Demo-Fragen (später von API laden)
const demoQuestions = [
    {
        id: 1,
        question: 'Was ist die Hauptstadt von Deutschland?',
        answers: [
            { id: 1, text: 'Berlin', correct: true },
            { id: 2, text: 'München', correct: false },
            { id: 3, text: 'Hamburg', correct: false },
            { id: 4, text: 'Köln', correct: false }
        ],
        points: 10,
        timeLimit: 30
    },
    {
        id: 2,
        question: 'Welches ist das größte Säugetier der Welt?',
        answers: [
            { id: 5, text: 'Elefant', correct: false },
            { id: 6, text: 'Blauwal', correct: true },
            { id: 7, text: 'Giraffe', correct: false },
            { id: 8, text: 'Nashorn', correct: false }
        ],
        points: 10,
        timeLimit: 30
    },
    {
        id: 3,
        question: 'In welchem Jahr fiel die Berliner Mauer?',
        answers: [
            { id: 9, text: '1987', correct: false },
            { id: 10, text: '1989', correct: true },
            { id: 11, text: '1991', correct: false },
            { id: 12, text: '1993', correct: false }
        ],
        points: 10,
        timeLimit: 30
    },
    {
        id: 4,
        question: 'Welches Element hat das chemische Symbol "Au"?',
        answers: [
            { id: 13, text: 'Silber', correct: false },
            { id: 14, text: 'Gold', correct: true },
            { id: 15, text: 'Aluminium', correct: false },
            { id: 16, text: 'Kupfer', correct: false }
        ],
        points: 10,
        timeLimit: 30
    },
    {
        id: 5,
        question: 'Wer malte die Mona Lisa?',
        answers: [
            { id: 17, text: 'Michelangelo', correct: false },
            { id: 18, text: 'Leonardo da Vinci', correct: true },
            { id: 19, text: 'Raphael', correct: false },
            { id: 20, text: 'Donatello', correct: false }
        ],
        points: 10,
        timeLimit: 30
    }
];

// Starte Quiz
async function startQuiz() {
    questionNumber = 0;
    sessionPoints = 0;
    correctCount = 0;
    askedQuestions = [];

    document.getElementById('quizStart').classList.add('hidden');
    document.getElementById('quizResults').classList.add('hidden');
    document.getElementById('quizQuestion').classList.remove('hidden');

    loadNextQuestion();
}

// Lade nächste Frage
function loadNextQuestion() {
    if (askedQuestions.length >= demoQuestions.length) {
        showResults();
        return;
    }

    // Wähle zufällige Frage, die noch nicht gestellt wurde
    const availableQuestions = demoQuestions.filter(q => !askedQuestions.includes(q.id));
    currentQuestion = availableQuestions[Math.floor(Math.random() * availableQuestions.length)];
    askedQuestions.push(currentQuestion.id);

    questionNumber++;
    timeLeft = currentQuestion.timeLimit;

    // Update UI
    document.getElementById('questionNumber').textContent = questionNumber;
    document.getElementById('questionText').textContent = currentQuestion.question;
    document.getElementById('sessionPoints').textContent = sessionPoints;

    // Lade Antworten
    const answersContainer = document.getElementById('answersContainer');
    answersContainer.innerHTML = currentQuestion.answers.map(answer => `
        <div class="answer-option" onclick="selectAnswer(${answer.id}, ${answer.correct})">
            <div style="font-size: 1.1rem;">${answer.text}</div>
        </div>
    `).join('');

    // Update Progress
    const progress = (questionNumber / demoQuestions.length) * 100;
    document.getElementById('progressBar').style.width = progress + '%';

    // Starte Timer
    startTimer();
}

// Timer
function startTimer() {
    if (timerInterval) clearInterval(timerInterval);

    timerInterval = setInterval(() => {
        timeLeft--;
        document.getElementById('timeLeft').textContent = timeLeft;

        if (timeLeft <= 0) {
            clearInterval(timerInterval);
            selectAnswer(null, false); // Zeit abgelaufen
        }
    }, 1000);
}

// Antwort auswählen
function selectAnswer(answerId, isCorrect) {
    clearInterval(timerInterval);

    const options = document.querySelectorAll('.answer-option');
    options.forEach(option => {
        option.style.pointerEvents = 'none';
    });

    // Markiere richtige/falsche Antwort
    const selectedOption = Array.from(options).find(opt =>
        opt.textContent.trim() === currentQuestion.answers.find(a => a.id === answerId)?.text
    );

    if (selectedOption) {
        if (isCorrect) {
            selectedOption.classList.add('correct');
            const points = calculatePoints();
            sessionPoints += points;
            correctCount++;
            document.getElementById('sessionPoints').textContent = sessionPoints;
        } else {
            selectedOption.classList.add('incorrect');
        }
    }

    // Zeige richtige Antwort
    const correctAnswer = currentQuestion.answers.find(a => a.correct);
    const correctOption = Array.from(options).find(opt =>
        opt.textContent.trim() === correctAnswer.text
    );
    if (correctOption && !isCorrect) {
        correctOption.classList.add('correct');
    }

    // Nächste Frage nach 2 Sekunden
    setTimeout(() => {
        loadNextQuestion();
    }, 2000);
}

// Berechne Punkte (mit Time-Bonus)
function calculatePoints() {
    const basePoints = currentQuestion.points;
    const timeBonus = Math.floor((timeLeft / currentQuestion.timeLimit) * basePoints * 0.5);
    return basePoints + timeBonus;
}

// Zeige Ergebnisse
function showResults() {
    document.getElementById('quizQuestion').classList.add('hidden');
    document.getElementById('quizResults').classList.remove('hidden');

    const coinsEarned = correctCount * 5;

    document.getElementById('finalPoints').textContent = sessionPoints;
    document.getElementById('correctCount').textContent = `${correctCount}/${demoQuestions.length}`;
    document.getElementById('coinsEarned').textContent = coinsEarned;

    // Update User Coins
    if (app.user) {
        app.user.coins += coinsEarned;
        app.setUser(app.user);
    }

    // Zufälliger Jackpot-Gewinn (10% Chance für Demo)
    if (Math.random() < 0.1) {
        const jackpotTypes = ['Bronze', 'Silber', 'Gold', 'Diamant'];
        const wonJackpot = jackpotTypes[Math.floor(Math.random() * jackpotTypes.length)];
        const winAmount = Math.floor(Math.random() * 1000) + 100;

        document.getElementById('jackpotWin').classList.remove('hidden');
        document.getElementById('jackpotDetails').textContent =
            `Du hast den ${wonJackpot} Jackpot gewonnen: ${winAmount} Coins!`;
    }
}

// Lade Powerups
async function loadMyPowerups() {
    const container = document.getElementById('myPowerups');
    if (!container) return;

    // Demo-Powerups
    const powerups = [
        { name: '50:50', count: 2, icon: 'fa-balance-scale' },
        { name: 'Extra Zeit', count: 1, icon: 'fa-clock' },
        { name: 'Doppelte Punkte', count: 3, icon: 'fa-star' }
    ];

    if (powerups.length === 0) {
        container.innerHTML = '<p style="text-align: center; color: #6b7280;">Keine Powerups vorhanden</p>';
        return;
    }

    container.innerHTML = powerups.map(p => `
        <div style="background: white; padding: 1rem; border-radius: 8px; text-align: center;">
            <div style="font-size: 2rem; margin-bottom: 0.5rem;">
                <i class="fas ${p.icon}" style="color: var(--primary);"></i>
            </div>
            <div style="font-weight: 600;">${p.name}</div>
            <div style="color: #6b7280; font-size: 0.9rem;">x${p.count}</div>
        </div>
    `).join('');
}

// Initialisierung
if (window.location.pathname.includes('quiz.html')) {
    loadMyPowerups();
}
