<?php
/**
 * GMS Database Optimization & Horizontal Scaling Readiness Migration
 *
 * 1. Standardizes all tables and columns to InnoDB and utf8mb4 / utf8mb4_unicode_ci
 * 2. Adds missing performance and composite indexes for fast JOINs and filtering
 * 3. Prepares tables for horizontal replication (Galera / Read Replicas / Group Replication)
 *
 * Usage: php tools/db_optimize_migration.php [--dry-run]
 *        php tools/db_optimize_migration.php --apply --backup-file=D:/path/outside/webroot/backup.sql
 */

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

$apply = false;
$dry_run_requested = false;
$backup_file = null;
foreach (array_slice($argv ?? [], 1) as $arg) {
    if ($arg === '--apply') {
        $apply = true;
    } elseif ($arg === '--dry-run') {
        $dry_run_requested = true;
    } elseif (strpos($arg, '--backup-file=') === 0 && $backup_file === null) {
        $backup_file = substr($arg, strlen('--backup-file='));
    } else {
        fwrite(STDERR, "Unknown or duplicate argument. Use --dry-run or --apply --backup-file=<path>.\n");
        exit(2);
    }
}

if ($apply && $dry_run_requested) {
    fwrite(STDERR, "Choose either --dry-run or --apply.\n");
    exit(2);
}

if ($apply) {
    $backup_real_path = $backup_file !== null ? realpath($backup_file) : false;
    $web_root = realpath(dirname(__DIR__));
    $web_root_prefix = rtrim((string)$web_root, "\\/") . DIRECTORY_SEPARATOR;
    if ($backup_real_path === false || !is_file($backup_real_path) || !is_readable($backup_real_path) || filesize($backup_real_path) < 1 || stripos($backup_real_path, $web_root_prefix) === 0) {
        fwrite(STDERR, "Apply requires a readable, non-empty backup file outside the web root.\n");
        exit(2);
    }
}

$dry_run = !$apply;

// x.php selects the hosting database when HTTP_HOST is absent (the CLI case).
// This helper is deliberately local-only; production needs a separate reviewed migration.
$_SERVER['HTTP_HOST'] = 'localhost';
require_once __DIR__ . '/../includes/x.php';

echo "========================================================\n";
echo " GMS / SmartCut Database Optimization & Scaling Migration\n";
echo " Mode: " . ($dry_run ? "DRY-RUN (Simulasi)" : "LIVE EXECUTION") . "\n";
echo " Server: " . $host . ":" . $port . " | DB: " . $db . "\n";
echo "========================================================\n\n";

mysqli_report(MYSQLI_REPORT_OFF);
$c = mysqli_connect($host, $user, $password, $db, $port);
if (!$c) {
    die("ERROR: Gagal menyambung ke pangkalan data: " . mysqli_connect_error() . "\n");
}

mysqli_set_charset($c, "utf8mb4");

// 1. Senarai Jadual untuk Penukaran Collation ke utf8mb4_unicode_ci & Engine InnoDB
$tables = [
    'access_level',
    'app_info',
    'area_daily_records',
    'attendance',
    'contract',
    'contract_location',
    'contractor',
    'contractor_users',
    'daily_records',
    'dps',
    'holiday',
    'kmz_file',
    'notifications',
    'profile_image',
    'si_attachment',
    'site_instruction',
    'staff_checkin',
    'subzone',
    'user_fcm_tokens',
    'user_log',
    'userinfo',
    'users',
    'zone'
];

echo ">>> FASA 1: Penyeragaman Enjin (InnoDB) & Collation (utf8mb4_unicode_ci)\n";
$schema = [];
$schema_res = $c->query("SELECT TABLE_NAME, ENGINE, TABLE_COLLATION FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE()");
$collation_res = $c->query("SELECT TABLE_NAME, COUNT(*) AS mismatches FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND COLLATION_NAME IS NOT NULL AND COLLATION_NAME <> 'utf8mb4_unicode_ci' GROUP BY TABLE_NAME");
if (!$schema_res || !$collation_res) {
    fwrite(STDERR, "ERROR: Tidak dapat membaca metadata skema.\n");
    exit(1);
}
while ($row = $schema_res->fetch_assoc()) {
    $schema[$row['TABLE_NAME']] = $row;
}
$column_collation_mismatches = [];
while ($row = $collation_res->fetch_assoc()) {
    $column_collation_mismatches[$row['TABLE_NAME']] = (int)$row['mismatches'];
}
foreach ($tables as $table) {
    if (!isset($schema[$table])) {
        fwrite(STDERR, "ERROR: Jadual {$table} tidak ditemui; semakan dihentikan.\n");
        exit(1);
    }
    $changes = [];
    if (strcasecmp($schema[$table]['ENGINE'] ?? '', 'InnoDB') !== 0) {
        $changes[] = 'ENGINE = InnoDB';
    }
    if (($schema[$table]['TABLE_COLLATION'] !== null && strcasecmp($schema[$table]['TABLE_COLLATION'], 'utf8mb4_unicode_ci') !== 0) || !empty($column_collation_mismatches[$table])) {
        $changes[] = 'CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci';
    }
    if (!$changes) {
        echo "  [TABLE] {$table} sudah seragam. [SKIP]\n";
        continue;
    }
    $sql = "ALTER TABLE `{$table}` " . implode(', ', $changes);
    echo "  [TABLE] {$table}: {$sql} ";
    if ($dry_run) {
        echo "[DRY-RUN]\n";
    } else {
        if ($c->query($sql)) {
            echo "[BERJAYA]\n";
        } else {
            fwrite(STDERR, "[RALAT: " . $c->error . "] Perubahan sebelumnya mungkin sudah berjaya; semak skema sebelum cuba semula.\n");
            exit(1);
        }
    }
}

// 2. FASA 2: Pengoptimuman Indeks (Composite & Foreign Key Indexes)
echo "\n>>> FASA 2: Pengoptimuman & Penambahan Indeks Berprestasi Tinggi\n";

$indexes_to_add = [
    // Jadual: [Index Name, Columns, IsUnique]
    'access_level' => [
        ['name' => 'uq_acc_lvl', 'cols' => '(`acc_lvl`)', 'unique' => true]
    ],
    'app_info' => [
        ['name' => 'idx_app_status', 'cols' => '(`app_status`)', 'unique' => false]
    ],
    'area_daily_records' => [
        ['name' => 'idx_adr_dr_area', 'cols' => '(`dr_id`, `area_id`)', 'unique' => false],
        ['name' => 'idx_adr_cid_date', 'cols' => '(`cid`, `date`)', 'unique' => false]
    ],
    'attendance' => [
        ['name' => 'idx_att_date', 'cols' => '(`date`)', 'unique' => false]
    ],
    'contract' => [
        ['name' => 'idx_contract_number', 'cols' => '(`contract_number`(100))', 'unique' => false],
        ['name' => 'idx_contract_status_sz', 'cols' => '(`c_status`, `sz_id`)', 'unique' => false],
        ['name' => 'idx_contract_status_cid', 'cols' => '(`c_status`, `cid`)', 'unique' => false]
    ],
    'contractor_users' => [
        ['name' => 'uq_contractor_user', 'cols' => '(`contractor_id`, `user_id`)', 'unique' => true]
    ],
    'site_instruction' => [
        ['name' => 'idx_si_contract_date', 'cols' => '(`contract_id`, `date_issued`)', 'unique' => false]
    ],
    'staff_checkin' => [
        ['name' => 'idx_sc_location', 'cols' => '(`location_name`)', 'unique' => false]
    ],
    'user_log' => [
        ['name' => 'idx_ul_date', 'cols' => '(`log_date`)', 'unique' => false],
        ['name' => 'idx_ul_uid_date', 'cols' => '(`uid`, `log_date`)', 'unique' => false]
    ],
    'zone' => [
        ['name' => 'idx_zone_name', 'cols' => '(`zone_name`)', 'unique' => false]
    ]
];

// Dapatkan indeks sedia ada untuk setiap jadual
foreach ($indexes_to_add as $table => $idx_list) {
    // Semak indeks sedia ada
    $existing = [];
    $res = $c->query("SHOW INDEX FROM `{$table}`");
    if ($res) {
        while ($r = $res->fetch_assoc()) {
            $existing[$r['Key_name']] = true;
        }
    }

    foreach ($idx_list as $idx) {
        $idx_name = $idx['name'];
        if (isset($existing[$idx_name])) {
            echo "  [INDEX] {$table}.{$idx_name} sudah wujud. [SKIP]\n";
            continue;
        }

        $type = $idx['unique'] ? "ADD UNIQUE KEY `{$idx_name}`" : "ADD KEY `{$idx_name}`";
        $sql = "ALTER TABLE `{$table}` {$type} {$idx['cols']}";
        echo "  [INDEX] Menambah {$table}.{$idx_name} {$idx['cols']}... ";
        if ($dry_run) {
            echo "[DRY-RUN OK]\n";
        } else {
            if ($c->query($sql)) {
                echo "[BERJAYA]\n";
            } else {
                fwrite(STDERR, "[RALAT: " . $c->error . "] Perubahan sebelumnya mungkin sudah berjaya; semak skema sebelum cuba semula.\n");
                exit(1);
            }
        }
    }
}

// 3. FASA 3: OPTIMIZE TABLE & ANALYZE TABLE
echo "\n>>> FASA 3: Penjanaan Semula Statistik Pengoptimum (ANALYZE TABLE)\n";
foreach ($tables as $table) {
    echo "  [ANALYZE] {$table}... ";
    if ($dry_run) {
        echo "[DRY-RUN OK]\n";
    } else {
        $res = $c->query("ANALYZE TABLE `{$table}`");
        if ($res) {
            echo "[BERJAYA]\n";
        } else {
            fwrite(STDERR, "[RALAT: " . $c->error . "] Semak jadual {$table}.\n");
            exit(1);
        }
    }
}

echo "\n========================================================\n";
echo " Migrasi & Pengoptimuman Pangkalan Data Selesai!\n";
echo "========================================================\n";
