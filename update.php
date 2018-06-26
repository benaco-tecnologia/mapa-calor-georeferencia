<?php
	$u = 'root';
	$p = 'r1r7C1h6ZG4ZVWF';
	$d = 'municipales';
	$q=$_GET["q"];
	$link = mysqli_connect("sscl-db-instance.chapmhehekr9.us-east-1.rds.amazonaws.com", $u, $p) or die(mysqli_error());
	mysqli_select_db($link, $d) or die(mysqli_error());
	$resultado = mysqli_query($link, $q) or die(mysqli_error());
	mysqli_close($link) or die(mysqli_error());
	echo "$q";
	//echo "1";
?>