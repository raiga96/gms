<?php 
require 'connect.php';

$timer=date('H:i:s');


  //redirect if hour between 8:00 to 21:00
  if( (($timer > "07:30:00")  &&  ($timer < "09:30:00")) || (($timer > "16:30:00")  &&  ($timer < "17:30:00")) ) {
    $data = array(
        // "hadir_bsu" => $BSUPerHadir,
      "status" => 'high',
        "hadir" => '0',
        "ponteng" => '0',
        "myhadir" => '0',
        "exception" => '0',
        "level" => '',
        "BSU" => '0',
        "div" => '0',

    );
    
    // Convert the data to JSON format
    $jsonData = json_encode($data);

    // Set the Content-Type header to indicate that the response contains JSON data
    header('Content-Type: application/json');

    // Output the JSON data
    //print_r($jsonData);
    print_r($jsonData);
  }else{

$currentY = date('Y');

$sql = "SELECT * FROM users as a join userinfo as b on b.uid = a.uid JOIN access_level as c on c.acc_lvl = a.access_level JOIN brasecunit AS d ON d.BSU_id = b.section JOIN division AS e ON e.DIV_ID = b.division WHERE a.uid = '" . $_GET[ 'uid' ] . "' OR a.user_id = '" . $_GET[ 'uid' ] . "' OR a.user_email = '" . $_GET[ 'uid' ] . "'";
$res = $conn -> query($sql);
$rows = $res -> fetch_assoc();
$uid = $rows['uid'];
$secoff = $rows['section'];
$divoff = $rows['division'];
$acc_lvl = $rows['lvl_name'];
$bsu = $rows['BSU_name'];
$div = $rows['DIV_NAME'];


$hadir = "SELECT COUNT(*) AS hadir FROM qrscan WHERE uid = '$uid' AND YEAR(date) = '" . $currentY . "'";
	$kirahadir = $conn -> query($hadir);
		$hadirsemasa =$kirahadir -> fetch_assoc();
		$JumHadir = $hadirsemasa['hadir'];

$cuti ="SELECT count(*) AS cuti FROM holiday WHERE YEAR(holiday_date) = '2024' AND holiday_date <= CURDATE()";
	$kiracuti = $conn -> query($cuti);
		$cutisemasa =$kiracuti -> fetch_assoc();
		$JumCuti = $cutisemasa['cuti'];

$BSUhadir = "SELECT COUNT(*) AS BSUhadir FROM qrscan A, userinfo B WHERE A.uid = B.uid AND B.section = '" . $secoff . "' AND B.division = '" . $divoff . "' AND YEAR(date) = '" . $currentY . "'";
	$kiraBSUhadir = $conn -> query($BSUhadir);
		$BSUhadirsemasa =$kiraBSUhadir -> fetch_assoc();
		$JumBSUHadir = $BSUhadirsemasa['BSUhadir'];

$kecuali = "SELECT count(*) AS Exception  FROM exception WHERE remarkAPV = 'Y' AND uid = '$uid' AND YEAR(date) = '" . $currentY . "' ";
	$kirakecuali = $conn -> query($kecuali);
		$kecualisemasa =$kirakecuali -> fetch_assoc();
		$JumException1 = $kecualisemasa['Exception'];
		if ( $JumException1 == '0') {$JumException = '0'; }	else { $JumException = $JumException1; }
		
// $perBSUhadir ="SELECT ROUND(('" . $JumBSUHadir . "' / '" .$TotalStaff. "' ) / (DAYOFYEAR(CURDATE()) - '" . $JumCuti . "') * 100 ) AS perBSUhadir";
// 	$pBSUhadir = $conn -> query($perBSUhadir);
// 		$peratusBSUhadir =$pBSUhadir -> fetch_assoc();
// 		$BSUPerHadir1 = $peratusBSUhadir['perBSUhadir'];
// 		if ( $BSUPerHadir1 == '0') {$BSUPerHadir = '0';}	else { $BSUPerHadir = $BSUPerHadir1; }

$ponteng ="SELECT (DAYOFYEAR(CURDATE()) - '" . $JumCuti . "' - '" . $JumHadir . "' - '" . $JumException1 . "' ) AS ponteng";
	$kiraponteng = $conn -> query($ponteng);
		$pontengsemasa =$kiraponteng -> fetch_assoc();
		$JumPonteng1 = $pontengsemasa['ponteng'];
		if ( $JumPonteng1 <= '0') {$JumPonteng = '0';}	else { $JumPonteng = $JumPonteng1; }

$perhadir ="SELECT ROUND('" . $JumHadir . "' / (DAYOFYEAR(CURDATE()) - '" . $JumCuti . "') * 100 ) AS perhadir";
	$phadir = $conn -> query($perhadir);
		$peratushadir =$phadir -> fetch_assoc();
		$MyPerHadir1 = $peratushadir['perhadir'];
		if ( $MyPerHadir1 == '0') {$MyPerHadir = '0';}	else { $MyPerHadir = $MyPerHadir1; }

		$data = array(
        // "hadir_bsu" => $BSUPerHadir,
        "status" => 'normal',
        "hadir" => $JumHadir,
        "ponteng" => $JumPonteng,
        "myhadir" => $MyPerHadir,
        "exception" => $JumException,
        "level" => $acc_lvl,
        "BSU" => $bsu,
        "div" => $div,

    );
    
    // Convert the data to JSON format
    $jsonData = json_encode($data);

    // Close the database connection
    $conn->close();

    // Set the Content-Type header to indicate that the response contains JSON data
    header('Content-Type: application/json');

    // Output the JSON data
    //print_r($jsonData);
    print_r($jsonData);
}
?>