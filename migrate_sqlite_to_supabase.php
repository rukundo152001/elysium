<?php

echo "=============================================\n";
echo " SQLite -> Supabase PostgreSQL Data Migration\n";
echo "=============================================\n\n";

$pgsql = null;

try {

    // =================================================
    // SQLITE SOURCE
    // =================================================
    $sqlite = new PDO(
        'sqlite:' . __DIR__ . DIRECTORY_SEPARATOR .
        'database' . DIRECTORY_SEPARATOR .
        'finalelysium.sqlite'
    );

    $sqlite->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $sqlite->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

    echo "Connected to SQLite: OK\n";

    // =================================================
    // SUPABASE CONNECTION FUNCTION
    // =================================================
    function connectSupabase()
    {
        $pdo = new PDO(
            'pgsql:host=aws-1-eu-west-1.pooler.supabase.com;port=5432;dbname=postgres',
            'postgres.ekgouzemmjmulyvbwadh',
            '#GI152001@2720!'
        );

        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

        return $pdo;
    }

    $pgsql = connectSupabase();

    echo "Connected to Supabase: OK\n\n";

    // =================================================
    // TABLES
    // =================================================

    $tables = [
        'countries',
        'provinces',
        'districts',
        'sectors',
        'services',
        'insurance_providers',
        'insurance_categories',
        'insurance_packages',
        'users',
        'patients',
        'patient_activities',
        'payment_summaries'
    ];

    // =================================================
    // FUNCTION: INSERT TABLE
    // =================================================

    function importTable($sqlite, $pgsql, $table)
    {
        echo "---------------------------------------------\n";
        echo "Importing: $table\n";

        $columns = $sqlite
            ->query("PRAGMA table_info(\"$table\")")
            ->fetchAll();

        $columnNames = array_column($columns, 'name');

        $rows = $sqlite
            ->query("SELECT * FROM \"$table\"")
            ->fetchAll();

        echo "SQLite records: " . count($rows) . "\n";

        if (!$rows) {
            echo "Nothing to import.\n";
            return;
        }

        $quotedColumns = array_map(
            fn($column) => '"' . $column . '"',
            $columnNames
        );

        $placeholders = array_fill(
            0,
            count($columnNames),
            '?'
        );

        $sql =
            'INSERT INTO "' . $table . '" (' .
            implode(', ', $quotedColumns) .
            ') VALUES (' .
            implode(', ', $placeholders) .
            ')';

        $insert = $pgsql->prepare($sql);

        $count = 0;

        foreach ($rows as $row) {

            $values = [];

            foreach ($columnNames as $column) {
                $values[] = $row[$column];
            }

            $insert->execute($values);

            $count++;

            if ($count % 100 === 0) {
                echo "  Imported: $count\n";
            }
        }

        echo "Imported: $count\n";
    }

    // =================================================
    // PARENT TABLES
    // =================================================

    $parentTables = [
        'countries',
        'provinces',
        'districts',
        'sectors',
        'services',
        'insurance_providers',
        'insurance_categories',
        'insurance_packages',
        'users'
    ];

    foreach ($parentTables as $table) {
        importTable($sqlite, $pgsql, $table);
    }

    echo "\nParent tables completed.\n\n";

    // =================================================
    // PATIENTS
    // =================================================

    importTable($sqlite, $pgsql, 'patients');

    echo "\nPatients completed.\n\n";

    // =================================================
    // PATIENT ACTIVITIES
    // =================================================

    echo "---------------------------------------------\n";
    echo "Importing: patient_activities\n";

    $activityColumns = $sqlite
        ->query('PRAGMA table_info("patient_activities")')
        ->fetchAll();

    $activityColumnNames = array_column(
        $activityColumns,
        'name'
    );

    $activities = $sqlite
        ->query('SELECT * FROM "patient_activities" ORDER BY id')
        ->fetchAll();

    echo "SQLite records: " . count($activities) . "\n";

    // Get valid service IDs from Supabase
    $validServiceIds = $pgsql
        ->query('SELECT id FROM "services"')
        ->fetchAll(PDO::FETCH_COLUMN);

    $validServiceIds = array_map(
        'intval',
        $validServiceIds
    );

    echo "Valid service IDs: " . count($validServiceIds) . "\n";

    // Prepare activity INSERT
    $quotedActivityColumns = array_map(
        fn($column) => '"' . $column . '"',
        $activityColumnNames
    );

    $activityPlaceholders = array_fill(
        0,
        count($activityColumnNames),
        '?'
    );

    $activitySql =
        'INSERT INTO "patient_activities" (' .
        implode(', ', $quotedActivityColumns) .
        ') VALUES (' .
        implode(', ', $activityPlaceholders) .
        ')';

    $activityInsert = $pgsql->prepare($activitySql);

    $importedActivities = 0;
    $orphanedActivities = 0;

    foreach ($activities as $activity) {

        if (
            isset($activity['service_id']) &&
            $activity['service_id'] !== null &&
            !in_array(
                (int) $activity['service_id'],
                $validServiceIds,
                true
            )
        ) {
            $activity['service_id'] = null;
            $orphanedActivities++;
        }

        $values = [];

        foreach ($activityColumnNames as $column) {
            $values[] = $activity[$column];
        }

        $activityInsert->execute($values);

        $importedActivities++;

        if ($importedActivities % 100 === 0) {
            echo "  Imported activities: $importedActivities\n";
        }
    }

    echo "Imported activities: $importedActivities\n";
    echo "Orphaned service IDs converted to NULL: $orphanedActivities\n";

    // =================================================
    // PAYMENT SUMMARIES
    // =================================================

    echo "\n";

    importTable(
        $sqlite,
        $pgsql,
        'payment_summaries'
    );

    // =================================================
    // FINAL VERIFICATION
    // =================================================

    echo "\n=============================================\n";
    echo "MIGRATION FINISHED\n";
    echo "=============================================\n\n";

    echo "VERIFYING COUNTS...\n\n";

    foreach ($tables as $table) {

        $sqliteCount = (int) $sqlite
            ->query(
                "SELECT COUNT(*) FROM \"$table\""
            )
            ->fetchColumn();

        $supabaseCount = (int) $pgsql
            ->query(
                'SELECT COUNT(*) FROM "' . $table . '"'
            )
            ->fetchColumn();

        $status =
            ($sqliteCount === $supabaseCount)
            ? 'OK'
            : 'MISMATCH';

        echo str_pad($table, 30)
            . " SQLite: "
            . str_pad($sqliteCount, 6)
            . " Supabase: "
            . str_pad($supabaseCount, 6)
            . " [$status]\n";
    }

    echo "\n=============================================\n";
    echo "DONE\n";
    echo "=============================================\n";

} catch (Throwable $e) {

    echo "\n=============================================\n";
    echo "MIGRATION FAILED\n";
    echo "=============================================\n";

    echo $e->getMessage() . "\n";

    exit(1);
}