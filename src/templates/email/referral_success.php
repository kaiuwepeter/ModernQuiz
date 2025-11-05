<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #10b981; color: white; padding: 20px; text-align: center; }
        .content { background: #f9fafb; padding: 30px; }
        .highlight { background: #d1fae5; padding: 20px; border-radius: 5px; text-align: center; margin: 20px 0; }
        .footer { text-align: center; color: #6b7280; font-size: 12px; margin-top: 30px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🎉 Glückwunsch!</h1>
        </div>
        <div class="content">
            <p>Hallo <?= htmlspecialchars($username ?? 'dort') ?>,</p>

            <p>Großartige Neuigkeiten! <strong><?= htmlspecialchars($referredUsername ?? 'Ein neuer User') ?></strong> hat sich mit deinem Empfehlungscode angemeldet!</p>

            <div class="highlight">
                <h2 style="margin: 0; color: #10b981;">+<?= $bonusPoints ?? 50 ?> Punkte</h2>
                <p style="margin: 10px 0 0;">Du hast Bonuspunkte erhalten!</p>
            </div>

            <p><strong>Deine Empfehlungs-Statistik:</strong></p>
            <ul>
                <li>📊 Gesamt geworbene User: <?= $totalReferrals ?? 1 ?></li>
                <li>⭐ Erhaltene Bonuspunkte: <?= $totalBonusPoints ?? $bonusPoints ?? 50 ?></li>
            </ul>

            <p>Teile weiterhin deinen Empfehlungscode:</p>
            <p style="text-align: center; font-size: 24px; background: white; padding: 15px; border-radius: 5px; font-family: monospace;">
                <strong><?= htmlspecialchars($referralCode ?? '') ?></strong>
            </p>

            <p>Danke, dass du ModernQuiz weiterempfiehlst!</p>

            <p>Dein ModernQuiz-Team</p>
        </div>
        <div class="footer">
            <p>© <?= date('Y') ?> ModernQuiz. Alle Rechte vorbehalten.</p>
        </div>
    </div>
</body>
</html>
