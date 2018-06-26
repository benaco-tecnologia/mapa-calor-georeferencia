<?php
	$u = 'root';
	$p = 'r1r7C1h6ZG4ZVWF';
	$d = 'municipales';
	$calle=$_GET["calle"];
	$link = mysqli_connect("sscl-db-instance.chapmhehekr9.us-east-1.rds.amazonaws.com", $u, $p) or die(mysqli_error());
	mysqli_select_db($link, $d) or die(mysqli_error());
	$q = "SELECT calle, numero, count(*) as total, max(lat) as lat, max(lng) as lng FROM municipales.electores 
			where lat is not null and lng is not null and calle='$calle'
			group by calle, numero 
			order by count(*) desc;";
	$resultado = mysqli_query($link, $q) or die(mysqli_error());
	if (mysqli_num_rows($resultado) >= 1) {
		$a = array();		
		while ($row = mysqli_fetch_array($resultado)) {
			$a[]="['{$row["calle"]}','{$row["numero"]}','{$row["lat"]}','{$row["lng"]}','{$row["total"]}']";
		}
		echo "var arr = [".implode(",",$a)."];"; 
	}
	mysqli_close($link) or die(mysqli_error());
?>