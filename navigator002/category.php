<?php defined('_JEXEC') or die;

$db = JFactory::getDbo();
$doc = JFactory::getDocument();

$css = "#map-canvas {
			min-height  : 800px;
			margin  : 0px;
			padding : 0px;
		}

		form {
			position : absolute;
			z-index  : 1;
			background: #fff;
			border: 2px solid #000;
			margin   : 2em;
			padding  : 1em;
			left	 : 33%;
		}";

// http://wrightshq.com/playground/placing-multiple-markers-on-a-google-map-using-api-3/
$js = "<script>
jQuery(function($) {
	// Asynchronously Load the map API
	var script = document.createElement('script');
	script.src = 'http://maps.googleapis.com/maps/api/js?sensor=false&callback=initialize';
	document.body.appendChild(script);
});

function initialize() {
	var map;
	var bounds = new google.maps.LatLngBounds();
	var mapOptions = {
		mapTypeId: 'roadmap'
	};

	// Display a map on the page
	map = new google.maps.Map(document.getElementById('map-canvas'), mapOptions);
	map.setTilt(45);

		// Display multiple markers on a map
		var infoWindow = new google.maps.InfoWindow(), marker, i;

		// Loop through our array of markers & place each one on the map
		for( i = 0; i < markers.length; i++ ) {
			var position = new google.maps.LatLng(markers[i][1], markers[i][2]);
			bounds.extend(position);
			marker = new google.maps.Marker({
			icon: {
				  path: google.maps.SymbolPath.CIRCLE,
				  scale: 2,
				  fillColor: '#8a2b87',
				  fillOpacity: 1,
				  strokeColor: '#8a2b87',
				  strokeWeight: 2
				},
				position: position,
				map: map,
				title: markers[i][0]
			});

			// Allow each marker to have an info window
			google.maps.event.addListener(marker, 'click', (function(marker, i) {
				return function() {
					infoWindow.setContent(infoWindowContent[i][0]);
					infoWindow.open(map, marker);
				}
			})(marker, i));

			// Automatically center the map fitting all markers on the screen
			map.fitBounds(bounds);
		}

		// Override our map zoom level once our fitBounds function runs (Make sure it only runs once)
		var boundsListener = google.maps.event.addListener((map), 'bounds_changed', function(event) {
			this.setZoom(3);
			google.maps.event.removeListener(boundsListener);
		});
		
		var styles = [
						{
							featureType: 'all',
							stylers	: [
								{ visibility: 'off' }

							]
						},
						{
							featureType: 'landscape',
							stylers	: [
								{ visibility: 'on' },
								{ color: '#999' }

							]
						},
						{
							featureType: 'water',
							stylers	: [
								{ visibility: 'on' },
								{ color: '#ffffff' }
							]
						}
					];

					map.setOptions({styles: styles});

	}
</script>";

$markers = "<script>
var markers = [";

foreach ($this->leading as $leading)
{
	$query = ' SELECT locations' .
		' FROM #__k2_items_locations' .
		' WHERE itemId = ' . $db->Quote($leading->id) . '';
	$db->setQuery($query);

	if ($db->loadResult() != 'null')
	{
		$value = json_decode($db->loadResult(), true);
	}

	foreach ($value as $key => $location)
	{
		$markers .= "['<b>$key</b>', {$location['lat']}, {$location['lng']}],";
	}
}
$markers .= "];
</script>";

$infoWindows = "<script>
	// Info Window Content
	var infoWindowContent = [";

foreach ($this->leading as $leading)
{
	$query = ' SELECT locations' .
		' FROM #__k2_items_locations' .
		' WHERE itemId = ' . $db->Quote($leading->id) . '';
	$db->setQuery($query);

	if ($db->loadResult() != 'null')
	{
		$value = json_decode($db->loadResult(), true);
	}

	foreach ($value as $key => $location)
	{
		$infoWindows .= "['" . addslashes($leading->introtext) . "'],";
	}
}

$infoWindows .= "];
</script>";

$doc->addStyleDeclaration($css);
$doc->addCustomTag($js);
$doc->addCustomTag($markers);
$doc->addCustomTag($infoWindows);
?>
<div id="map-canvas"></div>
<!-- Start K2 Category Layout -->
<div id="k2Container" class="itemListView<?php if ($this->params->get('pageclass_sfx'))
{
	echo ' ' . $this->params->get('pageclass_sfx');
} ?>">

	<?php // echo '<pre>' . print_r($this, true) . '</pre>'; ?>


	<?php if (isset($this->leading) && count($this->leading)): ?>
		<!-- Primary items -->
		<div id="itemListPrimary">
			<?php foreach ($this->leading as $key => $item): ?>

				<div class="itemContainer<?php echo $lastContainer; ?>"<?php echo (count($this->primary) == 1) ? '' : ' style="width:' . number_format(100 / $this->params->get('num_primary_columns'), 1) . '%;"'; ?>>
					<?php
					// Load category_item.php by default
					$this->item = $item;
					echo $this->loadTemplate('item');
					?>
				</div>
			<?php endforeach ?>

		</div>
	<?php endif; ?>
</div>
<!-- End K2 Category Layout -->
