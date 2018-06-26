<?php
	die();
	$u = 'root';
	$p = 'r1r7C1h6ZG4ZVWF';
	$d = 'municipales';
	$link = mysqli_connect("sscl-db-instance.chapmhehekr9.us-east-1.rds.amazonaws.com", $u, $p) or die(mysqli_error());
	mysqli_select_db($link, $d) or die(mysqli_error());
?>
<!DOCTYPE html>
<html>
  <head>
    <title>Geolocation</title>
    <meta name="viewport" content="initial-scale=1.0, user-scalable=no">
    <meta charset="utf-8">
    <style>
      html, body {
        height: 100%;
        margin: 0;
        padding: 0;
      }
      #map {
        height: 100%;
      }
    </style>
  </head>
  <body>
    <div id="map"></div>
	 <input type="button" value="Encode" id="code_address">
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
				 geocoder = new google.maps.Geocoder();
				map= new google.maps.Map(document.getElementById('map'), {
				center: {lat: -34.397, lng: 150.644},
				zoom: 6
			  });
			  var infoWindow = new google.maps.InfoWindow({map: map});
			}
			
			<?php 
				$q = "SELECT calle, numero, count(*)  FROM municipales.electores where lat is null and lng is null group by calle, numero order by count(*) desc limit 100;";
				$resultado = mysqli_query($link, $q) or die(mysqli_error());
				if (mysqli_num_rows($resultado) >= 1) {
					$a = array();
					while ($row = mysqli_fetch_array($resultado)) {
						$a[]="['{$row["calle"]}','{$row["numero"]}']";
					}
					echo "var arr = [".implode(",",$a)."];"; 
				}
			?>
			
			$("#code_address").click(
				function codeAddress() {
					var i=0;
					setInterval(function(){ 
						var a = arr[i][0];
						var b = arr[i][1];
						geocoder.geocode( { 'address': a+" "+b +", ñuñoa, santiago." }, function(results, status) {
						  if (status == google.maps.GeocoderStatus.OK) {
							var q = "update electores set lat ='"+results[0].geometry.location.lat()+"', lng = '"+results[0].geometry.location.lng()+"' where calle = '"+a+"' AND numero = '"+b+"';";
							$.get( "/primarias.cl/update.php?q="+q, function( data ) {
								console.log(data);
							});
							map.setCenter(results[0].geometry.location);
							var marker = new google.maps.Marker({
								map: map,
								position: results[0].geometry.location
							});
						  } else {
							console.log("Geocode was not successful for the following reason: " + status);
						  }
						});
						i++;
					}, 1000);
				  }
			);
			
		  initMap();
		  });
    </script>
  </body>
</html>
<?php
	mysqli_close($link) or die(mysqli_error());
?>