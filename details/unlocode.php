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
  </head>
  <style>
.tooltip {
  position: relative;
  display: inline-block;
  border-bottom: 1px dotted black;
}

.tooltip .tooltiptext {
  visibility: hidden;
  width: 120px;
  background-color: #616161;
  color: #fff;
  text-align: center;
  border-radius: 6px;
  padding: 5px 5px;
  
  /* Position the tooltip */
  position: absolute;
  z-index: 1;
  bottom: 100%;
  left: 50%;
  margin-left: -60px;
}

.tooltip:hover .tooltiptext {
  visibility: visible;
  font-size: smaller;
}
</style>
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
    $out .= "</ul>";
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
// $possibleIATA = $details->possibleIATA;
// if ($iata) {
//     echo "<p>Possible IATA <div class='tooltip'>?<span class='tooltiptext'>The location has an airport and no explicitly defined IATA. That means either means that the IATA is $possibleIATA, or the airport doesn't havce a IATA.</span></div>: $iata</p>\n";
// }
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
    echo "<p>Coordinates: $coordinates (".$details->decimalCoordinates->latitude .", ". $details->decimalCoordinates->longitude .")</p>\n";
}
?>
</div>
<div style="flex: 3;">
<?php
if ($coordinates) {
    echo '<iframe class="map" style="border:0" loading="lazy" allowfullscreen src="https://www.google.com/maps/embed/v1/view?zoom=12&center='. urlencode($details->decimalCoordinates->latitude .",". $details->decimalCoordinates->longitude) .'&key=AIzaSyDQvt-CZgnIcXjMw2boq46oaAKAjDjbNIM"></iframe>';
}
?>
</div>
</div>
</section>
</div>
<div class="footer">
From <a href='https://unece.org/trade/uncefact/unlocode' target='_blank'><?=$unlocodeVersion?></a><?php
if ($details->status) {
    echo " | $details->status";
}
?> | Entry last entered/updated: <?=$details->month?>-<?=$details->year?><br/>
</div>
</main>
  </body>
</html>