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
        .warning { background: #fef3c7; border-left: 4px solid #f59e0b; padding: 15px; margin: 20px 0; }
        .footer { text-align: center; color: #6b7280; font-size: 12px; margin-top: 30px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Passwort zurücksetzen</h1>
        </div>
        <div class="content">
            <p>Hallo <?= htmlspecialchars($username ?? 'dort') ?>,</p>

            <p>Du hast eine Anfrage zum Zurücksetzen deines Passworts gestellt.</p>

            <p style="text-align: center;">
                <a href="<?= $resetUrl ?? '#' ?>" class="button">Passwort zurücksetzen</a>
            </p>

            <div class="warning">
                <p><strong>⚠️ Wichtig:</strong></p>
                <ul>
                    <li>Dieser Link ist nur <strong><?= $expiresInMinutes ?? 60 ?> Minuten</strong> gültig</li>
                    <li>Der Link kann nur einmal verwendet werden</li>
                    <li>Aus Sicherheitsgründen wird dein Passwort erst geändert, nachdem du ein neues festgelegt hast</li>
                </ul>
            </div>

            <p>Falls du diese Anfrage nicht gestellt hast, ignoriere diese Email. Dein Passwort bleibt unverändert.</p>

            <p>Beste Grüße,<br>
            Dein ModernQuiz-Team</p>
        </div>
        <div class="footer">
            <p>© <?= date('Y') ?> ModernQuiz. Alle Rechte vorbehalten.</p>
            <p>Falls der Button nicht funktioniert, kopiere diesen Link: <?= $resetUrl ?? '#' ?></p>
        </div>
    </div>
</body>
</html>
