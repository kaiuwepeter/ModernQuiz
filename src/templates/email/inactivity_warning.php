<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #f59e0b; color: white; padding: 20px; text-align: center; }
        .content { background: #f9fafb; padding: 30px; }
        .button { display: inline-block; background: #4F46E5; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; margin: 20px 0; }
        .warning { background: #fee2e2; border-left: 4px solid #ef4444; padding: 15px; margin: 20px 0; }
        .stats { background: white; padding: 20px; border-radius: 5px; margin: 20px 0; }
        .footer { text-align: center; color: #6b7280; font-size: 12px; margin-top: 30px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>⚠️ Wir vermissen dich!</h1>
        </div>
        <div class="content">
            <p>Hallo <?= htmlspecialchars($username ?? 'dort') ?>,</p>

            <p>Du warst schon eine Weile nicht mehr bei ModernQuiz aktiv...</p>

            <div class="warning">
                <p><strong>⏰ Wichtige Information:</strong></p>
                <p>Dein Account ist seit <strong><?= $inactiveDays ?? 30 ?> Tagen</strong> inaktiv.</p>
                <p>Wenn du dich nicht innerhalb der nächsten <strong>5 Tage</strong> anmeldest, wird dein Account automatisch gelöscht.</p>
            </div>

            <?php if (isset($stats)): ?>
            <div class="stats">
                <h3>Deine Statistiken:</h3>
                <ul>
                    <li>🎯 Gespielte Quizze: <?= $stats['quizzes_played'] ?? 0 ?></li>
                    <li>⭐ Gesammelte Punkte: <?= $stats['total_points'] ?? 0 ?></li>
                    <li>🏆 Achievements: <?= $stats['achievements'] ?? 0 ?></li>
                    <li>👥 Freunde: <?= $stats['friends'] ?? 0 ?></li>
                </ul>
            </div>
            <?php endif; ?>

            <p>Komm zurück und:</p>
            <ul>
                <li>🎮 Spiele neue Quizze</li>
                <li>🏅 Sichere dir neue Achievements</li>
                <li>👊 Fordere Freunde heraus</li>
                <li>📈 Verbessere deinen Highscore</li>
            </ul>

            <p style="text-align: center;">
                <a href="<?= $loginUrl ?? '#' ?>" class="button">Jetzt anmelden</a>
            </p>

            <p><small>Falls du deinen Account nicht behalten möchtest, musst du nichts tun. Er wird automatisch nach Ablauf der Frist gelöscht.</small></p>

            <p>Wir würden uns freuen, dich wiederzusehen!<br>
            Dein ModernQuiz-Team</p>
        </div>
        <div class="footer">
            <p>© <?= date('Y') ?> ModernQuiz. Alle Rechte vorbehalten.</p>
        </div>
    </div>
</body>
</html>
