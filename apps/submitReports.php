<?php

/**
 * submitReports.php
 * Berjaya diubah suai ke MySQLi biasa, membaiki isu data terbalik,
 * dan bersedia untuk pemrosesan locations_data.
 */

header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// 1. Panggil fail sambungan MySQLi korang (menggunakan $conn)
require 'conn.php';

// ─── HELPER ──────────────────────────────────────────────────────────────────
function clean($value)
{
    return htmlspecialchars(strip_tags(trim($value ?? '')));
}

function respond($status, $message, $extra = [])
{
    echo json_encode(array_merge(['status' => $status, 'message' => $message], $extra));
    exit;
}

// ─── HANYA TERIMA POST ─────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respond('error', 'Method not allowed.');
}

// ─── BACA FIELDS DARI FLUTTER ─────────────────────────────────────────────────
$cid     = clean($_POST['mid']     ?? ''); // di dalam android studio mid ialah cid
$mid     = clean($_POST['cid']     ?? ''); // di dalam android studio cid ialah mid
$date    = clean($_POST['date']    ?? date('Y-m-d'));
$weather = clean($_POST['weather'] ?? '');

// Tab data (session / day_no)
$gc_session  = clean($_POST['gc_session']  ?? '');
$gc_day_no   = clean($_POST['gc_day_no']   ?? '');
$gc_checked  = clean($_POST['gc_checked']  ?? '0');

$dm_session  = clean($_POST['dm_session']  ?? '');
$dm_day_no   = clean($_POST['dm_day_no']   ?? '');
$dm_checked  = clean($_POST['dm_checked']  ?? '0');

// Time of Inspectio
$toi_in  = clean($_POST['time_am'] ?? '');
$toi_out = clean($_POST['time_pm'] ?? '');

// Grass Cutting
$gc_workers   = clean($_POST['gc_workers']   ?? '');
$gc_tools     = clean($_POST['gc_tools']     ?? '');
$gc_check_in  = clean($_POST['gc_check_in']  ?? '');
$gc_check_out = clean($_POST['gc_check_out'] ?? '');

// Daily Maintenance – Drain Cleaning
$dc_workers  = clean($_POST['dc_workers']  ?? '0');
$dc_tools    = clean($_POST['dc_tools']    ?? '');
$dc_comment  = clean($_POST['dc_comment']  ?? '');

// Tangkap lokasi data (JSON String dari Flutter)
$locations_data = $_POST['locations_data'] ?? '';

// ─── SEMAKAN ASAS ─────────────────────────────────────────────────────────────
if (empty($cid)) {
    respond('error', 'Contract ID (cid) diperlukan.');
}

// SEMAKAN PERTAMA
if (empty($gc_workers)) {
    respond('error', 'Grass Cutting (Nos. of Worker) is empty');
}

// ─── SEMAKAN DATA PENDUA (DUPLICATION CHECK) ──────────────────────────────────
// Guna Prepared Statement untuk langkah keselamatan maksimum
$sqlCheck = "SELECT id FROM daily_records WHERE cid = ? AND date = ?";
if ($stmtCheck = $conn->prepare($sqlCheck)) {
    $stmtCheck->bind_param("ss", $cid, $date);
    $stmtCheck->execute();
    $stmtCheck->store_result();

    // Semak jika bilangan baris (num_rows) melebihi 0
    if ($stmtCheck->num_rows > 0) {
        $stmtCheck->close();
        // Ayat professional pilihan Senior Developer
        respond('error', "Daily record already exist in the system");
    }
    $stmtCheck->close();
}


// ─── INSERT GUNA MYSQLI PREPARED STATEMENT ────────────────────────────────────
$sql = "INSERT INTO daily_records (
            cid, mid, date, weather,
            gc_session, gc_day_no, gc_checked,
            dm_session, dm_day_no, dm_checked,
            gc_workers, gc_tools, gc_check_in, gc_check_out,
            dc_workers, dc_tools, dc_comment,
            created_at
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";

if ($stmt = $conn->prepare($sql)) {

    // Ikat parameter mengikut turutan jenis data (s = string)
    // Di sini ada 17 strings yang perlu diikat
    $stmt->bind_param(
        "sssssssssssssssss",
        $cid,
        $mid,
        $date,
        $weather,
        $gc_session,
        $gc_day_no,
        $gc_checked,
        $dm_session,
        $dm_day_no,
        $dm_checked,
        $gc_workers,
        $gc_tools,
        $gc_check_in,
        $gc_check_out,
        $dc_workers,
        $dc_tools,
        $dc_comment
    );

    if ($stmt->execute()) {
        $insert_id = $conn->insert_id; // Ini adalah dr_id (Daily Record ID)

        // ─── PROSES DATA LOKASI (area_daily_records) ──────────────────────────
        if (!empty($locations_data)) {
            $locations = json_decode($locations_data, true);

            if (is_array($locations) && !empty($locations)) {
                // Sediakan query untuk table area_daily_records
                $loc_sql = "INSERT INTO area_daily_records (
                                dr_id, cid, area_id, 
                                gc_progress, dc_progress, 
                                gc_comment, dc_comment, 
                                date, created_date
                            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())";

                if ($loc_stmt = $conn->prepare($loc_sql)) {

                    foreach ($locations as $loc) {
                        $area_id     = clean($loc['area_id'] ?? '');
                        $raw_gc      = $loc['gc_progress'] ?? '';
                        $raw_dc      = $loc['dc_progress'] ?? '';
                        $gc_comment  = clean($loc['gc_comment'] ?? '');
                        $dc_comment  = clean($loc['dc_comment'] ?? '');

                        // ─── LOGIK PENUKARAN TEKS KEPADA NOMBOR (MAPPING) ───
                        // Menukarkan string pilihan dari Flutter kepada integer (0-3)

                        // Pilihan untuk Grass Cutting Progress
                        switch ($raw_gc) {
                            case 'X - Belum Dijalankan':
                                $gc_progress = 0;
                                break;
                            case '- - Sedang Dijalankan':
                                $gc_progress = 1;
                                break;
                            case '✓ - Siap Dijalankan':
                                $gc_progress = 2;
                                break;
                            case 'O - Tidak berterusan/Tiada Aktiviti':
                                $gc_progress = 3;
                                break;
                            default:
                                $gc_progress = 0; // Nilai lalai (fallback) jika kosong
                                break;
                        }

                        // Pilihan untuk Drain Cleaning Progress
                        switch ($raw_dc) {
                            case 'X - Belum Dijalankan':
                                $dc_progress = 0;
                                break;
                            case '- - Sedang Dijalankan':
                                $dc_progress = 1;
                                break;
                            case '✓ - Siap Dijalankan':
                                $dc_progress = 2;
                                break;
                            case 'O - Tidak berterusan/Tiada Aktiviti':
                                $dc_progress = 3;
                                break;
                            default:
                                $dc_progress = 0; // Nilai lalai (fallback) jika kosong
                                break;
                        }

                        // Ikat parameter untuk setiap kawasan/lokasi
                        // ssssssss = 8 data jenis string/integer
                        $loc_stmt->bind_param(
                            "iiisssss",
                            $insert_id,   // Masuk ke dr_id
                            $cid,         // Masuk ke cid
                            $area_id,     // Masuk ke area_id
                            $gc_progress, // Masuk ke gc_progress
                            $dc_progress, // Masuk ke dc_progress
                            $gc_comment,  // Masuk ke gc_comment
                            $dc_comment,  // Masuk ke dc_comment
                            $date         // Masuk ke date
                        );

                        $loc_stmt->execute();
                    }
                    $loc_stmt->close();
                }
            }
        }

        $stmt->close();
        $conn->close();

        // Berjaya! Pulangkan respon kejayaan ke Flutter
        respond('success', 'Daily record submitted successfully.', ['record_id' => $insert_id]);
    } else {
        respond('error', 'Gagal menyimpan rekod utama: ' . $stmt->error);
    }
} else {
    respond('error', 'Ralat pada penyediaan query SQL: ' . $conn->error);
}
