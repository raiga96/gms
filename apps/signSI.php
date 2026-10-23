<?php
/**
 * signSI.php
 * Mengemas kini status tandatangan digital (Digital Signature) bagi Site Instruction.
 * Menyokong peranan: CON (Contractor), SPV (Supervisor), PGS (Pengawas), dan SO (Superintending Officer).
 */

header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Origin: *');

// 1. Sambungan Database
require 'conn.php';

// 2. Terima Data POST
$si_no = $_POST['si_no'] ?? '';
$uid   = $_POST['uid'] ?? 0;
$type  = $_POST['type'] ?? ''; // CON, SPV, PGS, atau SO

if (empty($si_no) || empty($uid) || empty($type)) {
    echo json_encode(["status" => "error", "message" => "Parameter tidak lengkap."]);
    exit;
}

try {
    $now = date('Y-m-d H:i:s');
    $sql = "";
    
    // 3. Tentukan logik kemas kini berdasarkan jenis peranan
    // Nota: Nama lajur diselaraskan dengan skema pangkalan data anda.
    
    switch ($type) {
        case 'CON':
            // Contractor Sign (Lajur: con_dsign, date_con_dsign)
            $sql = "UPDATE site_instruction SET con_dsign = 'Y', date_con_dsign = ? WHERE num_si = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ss", $now, $si_no);
            break;

        case 'SPV':
            // Supervisor Sign (Lajur: spv_dsign, spv_id, date_spv_dsign)
            $sql = "UPDATE site_instruction SET spv_dsign = 'Y', spv_id = ?, date_spv_dsign = ? WHERE num_si = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("iss", $uid, $now, $si_no);
            break;

        case 'PGS':
            // Pengawas Sign (Lajur: pgs_dsign, pgs_id, date_pgs_dsign)
            $sql = "UPDATE site_instruction SET pgs_dsign = 'Y', pgs_id = ?, date_pgs_dsign = ? WHERE num_si = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("iss", $uid, $now, $si_no);
            break;

        case 'SO':
            // Superintending Officer Sign (Lajur: so_dsign, so_by, date_so_dsign)
            $sql = "UPDATE site_instruction SET so_dsign = 'Y', so_by = ?, date_so_dsign = ? WHERE num_si = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("iss", $uid, $now, $si_no);
            break;

        default:
            throw new Exception("Jenis tandatangan '$type' tidak dikenali.");
    }

    // 4. Laksanakan Query
    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            echo json_encode([
                "status" => "success", 
                "message" => "Tandatangan $type berjaya direkodkan bagi SI $si_no."
            ]);
        } else {
            echo json_encode([
                "status" => "error", 
                "message" => "Tiada rekod SI ditemui dengan nombor $si_no."
            ]);
        }
    } else {
        throw new Exception("Gagal mengemaskini database: " . $stmt->error);
    }

    $stmt->close();

} catch (Exception $e) {
    echo json_encode([
        "status" => "error", 
        "message" => $e->getMessage()
    ]);
}

// Tutup sambungan
$conn->close();
?>