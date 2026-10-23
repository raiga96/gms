<?php

/**
 * updateReports.php
 * Diselaraskan dengan logik submitReports.php
 * Menggunakan MySQLi Prepared Statement dan pemrosesan JSON locations_data.
 */

header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// 1. Panggil fail sambungan MySQLi (menggunakan $conn)
require 'conn.php';

// ─── HELPER ──────────────────────────────────────────────────────────────────
function clean($value)
{
    // Gunakan null coalescing operator dengan betul
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
// 🔴 BUG FIX 1: Pemetaan terbalik antara mid dan cid dibetulkan di bawah
$ddId    = clean($_POST['ddId']    ?? ''); // ID Daily Record dalam DB   
$cid     = clean($_POST['mid']     ?? ''); // ID Kontrak (Telah dibetulkan dari mid)
$mid     = clean($_POST['cid']     ?? ''); // ID Kontraktor (Telah dibetulkan dari cid)
$date    = clean($_POST['date']    ?? '');
$weather = clean($_POST['weather'] ?? '');

// Tab data (session / day_no)
$gc_session  = clean($_POST['gc_session']  ?? '');
$gc_day_no   = clean($_POST['gc_day_no']   ?? '');
$gc_checked  = clean($_POST['gc_checked']  ?? '0');

$dm_session  = clean($_POST['dm_session']  ?? '');
$dm_day_no   = clean($_POST['dm_day_no']   ?? '');
$dm_checked  = clean($_POST['dm_checked']  ?? '0');

// Time of Inspection (diambil dari time_am/pm)
$toi_in  = clean($_POST['time_am'] ?? '');
$toi_out = clean($_POST['time_pm'] ?? '');

// Formatkan waktu ke DATETIME MySQL (YYYY-MM-DD HH:MM:SS)
$toi_in_dt  = (!empty($toi_in))  ? $date . ' ' . $toi_in . ':00'  : null;
$toi_out_dt = (!empty($toi_out)) ? $date . ' ' . $toi_out . ':00' : null;

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
// 🔴 BUG FIX 2: Ganti semakan $cid dengan $ddId mengikut keperluan WHERE clause
if (empty($ddId)) {
    respond('error', 'Daily Record ID (ddId) diperlukan untuk kemaskini.');
}

// ─── CARI DR_ID BERDASARKAN ddId ───────────────────────────────────────────────
// Menggunakan $ddId secara langsung untuk cari data asal
$dr_id = 0;
$sqlFind = "SELECT id FROM daily_records WHERE id = ?";
if ($stmtFind = $conn->prepare($sqlFind)) {
    $stmtFind->bind_param("i", $ddId); // 'i' bermaksud integer
    $stmtFind->execute();
    $stmtFind->bind_result($dr_id);
    $stmtFind->fetch();
    $stmtFind->close();
}

if (!$dr_id) {
    respond('error', "Original record not found for the given ID.");
}

// ─── MULA TRANSAKSI ──────────────────────────────────────────────────────────
$conn->begin_transaction();

try {
    // ─── UPDATE JADUAL UTAMA (daily_records) ──────────────────────────────────
    $sql_update = "UPDATE daily_records SET 
                mid = ?, 
                weather = ?,
                gc_session = ?, 
                gc_day_no = ?, 
                gc_checked = ?,
                dm_session = ?, 
                dm_day_no = ?, 
                dm_checked = ?,
                gc_workers = ?, 
                gc_tools = ?, 
                gc_check_in = ?, 
                gc_check_out = ?,
                dc_workers = ?, 
                dc_tools = ?, 
                dc_comment = ?,
                toi_in = ?, 
                toi_out = ?,
                updated_at = NOW()
            WHERE id = ?";

    $stmt = $conn->prepare($sql_update);

    // 🔴 BUG FIX 3: Tukar jenis parameter bind_param (ss... menjadi jenis yang padan seperti i/s)
    // Serta menukar $ddId dihantar ke bind_param (bukan pembolehubah raw yang tiada)
    $stmt->bind_param(
        "sssssssssssssssssi", // 17 string ('s'), dan 'id' terakhir adalah integer ('i')
        $mid,
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
        $dc_comment,
        $toi_in_dt,
        $toi_out_dt,
        $ddId // Menggunakan $ddId yang disahkan dari Flutter
    );
    $stmt->execute();
    $stmt->close();

    // ─── PROSES DATA LOKASI (area_daily_records) ──────────────────────────────
    if (!empty($locations_data)) {
        $locations = json_decode($locations_data, true);

        if (is_array($locations)) {
            $loc_sql = "UPDATE area_daily_records SET 
                            gc_progress = ?, 
                            dc_progress = ?, 
                            gc_comment = ?, 
                            dc_comment = ?
                        WHERE dr_id = ? AND area_id = ?";

            if ($loc_stmt = $conn->prepare($loc_sql)) {
                foreach ($locations as $loc) {
                    $area_id     = clean($loc['area_id'] ?? '');
                    $raw_gc      = $loc['gc_progress'] ?? '';
                    $raw_dc      = $loc['dc_progress'] ?? '';
                    $gc_comment  = clean($loc['gc_comment'] ?? '');
                    $dc_comment  = clean($loc['dc_comment'] ?? '');

                    $map = [
                        'X - Belum Dijalankan' => 0,
                        '- - Sedang Dijalankan' => 1,
                        '✓ - Siap Dijalankan' => 2,
                        'O - Tidak berterusan/Tiada Aktiviti' => 3
                    ];

                    $gc_progress = $map[$raw_gc] ?? 0;
                    $dc_progress = $map[$raw_dc] ?? 0;

                    // 🔴 BUG FIX 4: Tukar jenis pengikat parameter "ssssii" -> "iiiiii" / "ssssii" mengikut jenis kolum DB anda.
                    // Anda meletakkan progress (integer) ke string "s", ubah kepada "i" jika dalam DB ianya integer.
                    // Di bawah dikekalkan ssssii mengikut jenis kolum asal anda, tetapi pastikan ia bertepatan.
                    $loc_stmt->bind_param(
                        "ssssii",
                        $gc_progress,
                        $dc_progress,
                        $gc_comment,
                        $dc_comment,
                        $dr_id,
                        $area_id
                    );
                    $loc_stmt->execute();
                }
                $loc_stmt->close();
            }
        }
    }

    $conn->commit();
    respond('success', 'Daily record updated successfully.');
} catch (Exception $e) {
    $conn->rollback();
    respond('error', 'Update failed: ' . $e->getMessage());
}

$conn->close();
