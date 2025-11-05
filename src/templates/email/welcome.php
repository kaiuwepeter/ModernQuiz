<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #4F46E5; color: white; padding: 20px; text-align: center; }
        .content { background: #f9fafb; padding: 30px; }
        .button { display: inline-block; background: #4F46E5; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; margin: 20px 0; }
        .footer { text-align: center; color: #6b7280; font-size: 12px; margin-top: 30px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Willkommen bei ModernQuiz!</h1>
        </div>
        <div class="content">
            <p>Hallo <?= htmlspecialchars($username ?? 'dort') ?>,</p>

            <p>Herzlich willkommen bei ModernQuiz - der modernen Quiz-Plattform!</p>

            <p>Dein Account wurde erfolgreich erstellt. Bitte verifiziere deine Email-Adresse, um alle Features freizuschalten:</p>

            <p style="text-align: center;">
                <a href="<?= $verificationUrl ?? '#' ?>" class="button">Email verifizieren</a>
            </p>

            <p>Was du jetzt tun kannst:</p>
            <ul>
                <li>🎯 Spiele aus über 120 Fragen in 15 Kategorien</li>
                <li>🎮 Erstelle deine eigenen Quizze</li>
                <li>👥 Fordere Freunde zu Duellen heraus</li>
                <li>🏆 Sammle Achievements und steige im Ranking auf</li>
            </ul>

            <?php if (isset($referralCode)): ?>
            <p><strong>Dein Empfehlungscode:</strong> <?= htmlspecialchars($referralCode) ?></p>
            <p>Teile diesen Code mit Freunden und erhalte Bonuspunkte für jede Empfehlung!</p>
            <?php endif; ?>

            <p>Viel Spaß beim Quizzen!</p>

            <p>Dein ModernQuiz-Team</p>
        </div>
        <div class="footer">
            <p>© <?= date('Y') ?> ModernQuiz. Alle Rechte vorbehalten.</p>
            <p>Falls du diese Email nicht angefordert hast, kannst du sie ignorieren.</p>
        </div>
    </div>
</body>
</html>
