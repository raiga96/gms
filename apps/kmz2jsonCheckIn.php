<?php
header('Content-Type: application/json');
require 'conn.php';

$id = $_GET['id'] ?? null;

if (!$id) {
    echo json_encode(["error" => "Missing id"]);
    exit;
}
$s = "SELECT * FROM users WHERE uid = ?";
$stmt = $conn ->prepare($s);
$stmt ->bind_param("s", $id);
$stmt ->execute();
$result = $stmt ->get_result();
$row = $result ->fetch_assoc();
$usertype = $row['user_type'];


if($usertype == "CON" ){
// 1. GET FROM DB (Guna Prepared Statement untuk elak SQL Injection)
$sql = "SELECT a.contract_id, a.contract_number, e.sz_name, b.kmz_file, b.url 
        FROM contract AS a 
        JOIN kmz_file AS b ON b.sz_id = a.sz_id 
        JOIN contractor AS c ON c.cid = a.cid 
        JOIN users AS d ON d.uid = c.uid 
        JOIN subzone AS e ON e.sz_id = a.sz_id 
        WHERE d.uid = ?";
}else{
    $sql = "SELECT a.contract_id, e.sz_name, a.contract_number, b.kmz_file, b.url 
        FROM contract AS a 
        JOIN kmz_file AS b ON b.sz_id = a.sz_id
        JOIN users AS d ON d.uid = a.spv_id OR d.uid = a.pgs_id 
        JOIN subzone AS e ON e.sz_id = a.sz_id
        WHERE d.uid = ?";
}

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    echo json_encode(["error" => "No active contracts or KMZ files found for this user"]);
    exit;
}

// 🔥 BASE URL KMZ FOLDER
$baseUrl = "https://jts.sarawak.gov.my/dbku/kmz/";
$arrContextOptions = [
    "ssl" => [
        "verify_peer" => false,
        "verify_peer_name" => false,
    ],
];

// Array besar untuk simpan semua hasil kontrak & polygon
$all_contracts = [];

// Fungsi bantuan untuk padam folder temporary
function deleteDir($dir) {
    if (!file_exists($dir)) return;
    foreach (scandir($dir) as $file) {
        if ($file == '.' || $file == '..') continue;
        $path = "$dir/$file";
        is_dir($path) ? deleteDir($path) : unlink($path);
    }
    rmdir($dir);
}

// 2. LOOP SETIAP BARIS KONTRAK (MULTIPLE KMZ)
while ($row = $result->fetch_assoc()) {
    $contractId = $row['contract_id'];
    $contractName = $row['contract_number'];
    $sz_name = $row['sz_name'];
    $fileUrl = $baseUrl . $row['kmz_file'];
    $url = $row['url'];
    
    $polygons = [];

    // 3. DOWNLOAD KMZ
    $fileContent = file_get_contents($url, false, stream_context_create($arrContextOptions));
    
    if ($fileContent === false) {
        // Jika satu fail gagal download, skip fail ini atau log ralat
        continue; 
    }

    $tempFile = tempnam(sys_get_temp_dir(), 'kmz_');
    file_put_contents($tempFile, $fileContent);

    // 4. OPEN & EXTRACT KMZ
    $zip = new ZipArchive;
    if ($zip->open($tempFile) === TRUE) {
        $extractPath = sys_get_temp_dir() . '/kmz_' . uniqid();
        mkdir($extractPath);
        $zip->extractTo($extractPath);
        $zip->close();

        // 5. FIND KML
        $kmlFile = null;
        foreach (scandir($extractPath) as $file) {
            if (pathinfo($file, PATHINFO_EXTENSION) == 'kml') {
                $kmlFile = $extractPath . '/' . $file;
                break;
            }
        }

        // 6. PARSE KML JIKA DIJUMPAI
        if ($kmlFile) {
            $xml = simplexml_load_file($kmlFile);
            $xml->registerXPathNamespace('k', 'http://www.opengis.net/kml/2.2');
            $coordinates = $xml->xpath('//k:coordinates');

            foreach ($coordinates as $block) {
                $points = [];
                $lines = explode(" ", trim((string)$block));

                foreach ($lines as $line) {
                    $parts = explode(",", trim($line));
                    if (count($parts) >= 2) {
                        $points[] = [
                            "lng" => (float)$parts[0],
                            "lat" => (float)$parts[1],
                        ];
                    }
                }
                if (!empty($points)) {
                    $polygons[] = $points;
                }
            }
        }

        // CLEAN FOLDER & FILE LOGIKAL UNTUK LOOP INI
        deleteDir($extractPath);
    }
    
    unlink($tempFile);

    // 7. SIMPAN DATA KONTRAK SELESAI DIPROSES KE ARRAY BESAR
    $all_contracts[] = [
        "contract_id" => $contractId,
        "sz_name" => $sz_name,
        "contract_name" => $contractName,
        "file_url" => $fileUrl,
        "polygons" => $polygons
    ];
}

// 8. OUTPUT JSON BERFORMAT BARU
echo json_encode([
    "status" => "success",
    "total_contracts" => count($all_contracts),
    "contracts" => $all_contracts
]);

$stmt->close();
$conn->close();
?>