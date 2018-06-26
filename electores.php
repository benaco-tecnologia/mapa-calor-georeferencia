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
				<th>Núm.</th>
				<th>Personas</th>
			</tr>	
		<?php 
			$q = "SELECT calle, numero, count(*) as total, max(lat) as lat, max(lng) as lng FROM municipales.electores 
					where lat is not null and lng is not null 
					group by calle, numero 
					order by count(*) desc;";
			$resultado = mysqli_query($link, $q) or die(mysqli_error());
			if (mysqli_num_rows($resultado) >= 1) {
				$a = array();
				while ($row = mysqli_fetch_array($resultado)) {
					//scale = c/max*2;
					$c = $row["total"];
					if ($c > 50) {
						$color = "red";
					}else if ($c > 30){
						$color = "orange";
					}else if ($c > 15){
						$color = "yellow";
					}else{
						$color = "green";
					}
					echo "<tr class=\"click_detalle\" style=\"cursor:pointer;\" lat=\"{$row["lat"]}\" lng=\"{$row["lng"]}\"><td style=\"background-color:$color;\"></td><td>{$row["calle"]}</td><td>{$row["numero"]}</td><td>{$row["total"]}</td></tr>";
				}
			}
		?>
		</table>
	</div>
    <div id="map"></div>
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.2/jquery.min.js"></script>
	<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBgxELyTBKWpgKX0F4TltyBVV7dabg12pY&signed_in=true&libraries=drawing,geometry">
    </script>
    <script>
		$( document ).ready(function() {

			// Note: This example requires that you consent to location sharing when
			// prompted by your browser. If you see the error "The Geolocation service
			// failed.", it means you probably did not give permission for the browser to
			// locate you.
			
			<?php 
				$q = "SELECT calle, numero, count(*) as total, max(lat) as lat, max(lng) as lng FROM municipales.electores 
						where lat is not null and lng is not null 
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
			?>
			
			var geocoder;
			var map
			function initMap() {
				//geocoder = new google.maps.Geocoder();
				map = new google.maps.Map(document.getElementById('map'), {
					center: {lat: -33.454910, lng: -70.598109},
					zoom: 16
				});
				
				var drawingManager = new google.maps.drawing.DrawingManager({
				drawingMode: google.maps.drawing.OverlayType.MARKER,
				drawingControl: true,
				drawingControlOptions: {
				  position: google.maps.ControlPosition.TOP_CENTER,
				  drawingModes: [
					google.maps.drawing.OverlayType.POLYGON,
					//google.maps.drawing.OverlayType.RECTANGLE
				  ]
				}
			  });
			  drawingManager.setMap(map);
			  google.maps.event.addListener(drawingManager, 'polygoncomplete', function (polygon) {
				var electores = 0;
				var area = google.maps.geometry.spherical.computeArea(polygon.getPath());
				for (i = 0; i < arr.length; i++) {
					var a = arr[i][0];
					var b = arr[i][1];
					var c = arr[i][4];
					var latlng = new google.maps.LatLng(arr[i][2],arr[i][3]);
					if (google.maps.geometry.poly.containsLocation(latlng, polygon)){
						electores += parseInt(c);
					}
				}
				
				var areaM = parseInt(area);//parseInt(parseInt(area)/1000000);
				var ele = electores / areaM;
				
				if(electores!=0){
					alert("El area seleccionada contiene "+electores+" electores.");
				}else{
					alert("El area no contiene electores.");
				}
				
				/*
				for (var i = 0; i < polygon.getPath().getLength(); i++) {
					console.log(polygon.getPath().getAt(i).toUrlValue(6));
					//document.getElementById('info').innerHTML += polygon.getPath().getAt(i).toUrlValue(6) + "<br>";
				}*/
				
				google.maps.event.addListener(polygon, 'click', function() {
					  this.setMap(null);
				  });
			});
				
			
				displayElectores();
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
			
			function displayElectores() {
				var i=0;
				//setInterval(function(){
				var max = 0;
				for (i = 0; i < arr.length; i++) {
					var a = arr[i][0];
					var b = arr[i][1];
					var c = arr[i][4];
					if(max==0&&max<c){
						max = c;
					}
					var latlng = new google.maps.LatLng(arr[i][2],arr[i][3]);
					scale = c/max*2;
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
				//}, 10);
			  }
			  
			  $(".click_detalle").click(function(){
					//map.setZoom(16);
					var lat = $(this).attr("lat");
					var lng = $(this).attr("lng");
					var latlng = new google.maps.LatLng(lat,lng);
					map.setCenter(latlng);
					map.setZoom(17);
			  });
			  
			  initMap();
		  });
    </script>
  </body>
</html>
<?php
	mysqli_close($link) or die(mysqli_error());
?>