<?php
// includes/BackupUtil.php
// Lightweight MySQL dump using PDO. Intended for small/medium databases.

class BackupUtil {
    public static function ensureDir(string $dir): void {
        if (!is_dir($dir)) {
            @mkdir($dir, 0775, true);
        }
    }

    public static function listBackups(string $dir): array {
        if (!is_dir($dir)) return [];
        $files = glob(rtrim($dir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . '*.sql');
        $items = [];
        foreach ($files as $path) {
            $items[] = [
                'name' => basename($path),
                'path' => $path,
                'size' => filesize($path) ?: 0,
                'mtime' => filemtime($path) ?: 0,
            ];
        }
        // newest first
        usort($items, function($a,$b){ return $b['mtime'] <=> $a['mtime']; });
        return $items;
    }

    public static function deleteBackup(string $dir, string $filename): bool {
        $safe = basename($filename);
        $path = rtrim($dir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $safe;
        return (is_file($path) && pathinfo($path, PATHINFO_EXTENSION) === 'sql') ? @unlink($path) : false;
    }

    public static function exportDatabase(PDO $pdo, string $backupDir, array $options = []): array {
        self::ensureDir($backupDir);
        $dbName = self::getDatabaseName($pdo);
        $timestamp = isset($options['timestamp']) ? (string)$options['timestamp'] : date('Ymd_His');
        $file = rtrim($backupDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . ($options['prefix'] ?? 'backup_') . $dbName . '_' . $timestamp . '.sql';

        $tables = self::getTables($pdo);
        $fh = fopen($file, 'w');
        if (!$fh) {
            throw new RuntimeException('Unable to write backup file.');
        }

        $header = sprintf("-- Backup for database: %s\n-- Generated at: %s\n\nSET FOREIGN_KEY_CHECKS=0;\n\n", $dbName, date('c'));
        fwrite($fh, $header);

        foreach ($tables as $table) {
            // Create table
            $create = self::getCreateTable($pdo, $table);
            fwrite($fh, "--\n-- Table structure for table `$table`\n--\n\n");
            fwrite($fh, "DROP TABLE IF EXISTS `$table`;\n");
            fwrite($fh, $create . ";\n\n");

            // Data dump
            $count = self::writeTableData($pdo, $fh, $table, $options['rows_per_insert'] ?? 200);
            fwrite($fh, "\n");
        }

        fwrite($fh, "SET FOREIGN_KEY_CHECKS=1;\n");
        fclose($fh);
        return ['file' => $file, 'db' => $dbName, 'timestamp' => $timestamp];
    }

    protected static function getDatabaseName(PDO $pdo): string {
        $stmt = $pdo->query('SELECT DATABASE()');
        return (string)$stmt->fetchColumn();
    }

    protected static function getTables(PDO $pdo): array {
        $tables = [];
        $stmt = $pdo->query('SHOW TABLES');
        while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
            $tables[] = $row[0];
        }
        return $tables;
    }

    protected static function getCreateTable(PDO $pdo, string $table): string {
        $stmt = $pdo->query('SHOW CREATE TABLE `' . str_replace('`','``',$table) . '`');
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['Create Table'] ?? '';
    }

    protected static function writeTableData(PDO $pdo, $fh, string $table, int $chunkSize = 200): int {
        $count = 0;
        $stmt = $pdo->query('SELECT COUNT(*) FROM `'.$table.'`');
        $total = (int)$stmt->fetchColumn();
        if ($total === 0) return 0;

        fwrite($fh, "--\n-- Dumping data for table `$table` ($total rows)\n--\n\n");

        $offset = 0;
        while ($offset < $total) {
            $q = $pdo->query('SELECT * FROM `'.$table.'` LIMIT ' . (int)$chunkSize . ' OFFSET ' . (int)$offset);
            $rows = $q->fetchAll(PDO::FETCH_ASSOC);
            if (!$rows) break;

            $columns = array_map(function($c){ return '`'.str_replace('`','``',$c).'`'; }, array_keys($rows[0]));
            $colList = implode(', ', $columns);
            $valuesSql = [];
            foreach ($rows as $r) {
                $vals = [];
                foreach ($r as $v) {
                    if ($v === null) { $vals[] = 'NULL'; continue; }
                    $vals[] = "'" . str_replace(["\\","'", "\n", "\r", "\t"], ["\\\\","\\'","\\n","\\r","\\t"], (string)$v) . "'";
                }
                $valuesSql[] = '(' . implode(', ', $vals) . ')';
                $count++;
            }
            $insert = 'INSERT INTO `'.$table.'` ('.$colList.') VALUES ' . implode(",\n", $valuesSql) . ";\n";
            fwrite($fh, $insert);

            $offset += $chunkSize;
        }
        return $count;
    }

    public static function generateHumanReadableBackup(PDO $pdo, string $backupDir): string {
        self::ensureDir($backupDir);
        $dbName = self::getDatabaseName($pdo);
        $timestamp = date('Ymd_His');
        $file = rtrim($backupDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'readable_' . $dbName . '_' . $timestamp . '.txt';

        $lines = [];
        $lines[] = 'Barangay Database Readable Backup';
        $lines[] = 'Generated at: ' . date('c');
        $lines[] = 'Database: ' . $dbName;
        $lines[] = str_repeat('=', 60);

        $tables = self::getTables($pdo);
        $lines[] = 'Tables found: ' . count($tables);
        $lines[] = '';

        foreach ($tables as $table) {
            // Row count
            $rowCount = 0;
            try {
                $stmt = $pdo->query('SELECT COUNT(*) FROM `'.$table.'`');
                $rowCount = (int)$stmt->fetchColumn();
            } catch (Throwable $e) {
                // ignore
            }

            // Try to infer last update timestamp
            $lastInfo = 'N/A';
            try {
                // Check if table has updated_at or created_at
                $cols = $pdo->query('SHOW COLUMNS FROM `'.$table.'`')->fetchAll(PDO::FETCH_COLUMN, 0);
                $candidate = null;
                if (in_array('updated_at', $cols, true)) $candidate = 'updated_at';
                elseif (in_array('modified_at', $cols, true)) $candidate = 'modified_at';
                elseif (in_array('created_at', $cols, true)) $candidate = 'created_at';
                if ($candidate) {
                    $stmt = $pdo->query('SELECT MAX(`'.$candidate.'`) FROM `'.$table.'`');
                    $val = $stmt->fetchColumn();
                    if ($val) { $lastInfo = $val; }
                }
            } catch (Throwable $e) {}

            $lines[] = '- Table: ' . $table;
            $lines[] = '  Records: ' . $rowCount;
            $lines[] = '  Last activity: ' . $lastInfo;
            $lines[] = '';
        }

        $help = [
            'How to use this backup:',
            '1) The .sql files can be restored using phpMyAdmin or the MySQL command line.',
            '2) This readable file summarizes what is inside the database in plain English.',
            '3) Keep both the .sql file and this .txt file for complete backups.',
        ];
        $lines[] = str_repeat('-', 60);
        foreach ($help as $h) { $lines[] = $h; }

        file_put_contents($file, implode(PHP_EOL, $lines));
        return $file;
    }

    public static function generateReadableTextDump(PDO $pdo, string $backupDir, string $dbName, string $timestamp): string {
        self::ensureDir($backupDir);
        $file = rtrim($backupDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'readable_' . $dbName . '_' . $timestamp . '.txt';
        $fh = fopen($file, 'w');
        if (!$fh) { throw new RuntimeException('Unable to write readable backup file.'); }
        fwrite($fh, "Barangay Database Readable Backup\n");
        fwrite($fh, 'Generated at: ' . date('c') . "\n");
        fwrite($fh, 'Database: ' . $dbName . "\n");
        fwrite($fh, str_repeat('=', 60) . "\n\n");

        $tables = self::getTables($pdo);
        foreach ($tables as $table) {
            fwrite($fh, 'Table: ' . $table . "\n");
            // Column names
            $colsStmt = $pdo->query('SHOW COLUMNS FROM `'.$table.'`');
            $cols = [];
            while ($c = $colsStmt->fetch(PDO::FETCH_ASSOC)) { $cols[] = $c['Field']; }
            fwrite($fh, 'Columns: ' . implode(', ', $cols) . "\n");

            $countStmt = $pdo->query('SELECT COUNT(*) FROM `'.$table.'`');
            $total = (int)$countStmt->fetchColumn();
            fwrite($fh, 'Records: ' . $total . "\n");
            if ($total > 0) {
                $offset = 0; $chunkSize = 200; $rowNum = 1;
                while ($offset < $total) {
                    $q = $pdo->query('SELECT * FROM `'.$table.'` LIMIT ' . (int)$chunkSize . ' OFFSET ' . (int)$offset);
                    $rows = $q->fetchAll(PDO::FETCH_ASSOC);
                    if (!$rows) break;
                    foreach ($rows as $r) {
                        $parts = [];
                        foreach ($r as $k => $v) {
                            if ($v === null || $v === '') { $parts[] = $k . '=⟂'; }
                            else { $parts[] = $k . '=' . (string)$v; }
                        }
                        fwrite($fh, 'Row ' . $rowNum++ . ': ' . implode('; ', $parts) . "\n");
                    }
                    $offset += $chunkSize;
                }
            }
            fwrite($fh, "\n");
        }
        fclose($fh);
        return $file;
    }
}
