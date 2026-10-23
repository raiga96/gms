<?php
header('Content-Type: application/json');
require 'conn.php';

$id = $_GET['id'] ?? null;

if (!$id) {
    echo json_encode(["error" => "Missing id"]);
    exit;
}

// 1. GET FROM DB
$sql = "SELECT * FROM kmz_file WHERE sz_id = '$id' LIMIT 1";
$result = $conn->query($sql);

if ($result->num_rows == 0) {
    echo json_encode(["error" => "File not found"]);
    exit;
}

$row = $result->fetch_assoc();

// 🔥 BASE URL KMZ FOLDER
$baseUrl = "https://jts.sarawak.gov.my/dbku/kmz/";

// kalau DB hanya simpan filename
$fileUrl = $baseUrl . $row['kmz_file'];

// 2. DOWNLOAD KMZ
$url = $row['url'];

$arrContextOptions = [
    "ssl" => [
        "verify_peer" => false,
        "verify_peer_name" => false,
    ],
];

$fileContent = file_get_contents(
    $url,
    false,
    stream_context_create($arrContextOptions)
);

if ($fileContent === false) {
    echo json_encode(["error" => "Failed to download KMZ"]);
    exit;
}

$tempFile = tempnam(sys_get_temp_dir(), 'kmz_');
file_put_contents($tempFile, $fileContent);

// 3. OPEN KMZ
$zip = new ZipArchive;

if ($zip->open($tempFile) !== TRUE) {
    echo json_encode(["error" => "Cannot open KMZ"]);
    exit;
}

$extractPath = sys_get_temp_dir() . '/kmz_' . uniqid();
mkdir($extractPath);

$zip->extractTo($extractPath);
$zip->close();

// 4. FIND KML
$kmlFile = null;

foreach (scandir($extractPath) as $file) {
    if (pathinfo($file, PATHINFO_EXTENSION) == 'kml') {
        $kmlFile = $extractPath . '/' . $file;
        break;
    }
}

if (!$kmlFile) {
    echo json_encode(["error" => "KML not found"]);
    exit;
}

// 5. PARSE KML
$xml = simplexml_load_file($kmlFile);
$xml->registerXPathNamespace('k', 'http://www.opengis.net/kml/2.2');

$polygons = [];
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

// 6. CLEAN
unlink($tempFile);

function deleteDir($dir)
{
    if (!file_exists($dir)) return;
    foreach (scandir($dir) as $file) {
        if ($file == '.' || $file == '..') continue;
        $path = "$dir/$file";
        is_dir($path) ? deleteDir($path) : unlink($path);
    }
    rmdir($dir);
}

deleteDir($extractPath);

// 7. OUTPUT
echo json_encode([
    "status" => "success",
    "file" => $fileUrl,
    "polygons" => $polygons
]);

$conn->close();
