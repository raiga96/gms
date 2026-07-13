<?php
require 'dompdf/vendor/autoload.php';
$id = $_GET['id'];

require 'includes/connection.php';

use Dompdf\Dompdf;
use Dompdf\Options;

// 1. Setting Options untuk Dompdf
$options = new Options();
$options->set('defaultFont', 'Poppins'); // Menggunakan font Courier sesuai FPDF awal
$options->set('isHtml5ParserEnabled', true);
$options->set('isRemoteEnabled', true);

$dompdf = new Dompdf($options);

// Fetch Main Data
$sql1 = "SELECT * FROM daily_records WHERE id = '$id'";
$res1 = $conn->query($sql1);
$row1 = $res1->fetch_assoc();
if (!$row1) { die("Record not found"); }

$sql2 = "SELECT * FROM contract WHERE contract_id = '" . $row1['cid'] . "'";
$res2 = $conn->query($sql2);
$row2 = $res2->fetch_assoc();
$contract_num = $row2['contract_number'] ?? '';
$sz_id = $row2['sz_id'] ?? '';

$sql3 = "SELECT * FROM subzone WHERE sz_id = '$sz_id'";
$res3 = $conn->query($sql3);
$row3 = $res3->fetch_assoc();
$zone_name = $row3['sz_name'] ?? '';
$zone_display = $contract_num . '-' . $zone_name;

$date_display = isset($row1['date']) ? date('d/m/y', strtotime($row1['date'])) : '';

// --- Fetch Site Instructions & Demerits grouped by area_id ---
$demerits_by_area = [];
$si_numbers = [];
$reference_by_area = [];

$sql_si = "SELECT 
    b.num_si,
    b.area_id,
    b.si_category,
    b.reference,
    b.instruction,
    COALESCE(sub_dps.total_demerit, 0) AS demerit_point_sebenar
FROM daily_records AS a 
JOIN site_instruction AS b ON b.ddId = a.id 
LEFT JOIN (
    SELECT 
        b_sub.si_id AS actual_id,
        SUM(c_sub.demerit_point) AS total_demerit
    FROM site_instruction AS b_sub
    JOIN dps AS c_sub ON 
        c_sub.dem_cate = b_sub.si_category 
        AND JSON_CONTAINS(
            b_sub.reference, 
            JSON_QUOTE(CONCAT(c_sub.ref, '(', c_sub.numb, ')')), 
            '$'
        )
    GROUP BY b_sub.si_id
) AS sub_dps ON sub_dps.actual_id = b.si_id
WHERE a.id = '$id'";

$res_si_all = $conn->query($sql_si);
$instructions = [];
if ($res_si_all && $res_si_all->num_rows > 0) {
    while ($row_si = $res_si_all->fetch_assoc()) {
        $demerits_by_area[$row_si['area_id']][$row_si['si_category']] = $row_si['demerit_point_sebenar'];
        $reference_by_area[$row_si['area_id']][$row_si['si_category']] = $row_si['reference'];
        if (!empty($row_si['num_si'])) {
            $si_numbers[] = $row_si['num_si'];
        }
        if (!empty($row_si['instruction'])) {
            $instructions[] = htmlspecialchars($row_si['instruction']);
        }
    }
}
$si_display = !empty($si_numbers) ? implode(', ', array_unique($si_numbers)) : '..........';
$comments_by_sor = !empty($instructions) ? implode('; ', array_unique($instructions)) : 'No comment from SOR';

// 2. Siapkan Struktur HTML & CSS
$html = '
<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <title>Site Daily Record</title>
    <style>
        @page {
            margin: 10mm 10mm 10mm 10mm;
        }
        body {
            font-family: "Courier", Courier, monospace;
            font-size: 11px;
            color: #000;
            line-height: 1.2;
        }
        .header { text-align: center; margin-bottom: 5px; }
        .header p { font-size: 18px; margin: 0; font-weight: bold; }
        .header h4 { font-size: 14px; margin: 2px 0; font-weight: bold; }
        
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #000; padding: 5px; vertical-align: middle; }
        .table-atas th, .table-atas td { border: none !important; padding-top: 0; padding-bottom: 0; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-left { text-align: left; }
        .font-bold { font-weight: bold; }
        .font-italic { font-style: italic; }

        .sesi-container { display: inline-block; width: 100%; }
        .inline-box { display: inline-block; width: 45px; height: 20px; border: 1px solid #000; vertical-align: middle; margin-right: 5px; }
        .sesi-text { display: inline-block; vertical-align: middle; }

        .sig-space { height: 45px; margin-top: 15px; font-style: italic; }
        .clear { clear: both; }

        /* Container Utama diperlebar menjadi 110px untuk memberi ruang teks kiri/kanan */
        .circle-container {
            position: relative;
            width: 110px;
            height: 90px;
            margin: 0 auto;
            box-sizing: border-box;
            /* background-color: rgba(0,0,0,0.05); */ /* Aktifkan ini untuk debug area jika perlu */
        }

        /* Lingkaran Utama (Tengah-tengah secara horizontal: (110 - 60) / 2 = 25px) */
        .main-circle {
            position: absolute;
            top: 15px;
            left: 25px; 
            width: 60px;
            height: 60px;
            border: 1.5px solid #1b1b1b; 
            border-radius: 50%;
            box-sizing: border-box;
        }

        /* Garis Vertikal (Tengah container: 110 / 2 = 55px, dikurangi setengah tebal garis) */
        .line-vertical {
            position: absolute;
            top: 15px;
            left: 54.25px; 
            width: 1.5px;
            height: 62px;
            background-color: #1b1b1b;
        }

        /* Garis Horizontal (Dimulai dari posisi left lingkaran) */
        .line-horizontal {
            position: absolute;
            top: 44.25px; 
            left: 25px;
            width: 62px;
            height: 1.5px;
            background-color: #1b1b1b;
        }

        /* Base Label Waktu */
        .time-label {
            position: absolute;
            color: #1b1b1b;
            font-weight: bold;
            font-size: 8px;
            font-family: Courier New, Courier, monospace;
            line-height: 1;
        }

        /* --- POSISI TEKS PERBAIKAN --- */
        
        /* Atas: Tengah sempurna */
        .t-0800 {
            top: 2px;
            left: 50%;
            transform: translateX(-50%);
        }

        /* Kanan: Ditempatkan relatif dari posisi kanan lingkaran (25px dari kanan luar) + jarak aman */
        .t-0900 {
            top: 50%;
            right: 0px; /* Diberi jarak 2px dari tepi container terluar agar tidak menyentuh lingkaran */
            transform: translateY(-50%);
        }

        /* Bawah: Tengah sempurna */
        .t-1200 {
            bottom: 0px;
            left: 50%;
            transform: translateX(-50%);
        }

        /* Kiri: Ditempatkan relatif dari tepi kiri container terluar */
        .t-1800 {
            top: 50%;
            left: 2px; 
            transform: translateY(-50%);
        }
    </style>
</head>
<body style="border: 1px solid #000;">

    <div class="header">
        <p>SITE DAILY RECORD</p>
        <h4>LANDSCAPE DIVISION</h4>
        <h4>QUALITY GRASSCUTTING & DRAIN CLEANING</h4>
    </div>
    <div style="margin-bottom:10px; padding:10px">
        <table style="border: none; margin: 0; padding: 0;">
            <tr style="border: none;">
                <td style="border: none; padding: 0;" class="text-left font-bold">
                    ZONE: <span style="font-weight: normal;">' . htmlspecialchars($zone_display) . '</span>
                </td>
                <td style="border: none; padding: 0;" class="text-right font-bold">
                    DATE: <span style="font-weight: normal;">' . $date_display . '</span>
                </td>
            </tr>
        </table>
    </div>

    <table class="table-atas">
        <thead>
            <tr class="font-bold" style="font-size: 10px;">
                <th style="width: 10px;padding-bottom:8px">DAY</th>
                <th style="text-align:left;padding-bottom:8px">GRASSCUTTING</th>
                <th style="text-align:left;padding-bottom:8px">DRAIN CLEANING</th>
            </tr>
        </thead>
        <tbody>';
        
        $hari = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
        $recordDay = isset($row1['date']) ? date('l', strtotime($row1['date'])) : '';
        $gcChecked = isset($row1['gc_checked']) ? (int)$row1['gc_checked'] : 0;
        $gcSession = isset($row1['gc_session']) ? $row1['gc_session'] : '';
        $gcDayNo   = isset($row1['gc_day_no']) ? $row1['gc_day_no'] : '';
        $dmChecked = isset($row1['dm_checked']) ? (int)$row1['dm_checked'] : 0;
        $dmSession = isset($row1['dm_session']) ? $row1['dm_session'] : '';
        $dmDayNo   = isset($row1['dm_day_no']) ? $row1['dm_day_no'] : '';

        foreach ($hari as $h) {
            $isMatch = (strtolower($h) === strtolower($recordDay));
            $gc_box = ($isMatch && $gcChecked === 1) ? 'X' : '';
            $gc_sess_val = $isMatch ? $gcSession : '......';
            $gc_day_val = $isMatch ? $gcDayNo : '......';
            
            $dm_box = ($isMatch && $dmChecked === 1) ? 'X' : '';
            $dm_sess_val = $isMatch ? $dmSession : '......';
            $dm_day_val = $isMatch ? $dmDayNo : '......';

            $html .= '
            <tr>
                <td class="text-right font-bold" style="font-size:9px">' . $h . '</td>
                <td>
                    <div class="sesi-container">
                        <div class="inline-box text-center" style="line-height:20px; font-weight:bold;font-size:9px">' . $gc_box . '</div>
                        <span class="sesi-text" style="font-size:9px">(Session ' . htmlspecialchars($gc_sess_val) . ') Day No : ' . htmlspecialchars($gc_day_val) . '</span>
                    </div>
                </td>
                <td>
                    <div class="sesi-container">
                        <div class="inline-box text-center" style="line-height:20px; font-weight:bold;font-size:9px">' . $dm_box . '</div>
                        <span class="sesi-text" style="font-size:9px">(Session ' . htmlspecialchars($dm_sess_val) . ') Day No : ' . htmlspecialchars($dm_day_val) . '</span>
                    </div>
                </td>
            </tr>';
        }
        
$html .= '
        </tbody>
    </table>
    <div style="height:10px"></div>
    <table>
        <thead>
            <tr class="font-bold" style="font-size: 8px;">
                <th style="width: 8px; border-left:none">ACTIVITIES</th>
                <th style="width: 10px;" class="text-center">NOS. WORKERS</th>
                <th style="width: 10px;" class="text-center">MACHINES/TOOLS</th>
                <th style="width: 30px;">TIME</th>
                <th style="width: 150px; border-right:none">WEATHER</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="font-bold" style="border-left:none;font-size:9px">GRASSCUTTING</td>
                <td class="text-center" style="font-size:9px">' . htmlspecialchars($row1['gc_workers'] ?? '') . '</td>
                <td class="text-center" style="font-size:9px">' . htmlspecialchars($row1['gc_tools'] ?? '') . '</td>
                <td style="font-size:9px">Datang: --:--</td>
                <td class="text-center" rowspan="3" style="font-size: 9px; border-right:none">
                <div align="center" style="margin-bottom:5px">
                    <div class="circle-container">
                        <div class="time-label t-0800">0800</div>
                        <div class="time-label t-0900">0900</div>
                        <div class="time-label t-1200">1200</div>
                        <div class="time-label t-1800">1800</div>

                        <div class="main-circle"></div>
                        <div class="line-vertical"></div>
                        <div class="line-horizontal"></div>
                    </div>
                </div>';
                    
                    $weather = isset($row1['weather']) ? $row1['weather'] : '';
                    $html .= 'Weather: <br>';
                    $html .= ($weather == 'Cloudy' ? '[ X ] Cloudy &nbsp;&nbsp;&nbsp;' : '[  ] Cloudy &nbsp;&nbsp;&nbsp;');
                    $html .= ($weather == 'Rainy' ? '[ X ] Rainy &nbsp;&nbsp;&nbsp;' : '[  ] Rainy &nbsp;&nbsp;&nbsp;');
                    $html .= ($weather == 'Fine' ? '[ X ] Fine<br>' : '[  ] Fine<br>');
                    $html .= '<br>(Time of inspection)<br>AM: --:--&nbsp;&nbsp;&nbsp;&nbsp;PM: --:--';

$html .= '      </td>
            </tr>
            <tr>
                <td class="font-bold" style="border-left:none;font-size:9px">DRAIN CLEANING</td>
                <td class="text-center" style="font-size:9px">' . htmlspecialchars($row1['dc_workers'] ?? '') . '</td>
                <td class="text-center" style="font-size:9px">' . htmlspecialchars($row1['dc_tools'] ?? '') . '</td>
                <td style="font-size:9px">Pulang: --:--</td>
            </tr>
            <tr class="font-bold">
                <td style="border-left:none;font-size:9px">TOTAL</td>
                <td class="text-center" style="font-size:9px">' . ((int)($row1['gc_workers'] ?? 0) + (int)($row1['dc_workers'] ?? 0)) . '</td>
                <td class="text-center" style="font-size:9px"></td>
                <td style="font-size:9px"></td>
            </tr>
        </tbody>
    </table>
    <div style="height:5px;"></div>
    <table>
        <thead>
            <tr class="font-bold text-center" style="font-size: 9px;">
                <th style="width: 1%; border-left:none">AREA</th>
                <th style="width: 10%;">LOCATION</th>
                <th style="width: 2%;">ACTIVITY</th>
                <th style="width: 2%;">PROGRESS</th>
                <th style="width: 16%;">REFERENCE</th>
                <th style="width: 5%; border-right:none">TOTAL DEMERIT</th>
            </tr>
        </thead>
        <tbody>';

        $sql_loc = "SELECT * FROM contract_location WHERE sz_id = '$sz_id'";
        $res_loc = $conn->query($sql_loc);
        if ($res_loc && $res_loc->num_rows > 0) {
            while ($row_loc = $res_loc->fetch_assoc()) {
                $area_no = $row_loc['area'];
                $locId = $row_loc['locId'];
                
                $sql_adr = "SELECT * FROM area_daily_records WHERE dr_id = '$id' AND area_id = '$area_no'";
                $res_adr = $conn->query($sql_adr);
                $row_adr = $res_adr->fetch_assoc();
                
                $status_map = ['0' => 'X','1' => '-','2' => '/','3' => 'O'];
                $gc_progress = (isset($row_adr['gc_progress']) && array_key_exists($row_adr['gc_progress'], $status_map)) ? $status_map[$row_adr['gc_progress']] : '';
                $dc_progress = (isset($row_adr['dc_progress']) && array_key_exists($row_adr['dc_progress'], $status_map)) ? $status_map[$row_adr['dc_progress']] : '';
                
                $demerit_val_gc = (isset($demerits_by_area[$locId]['GC']) && $demerits_by_area[$locId]['GC'] > 0) ? $demerits_by_area[$locId]['GC'] : '';
                $demerit_val_dc = (isset($demerits_by_area[$locId]['DC']) && $demerits_by_area[$locId]['DC'] > 0) ? $demerits_by_area[$locId]['DC'] : '';

                $raw_gc = $reference_by_area[$locId]['GC'] ?? '';
                $raw_dc = $reference_by_area[$locId]['DC'] ?? '';

                $clean_format = function($data) {
                    $array = json_decode($data, true);
                    if (is_array($array) && !empty($array)) { return implode(', ', $array); }
                    return (!empty($data) && $data !== '-') ? $data : '';
                };

                $ref_gc = $clean_format($raw_gc);
                $ref_dc = $clean_format($raw_dc);

                $html .= '
                <tr>
                    <td class="text-center font-bold" rowspan="2" style="border-left:none">' . htmlspecialchars($area_no) . '</td>
                    <td rowspan="2" style="font-size: 9px; vertical-align: top;">' . htmlspecialchars($row_loc['location']) . '</td>
                    <td style="font-size:9px">Grasscutting</td>
                    <td class="text-center font-bold">' . htmlspecialchars($gc_progress) . '</td>
                    <td class="text-center">' . htmlspecialchars($ref_gc) . '</td>
                    <td class="text-center" style="border-right:none">' . htmlspecialchars($demerit_val_gc) . '</td>
                </tr>
                <tr>
                    <td class="text-center" style="font-size:9px">Drain Cleaning</td>
                    <td class="text-center font-bold">' . htmlspecialchars($dc_progress) . '</td>
                    <td class="text-center">' . htmlspecialchars($ref_dc) . '</td>
                    <td class="text-center" style="border-right:none">' . htmlspecialchars($demerit_val_dc) . '</td>
                </tr>';
            }
        }

$html .= '
        </tbody>
    </table>
    <table>
        <tr>
            <td style="border-top: none;border-bottom: none; border-right: none; border-left: none; font-size:9px;padding-top:8px;padding-bottom:8px">
                Instruction to contractor :
            </td>
            <td style="border-top: none;border-bottom: none; border-right: none; border-left: none; font-size:9px;padding-top:8px;padding-bottom:8px">
                SI.No. ' . $si_display . ' / Lisan
            </td>
            <td style="border-top: none;border-bottom: none; border-left: none; border-right: none; font-size:9px;padding-top:8px;padding-bottom:8px">
                <div class="sesi-container">
                    <span class="sesi-text">No</span>
                    <div class="inline-box text-center" style="line-height:20px; font-weight:bold;">' . (empty($si_numbers) ? 'X' : '') . '</div>
                </div>
            </td>
        </tr>
    </table>
    <table>
        <tr>
            <td style="border-bottom: none; border-left: none; border-right: none;">
            <p class="font-italic" style="font-size: 8px; margin: 2px 0;">Nota: Wakil kontraktor hendaklah menyemak dan memahami catatan kerja sebelum menurunkan tanda-tangan.</p>
            </td>
        </tr>
    </table>
    <table>
        <tr>
            <td style="border-left:none">
                <div>
                    <div class="text-center" style="font-size: 8px; font-weight:bold;">Contractor\'s Representative</div>
                    <div class="text-center" style="font-size: 8px;">Signature</div>
                    <div class="sig-space text-center">';
                    
                    if (isset($row1['contractor_dsign']) && $row1['contractor_dsign'] === 'Y') {
                        $html .= '<span style="font-weight:bold; color: #059669;">Telah Ditandatangani Secara Digital</span>';
                    } else {
                        $html .= '[ TANDATANGAN ]';
                    }

$html .= '          </div>
                    <div class="text-center" style="font-size: 8px;">(MOHD AZREEN)</div>
                </div>
            </td>
            <td >
                <div>
                    <div class="text-center" style="font-size: 8px; font-weight:bold;">Superintending Officer</div>
                    <div class="text-center" style="font-size: 8px;">Representative (Superior)</div>
                    <div class="sig-space text-center">';
                    
                    if (isset($row1['supervisor_dsign']) && $row1['supervisor_dsign'] === 'Y') {
                        $html .= '<span style="font-weight:bold; color: #059669;">Telah Ditandatangani Secara Digital</span>';
                    } else {
                        $html .= '[ TANDATANGAN ]';
                    }

$html .= '          </div>
                    <div class="text-center" style="font-size: 8px;">(WILSON ASOI)</div>
                </div>
            </td>
            <td style="width:200px; border-right:none">
                <div>
                    <div class="text-center" style="font-size: 8px; font-weight:bold;">Superintending Officer</div>
                    <div class="text-center" style="font-size: 8px;">Representative (Verifier)</div>
                    <div class="sig-space text-center">';
                    
                    if (isset($row1['pengawas_dsign']) && $row1['pengawas_dsign'] === 'Y') {
                        $html .= '<span style="font-weight:bold; color: #059669;">Telah Ditandatangani Secara Digital</span>';
                    } else {
                        $html .= '[ TANDATANGAN ]';
                    }

$html .= '          </div>
                    <div class="text-center" style="font-size: 8px;">(MOHD KHAIRULAIMAN / MOHD SYAMIM SIDDIQ)</div>
                </div>
            </td>
        </tr>
        <tr>
            <td colspan="3" style="border-right:none; border-left:none; border-bottom:none; padding:5;">
                <div style="display: inline-block; width: 45%; vertical-align: top; box-sizing: border-box;">
                    <span style="font-weight:bold; font-size:8px; display: block; margin-bottom: 2px;">PROGRESS (of work):</span>
                    <span style="padding-left:5px; font-size:8px; display: block;">X : Belum dijalankan</span>
                    <span style="padding-left:5px; font-size:8px; display: block;">- : Sedang dijalankan</span>
                    <span style="padding-left:5px; font-size:8px; display: block;">/ : Siap dijalankan</span>
                    <span style="padding-left:5px; font-size:8px; display: block;">O : Tidak berterusan / Tiada Aktiviti</span>
                </div>

                <div style="display: inline-block; width: 50%; vertical-align: top; box-sizing: border-box; padding-left: 10px; padding-top:10px">
                    <span style="font-size:8px; font-weight:bold; display: block; margin-bottom: 4px;">
                        Comments By ASO : ___________________________________________________
                    </span>
                    <span style="font-size:8px; font-weight:bold; display: block; margin-bottom: 4px;">
                        (JA/KU/KB)
                    </span>
                    <span style="font-size:8px; font-weight:bold; display: block;">
                        (if any) ____________________________________________________________
                    </span>
                </div>
            </td>
    </tr>
    <tr>
        <td colspan="2" style="border-right:none; border-left:none; border-top:none; border-bottom:none; padding:0;">
            <span style="padding-left:15px; padding-top:5px; font-size:8px;"> * Semua kawasan tersebut termasuk "Backhoe","Road Vergas", "Island", sempadan tanah persendirian, </span>
            <span style="padding-left:15px; padding-top:5px; font-size:8px;">tanah rezab Kerajaan, Jalan-jalan yang tiada tanda nama dan lain-lain seperti dalam sempadan kontrak.</span>
        </td>
    </tr>
    </table>
    <div class="clear"></div>
</body>
</html>
';

// 3. Load HTML ke Dompdf dan Render
$dompdf->loadHtml($html);

// Set ukuran kertas A4 Portrait
$dompdf->setPaper('A4', 'portrait');

$dompdf->render();

// 4. Output ke Browser secara Inline
header('Content-Type: application/pdf');
echo $dompdf->output();