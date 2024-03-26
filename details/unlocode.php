<?php    
include('../include.php');
include('detailsLoader.php');

$unlocode = $_GET["unlocode"];
// Redirect permanently to uppercase variant of provided URL when not uppercase
if($unlocode != strtoupper($unlocode)) {
    header("Location: /".strtoupper($unlocode), true, 301);
    exit;
}

$detailsLoader = new DetailsLoader();
$details = $detailsLoader->loadDetails($unlocode);
if (!$details) {
    http_response_code(404);
    include('../404.shtml');
    die();
}

?>
<!DOCTYPE html>
<html>
  <head>  
    <meta content="width=device-width, initial-scale=1" name="viewport" />	
    <title><?=$details->title?></title>
    <meta name="description" content="<?=$details->description?>"/>
    <link rel="icon" href="favicon.svg">
    <link rel="stylesheet" href="flat-remix.min.css">
    <link rel="stylesheet" href="unlocode.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
     integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY="
     crossorigin=""/>
      <!-- Make sure you put this AFTER Leaflet's CSS -->
     <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
       integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo="
       crossorigin=""></script>
  </head>
  <body class="selectable">

  <main>
    <div class="paper">
    <h1><?=$details->header?></h1>
    <div class="divsContainer">
        <div style="flex: 2;">
  <?php
  
foreach($details->names as $index=>$name) {
    if ($index != 0) {
        echo "or<br/>\n";
    }
    echo "Name: ". $name . "<br/>\n";
}


if ($details->entryToBeRemoved) {
    echo "<span style='color:#d41919'>Entry to be removed in the next issue</span><br/>\n";
}

$subdivision = $details->subdivision;
if ($subdivision) {
    if ($details->regionName) {
        echo "$details->regionType: $details->regionName ($subdivision)<br/>\n";
    } else {
        echo "$details->regionType: $subdivision<br/>\n";
    }
}
echo "Country: $details->country<br/>\n<br/>\n";

function array2ul($array) {
    $out = "<ul>";
    foreach($array as $key => $elem){
        $out .= "<li>$elem</li>";
    }
    $out .= "</ul>\n";
    return $out; 
}

$functions = $details->functions;
if ($functions) {
    echo "Functions: <br/>\n";
    echo array2ul($functions);
}

$iata = $details->IATA;
if ($iata) {
    echo "<p>IATA: $iata</p>\n";
}
$possibleIATA = $details->possibleIATA;
if ($possibleIATA) {
    echo "<p>Possible IATA<span class='tooltip'>?<span class='tooltiptext'>The location has an airport and no explicitly defined IATA. That means either means that the IATA is $possibleIATA, or the airport doesn't have an IATA.</span></span>: $possibleIATA</p>\n";
}
$otherLocationsWithSameIata = $details->otherLocationsWithSameIata;
if ($otherLocationsWithSameIata) {
    echo "<div>Other ". (count($otherLocationsWithSameIata) == 1 ? "entry" : "entries") ." with same IATA: ";
    foreach($otherLocationsWithSameIata as $index=>$other) {
        if ($index != 0) {
            echo ", ";
        }
        echo "<a href='https://unlocode.info/$other->unlocode'>$other->unlocode</a>";
        if ($other->warning) {
            echo "<div class='tooltip'>!!<span class='tooltiptext'>$other->warning</span></div>";
        }
    }
    echo ".</div>\n";
}

$remarks = $details->remarks;
if ($remarks) {
    echo "<p>Remarks: $remarks</p>\n";
}
$coordinates = $details->coordinates;
if ($coordinates) {
    echo "<p>Coordinates: $coordinates => ".$details->degreesCoordinates->latitude .", ". $details->degreesCoordinates->longitude ." or ". $details->decimalCoordinates->latitude .", ". $details->decimalCoordinates->longitude ."</p>\n";
}
?>
</div>
<div style="flex: 3;">
<?php
if ($coordinates) {
    //echo '<iframe class="map" style="border:0" loading="lazy" allowfullscreen src="https://www.google.com/maps/embed/v1/view?zoom=12&center='. urlencode($details->decimalCoordinates->latitude .",". $details->decimalCoordinates->longitude) .'&key=AIzaSyDQvt-CZgnIcXjMw2boq46oaAKAjDjbNIM"></iframe>';
    echo "<div id='leafletMap'></div>";
    echo "<script>var map = L.map('leafletMap').setView([".$details->decimalCoordinates->latitude.", ". $details->decimalCoordinates->longitude ."], 13);
    const urlTemplate = window.devicePixelRatio > 1 ? 'https://tile.osmand.net/hd//{z}/{x}/{y}.png' : 'https://tile.openstreetmap.org/{z}/{x}/{y}.png'
    L.tileLayer(urlTemplate, {
        maxZoom: 19,
        attribution: '&copy; <a href=\"http://www.openstreetmap.org/copyright\">OpenStreetMap</a>'
    }).addTo(map);
    </script>";
}
?>
</div>
</div>
</div>
<div class="footer">
From <a href='https://unece.org/trade/uncefact/unlocode' target='_blank'><?=$unlocodeVersion?></a>
<?php
if ($details->status) {
    echo " | $details->status";
}
if ($details->lastEnteredUpdated) {
    echo " | Entry last entered/updated: ".$details->lastEnteredUpdated->month."-".$details->lastEnteredUpdated->year ."<br/>";
}
?>
</div>
</main>
  </body>
</html>