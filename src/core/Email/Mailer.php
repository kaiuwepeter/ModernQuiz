<?php
// src/core/Email/Mailer.php
namespace ModernQuiz\Core\Email;

use ModernQuiz\Core\Config;

class Mailer {
    private $db;
    private $config;

    public function __construct($database) {
        $this->db = $database;
        $this->config = Config::getInstance()->getMailConfig();
    }

    /**
     * Sendet eine Email sofort
     */
    public function send(string $to, string $subject, string $body, ?string $toName = null): bool {
        $headers = $this->buildHeaders($toName);

        if (Config::getInstance()->isDebug()) {
            // Im Debug-Modus: Logge Email statt zu senden
            error_log("EMAIL TO: $to\nSUBJECT: $subject\nBODY: $body");
            return true;
        }

        return mail($to, $subject, $body, $headers);
    }

    /**
     * Fügt Email zur Queue hinzu
     */
    public function queue(
        string $to,
        string $subject,
        string $body,
        ?string $toName = null,
        ?string $template = null,
        ?array $templateData = null,
        int $priority = 5,
        ?\DateTime $scheduledAt = null
    ): ?int {
        $stmt = $this->db->prepare(
            "INSERT INTO email_queue (to_email, to_name, subject, body, template, template_data, priority, scheduled_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
        );

        if ($stmt->execute([
            $to,
            $toName,
            $subject,
            $body,
            $template,
            $templateData ? json_encode($templateData) : null,
            $priority,
            $scheduledAt ? $scheduledAt->format('Y-m-d H:i:s') : null
        ])) {
            return $this->db->lastInsertId();
        }

        return null;
    }

    /**
     * Verarbeitet Email-Queue
     */
    public function processQueue(int $limit = 10): int {
        $stmt = $this->db->prepare(
            "SELECT * FROM email_queue
             WHERE status = 'pending'
             AND (scheduled_at IS NULL OR scheduled_at <= NOW())
             AND attempts < max_attempts
             ORDER BY priority DESC, created_at ASC
             LIMIT ?"
        );

        $stmt->execute([$limit]);
        $emails = $stmt->fetchAll();

        $processed = 0;

        foreach ($emails as $email) {
            // Update status zu 'sending'
            $this->updateEmailStatus($email['id'], 'sending');

            try {
                $body = $email['body'];

                // Wenn Template verwendet wird, rendere es
                if ($email['template']) {
                    $templateData = json_decode($email['template_data'], true);
                    $body = $this->renderTemplate($email['template'], $templateData);
                }

                $success = $this->send($email['to_email'], $email['subject'], $body, $email['to_name']);

                if ($success) {
                    $this->updateEmailStatus($email['id'], 'sent');
                    $updateStmt = $this->db->prepare(
                        "UPDATE email_queue SET sent_at = NOW() WHERE id = ?"
                    );
                    $updateStmt->execute([$email['id']]);
                    $processed++;
                } else {
                    throw new \Exception("mail() function returned false");
                }

            } catch (\Exception $e) {
                $attempts = $email['attempts'] + 1;
                $status = $attempts >= $email['max_attempts'] ? 'failed' : 'pending';

                $updateStmt = $this->db->prepare(
                    "UPDATE email_queue
                     SET status = ?, attempts = ?, error_message = ?
                     WHERE id = ?"
                );
                $updateStmt->execute([$status, $attempts, $e->getMessage(), $email['id']]);
            }
        }

        return $processed;
    }

    /**
     * Sendet Email mit Template
     */
    public function sendTemplate(string $to, string $subject, string $template, array $data = [], ?string $toName = null): bool {
        $body = $this->renderTemplate($template, $data);
        return $this->send($to, $subject, $body, $toName);
    }

    /**
     * Rendert ein Email-Template
     */
    private function renderTemplate(string $template, array $data = []): string {
        $templatePath = dirname(__DIR__, 2) . "/templates/email/{$template}.php";

        if (!file_exists($templatePath)) {
            throw new \Exception("Email template not found: {$template}");
        }

        extract($data);
        ob_start();
        include $templatePath;
        return ob_get_clean();
    }

    /**
     * Baut Email-Headers
     */
    private function buildHeaders(?string $toName = null): string {
        $from = $this->config['from']['address'];
        $fromName = $this->config['from']['name'];

        $headers = [];
        $headers[] = "From: {$fromName} <{$from}>";
        $headers[] = "Reply-To: {$from}";
        $headers[] = "MIME-Version: 1.0";
        $headers[] = "Content-Type: text/html; charset=UTF-8";
        $headers[] = "X-Mailer: ModernQuiz-Mailer/1.0";

        return implode("\r\n", $headers);
    }

    /**
     * Update Email Status
     */
    private function updateEmailStatus(int $emailId, string $status): void {
        $stmt = $this->db->prepare(
            "UPDATE email_queue SET status = ? WHERE id = ?"
        );
        $stmt->execute([$status, $emailId]);
    }

    /**
     * Cleanup alte Emails
     */
    public function cleanupOldEmails(int $daysOld = 30): int {
        $stmt = $this->db->prepare(
            "DELETE FROM email_queue
             WHERE status IN ('sent', 'failed')
             AND created_at < DATE_SUB(NOW(), INTERVAL ? DAY)"
        );

        $stmt->execute([$daysOld]);
        return $stmt->rowCount();
    }
}
