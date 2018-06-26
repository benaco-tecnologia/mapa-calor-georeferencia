<?php
	$u = 'root';
	$p = 'r1r7C1h6ZG4ZVWF';
	$d = 'municipales';
	$link = mysqli_connect("sscl-db-instance.chapmhehekr9.us-east-1.rds.amazonaws.com", $u, $p) or die(mysqli_error());
	mysqli_select_db($link, $d) or die(mysqli_error());
?>
<!DOCTYPE html>
<html>
  <head>
    <title>Electores</title>
    <meta name="viewport" content="initial-scale=1.0, user-scalable=no">
    <meta charset="utf-8">
    <style>
      html, body {
        height: 100%;
        margin: 0;
        padding: 0;
		font-family: arial;
		font-size:0.9em;
      }
      #map {
        height: 100%;
      }
	  #leyenda{
		z-index: 100;
		width: 300px;
		right:0;
		top: 0;
		bottom: 0;
		position:absolute;
		background-color:#FFFFFF;
		padding: 10px;
		overflow: auto;
	  }
    </style>
  </head>
  <body>
	<div id="leyenda">
		<h3 align="center">Electores</h3>
		<table>
			<tr>
				<th>&nbsp;&nbsp;</th>
				<th>Calle</th>
				<th>Personas</th>
			</tr>	
		<?php 
			$q = "SELECT calle, count(*) as total  FROM municipales.electores where lat is null and lng is null group by calle order by count(*) desc;";
			$resultado = mysqli_query($link, $q) or die(mysqli_error());
			if (mysqli_num_rows($resultado) >= 1) {
				$a = array();
				while ($row = mysqli_fetch_array($resultado)) {
					//scale = c/max*2;
					$c = $row["total"];
					if ($c > 200) {
						$color = "red";
					}else if ($c > 100){
						$color = "orange";
					}else if ($c > 50){
						$color = "yellow";
					}else{
						$color = "green";
					}
					echo "<tr class=\"click_detalle\" style=\"cursor:pointer;\" calle=\"{$row["calle"]}\"><td style=\"background-color:$color;\"></td><td>{$row["calle"]}</td><td>{$row["total"]}</td></tr>";
				}
			}
		?>
		</table>
	</div>
    <div id="map"></div>
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.2/jquery.min.js"></script>
	<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBgxELyTBKWpgKX0F4TltyBVV7dabg12pY&signed_in=true">
    </script>
    <script>
		$( document ).ready(function() {
			// Note: This example requires that you consent to location sharing when
			// prompted by your browser. If you see the error "The Geolocation service
			// failed.", it means you probably did not give permission for the browser to
			// locate you.
			var geocoder;
			var map
			function initMap() {
				//geocoder = new google.maps.Geocoder();
				map = new google.maps.Map(document.getElementById('map'), {
					center: {lat: -33.454910, lng: -70.598109},
					zoom: 16
				});
			}
			
			function pinSymbol(color, pscale) {
				return {
					//path: 'M 0,0 C -2,-20 -10,-22 -10,-30 A 10,10 0 1,1 10,-30 C 10,-22 2,-20 0,0 z M -2,-30 a 2,2 0 1,1 4,0 2,2 0 1,1 -4,0',
					path: 'M-10,0a10,10 0 1,0 20,0a10,10 0 1,0 -20,0',
					//path: 'M-100,0a100,100 0 1,0 200,0a100,100 0 1,0 -200,0',
					fillColor: color,
					fillOpacity: 1,
					strokeColor: '#000',
					strokeWeight: 2,
					scale: pscale,
			   };
			}
			  
			   $(".click_detalle").click(function(){
					//map.setZoom(16);
					var calle = $(this).attr("calle");
					$.get( "/primarias.cl/get_calle.php?calle="+calle, function( data ) {
						eval(data);
						
						var medio = Math.round(arr.length/2);
						var latlng2 = new google.maps.LatLng(arr[medio][2],arr[medio][3]);
						map = new google.maps.Map(document.getElementById('map'), {
							center: latlng2,
							zoom: 16
						});

						var max = 0;
						for (i = 0; i < arr.length; i++) {
							var a = arr[i][0];
							var b = arr[i][1];
							var c = arr[i][4];
							if(max==0&&max<c){
								max = c;
							}
							var latlng = new google.maps.LatLng(arr[i][2],arr[i][3]);
							//scale = c/max*2;
							scale = 1;
							if (c > 50) {
								//scale = c/max;
								color = "red";
							}else if (c > 30){
								//scale = 0.8;
								color = "orange";
							}else if (c > 15){
								//scale = 0.6;
								color = "yellow";
							}else{
								//scale = 0.4;
								color = "green";
							}
							var marker = new google.maps.Marker({
								title: a+ " " + b + "\n("+c+" personas)",
								map: map,
								position: latlng,
								icon: pinSymbol(color, scale),
							});
						//	i++;
						}
					});
					
					//var latlng = new google.maps.LatLng(lat,lng);
					//map.setCenter(latlng);
					map.setZoom(15);
			  });
			  
		   initMap();
		  });
    </script>
  </body>
</html>
<?php
	mysqli_close($link) or die(mysqli_error());
?>