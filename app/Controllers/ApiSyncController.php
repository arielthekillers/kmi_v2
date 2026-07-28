<?php

namespace App\Controllers;

use App\Core\Database;

class ApiSyncController
{
    public function handleSync()
    {
        // 1. Verify Secret Key
        $secret = $_POST['secret'] ?? '';
        $expectedSecret = getenv('SYNC_SECRET_KEY');

        if (empty($expectedSecret) || $secret !== $expectedSecret) {
            http_response_code(403);
            echo json_encode(['error' => 'Invalid or missing secret key.']);
            return;
        }

        $action = $_POST['action'] ?? '';

        $db = Database::getInstance();
        $conn = $db->getConnection();

        if ($action === 'compare') {
            // Receive local schema and compare
            $localSchemaJson = $_POST['schema'] ?? '{}';
            $localSchema = json_decode($localSchemaJson, true);

            if (!is_array($localSchema)) {
                echo json_encode(['error' => 'Invalid schema data.']);
                return;
            }

            // Get Prod Tables
            $stmt = $conn->query("SHOW TABLES");
            $prodTables = $stmt->fetchAll(\PDO::FETCH_COLUMN);

            $queriesToRun = [];

            foreach ($localSchema as $table => $tableData) {
                if (!in_array($table, $prodTables)) {
                    // Table missing in prod, get CREATE TABLE statement
                    $queriesToRun[] = $tableData['create_sql'] . ";";
                } else {
                    // Compare columns
                    $stmt = $conn->query("SHOW COLUMNS FROM `$table`");
                    $prodCols = $stmt->fetchAll(\PDO::FETCH_COLUMN);
                    
                    $localCols = $tableData['columns'];
                    $missingCols = array_diff($localCols, $prodCols);
                    
                    if (!empty($missingCols)) {
                        $createSqlLines = explode("\n", $tableData['create_sql']);
                        
                        foreach ($missingCols as $col) {
                            $defLine = "";
                            foreach ($createSqlLines as $line) {
                                $trimmed = trim($line);
                                if (strpos($trimmed, "`$col`") === 0) {
                                    $defLine = rtrim($trimmed, ",");
                                    break;
                                }
                            }
                            if ($defLine) {
                                $queriesToRun[] = "ALTER TABLE `$table` ADD COLUMN $defLine;";
                            }
                        }
                    }
                }
            }

            echo json_encode(['success' => true, 'queries' => $queriesToRun]);

        } elseif ($action === 'apply') {
            $queriesJson = $_POST['queries'] ?? '[]';
            $queries = json_decode($queriesJson, true);

            if (!is_array($queries)) {
                echo json_encode(['error' => 'Invalid queries data.']);
                return;
            }

            $success = 0;
            $errors = [];
            foreach ($queries as $sql) {
                try {
                    $conn->exec($sql);
                    $success++;
                } catch (\PDOException $e) {
                    $errors[] = "Error running: $sql | " . $e->getMessage();
                }
            }

            echo json_encode(['success' => true, 'executed' => $success, 'errors' => $errors]);
        } else {
            echo json_encode(['error' => 'Invalid action.']);
        }
    }
}
