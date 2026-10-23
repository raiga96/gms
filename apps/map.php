<?php 
$lat = $_GET['lat'];
$long = $_GET['long'];
?>
<iframe width="100%" height="100%" style="border:0px solid red;" src="https://maps.google.com/maps?q=<?PHP  echo $lat; ?>,<?PHP  echo $long; ?>&output=embed"></iframe>