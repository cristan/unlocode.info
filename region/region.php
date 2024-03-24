<?php    
include('../include.php');
include('countryLoader.php');
include('../countryList.php');

$countryCode = $_GET["countryCode"];
$regionCode = $_GET["regionCode"];

// Redirect permanently to uppercase variant of provided URL when not uppercase
if($countryCode != strtoupper($countryCode) || $regionCode != strtoupper($regionCode)) {
    header("Location: /country/".strtoupper($countryCode) ."/region/". strtoupper($regionCode), true, 301);
    exit;
}

$regionLoader = new RegionLoader();
$details = $regionLoader->loadRegion($countryCode, $regionCode);
if (!$details) {
    http_response_code(404);
    include('../404.shtml');
    die();
}
$countryName = $countries[$countryCode]
?>
<!DOCTYPE html>
<html>
  <head>  
    <meta content="width=device-width, initial-scale=1" name="viewport" />	
    <title><?=$countryName?></title>
    <meta name="description" content="Explore UNLOCODEs for <?=$countryName?>. Find codes for ports, rail terminals, road terminals, airports, and more."/>
    <link rel="icon" href="../favicon.svg">
    <link rel="stylesheet" href="../flat-remix.min.css">
    <link rel="stylesheet" href="../unlocode.css">
  </head>
  <body class="selectable">
  <main>
    <div class="paper">

      <h1><a href="/">UN/LOCODE</a> in <?=$countryName?></h1>
      <div class="unlocodesContainer">
    
    <?php
foreach($details as $entry) {
    echo "<div style='padding: 8px 0;'><a href='https://unlocode.info/$entry->unlocode'>$entry->unlocode</a>: ". $entry->name . "</div>\n";
}
  ?>
    </div>
  </div>
  <div class="footer">
From <a href='https://unece.org/trade/uncefact/unlocode' target='_blank'><?=$unlocodeVersion?></a>
</div>
  </main>
</body>
</html>