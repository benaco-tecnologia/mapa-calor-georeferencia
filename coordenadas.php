

<?php
	// Codificar pagina en ANSI
	// Codificar contenidos en UTF8 con utf8_encode
	// En aplicación usa como UTF8 con guard let
	
	$u = 'root';
	$p = 'r1r7C1h6ZG4ZVWF';
	$d = 'municipales';
	$sql = "";
	$link = mysqli_connect("sscl-db-instance.chapmhehekr9.us-east-1.rds.amazonaws.com", $u, $p) or die(mysqli_error());
	mysqli_select_db($link, $d) or die(mysqli_error());
	$q = "SELECT calle, numero, count(*) FROM municipales.electores where lat is null and lng is null group by calle, numero order by count(*) desc limit 1;";
	$resultado = mysqli_query($link, $q) or die(mysqli_error());
	$rt = array();
	if (mysqli_num_rows($resultado) >= 1) {
		while ($row = mysqli_fetch_row($resultado)) {
			//$address=urlencode($row[0]." ".$row[1].", ÑUÑOA");
			$address = $row[0]." ".$row[1].", ÑUÑOA";
			$address = str_replace(" ", "+", $address); // replace all the white space with "+" sign to match with google search pattern
			$address = utf8_encode($address);
			$url = "https://maps.google.com/maps/api/geocode/json?address=$address&key=AIzaSyCw8cMLgq1jAkNWwB2Bk894j_ouJ_tXGxk";
			//echo $url."<br />";
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
			$response = curl_exec($ch);
			curl_close($ch);
			$json = json_decode($response);
			//print_r($json);
			$json = json_decode(utf8_decode($response),TRUE);
			echo @$json['results'][0]['geometry']['location']["lat"]."<br />";
			echo @$json['results'][0]['geometry']['location']["lng"]."<br />";
			$sql .= "UPDATE municipales.electores set lat = '{$json['results'][0]['geometry']['location']["lat"]}', lng = '{$json['results'][0]['geometry']['location']["lng"]}' WHERE calle = '{$row[0]}' AND numero = '{$row[1]}';<br />";
			//sleep(1);
		}
	} else {	 
	}
	mysqli_close($link) or die(mysqli_error());
	echo $sql;
	
?>