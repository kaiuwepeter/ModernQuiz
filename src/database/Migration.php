<?php
// src/database/Migration.php
namespace ModernQuiz\Database;

class Migration {
    protected $db;
    protected $migrationsTable = 'migrations';

    public function __construct($database) {
        $this->db = $database;
        $this->ensureMigrationsTable();
    }

    protected function ensureMigrationsTable(): void {
        $sql = "CREATE TABLE IF NOT EXISTS {$this->migrationsTable} (
            id INT AUTO_INCREMENT PRIMARY KEY,
            migration VARCHAR(255) NOT NULL,
            batch INT NOT NULL,
            executed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";
        
        $this->db->query($sql);
    }

    public function migrate(): array {
        $executedMigrations = $this->getExecutedMigrations();
        $migrationsPath = __DIR__ . '/migrations/';
        $files = glob($migrationsPath . '*.php');
        $batch = $this->getNextBatchNumber();
        $executed = [];

        foreach ($files as $file) {
            $migrationName = basename($file, '.php');
            if (!in_array($migrationName, $executedMigrations)) {
                require_once $file;
                $className = 'ModernQuiz\\Database\\Migrations\\' . $migrationName;
                $migration = new $className($this->db);
                
                if ($migration->up()) {
                    $this->logMigration($migrationName, $batch);
                    $executed[] = $migrationName;
                }
            }
        }

        return $executed;
    }

    protected function getExecutedMigrations(): array {
        $result = $this->db->query("SELECT migration FROM {$this->migrationsTable}");
        return array_column($result->fetch_all(MYSQLI_ASSOC), 'migration');
    }

    protected function getNextBatchNumber(): int {
        $result = $this->db->query("SELECT MAX(batch) as batch FROM {$this->migrationsTable}");
        return ($result->fetch_assoc()['batch'] ?? 0) + 1;
    }

    protected function logMigration($name, $batch): void {
        $stmt = $this->db->prepare("INSERT INTO {$this->migrationsTable} (migration, batch) VALUES (?, ?)");
        $stmt->bind_param('si', $name, $batch);
        $stmt->execute();
    }
}