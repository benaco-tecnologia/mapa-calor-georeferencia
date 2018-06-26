<?php
$u = 'root';
$p = 'r1r7C1h6ZG4ZVWF';
$d = 'municipales';
$link = mysqli_connect("sscl-db-instance.chapmhehekr9.us-east-1.rds.amazonaws.com", $u, $p) or die(mysqli_error());
mysqli_select_db($link, $d) or die(mysqli_error());
$fila = 1;
if (($gestor = fopen("basefinalparaGeo.csv", "r")) !== FALSE) {
    while (($datos = fgetcsv($gestor, 1000, ";")) !== FALSE) {
        $numero = count($datos);
        echo "<p> $numero de campos en la línea $fila: <br /></p>\n";
        $fila++;
		$datos=implode(";",$datos);
		$datos=utf8_decode($datos);
		$datos=str_replace("'","",$datos);
		$data = explode(";",$datos);
		$insert = "INSERT INTO electores (rut,nombre,paterno,materno,direccion,circunscripcion,calle,numero,resto,poblacion,sexo,edad,profesion,salud,mesa,local) ".
				  "VALUES ('{$data[0]}','{$data[12]} {$data[13]}','{$data[10]}','{$data[11]}','{$data[4]}','{$data[3]}','{$data[5]}','{$data[7]}','{$data[8]}','{$data[9]}','{$data[15]}','{$data[16]}','{$data[17]}','{$data[18]}','{$data[28]}','{$data[30]}');";
		echo $insert . "<br />";
		mysqli_query($link, $insert) or die(mysqli_error());
    }
    fclose($gestor);
}
mysqli_close($link) or die(mysqli_error());
?>