<?php
include('../include.php');
include('sitemapinclude.php');
include('../database.php');

// Get the requested URL
$request_uri = $_SERVER['REQUEST_URI'];

// Extract the filename from the requested URL
$file_name = basename($request_uri);
$indexRegex = "/sitemap(\d*)\.xml/";
preg_match($indexRegex, $file_name, $matches);
$index = $matches[1];

$limit = "";
if ($index == 2) {
    // sitemap2.xml will have limit 1000
    $limit = "LIMIT $numResultsPerSitemap";
} else {
    // sitemap3.xml will have limit 1000 1000
    $start = $index - 2;
    $limit = "LIMIT ". $start * $numResultsPerSitemap .", 1000";
}

// Unlocodes
$connection = setupDb();

$sql = "SELECT DISTINCT country, location FROM `CodeList` order by country, location $limit;";
$result = $connection->query($sql);
$unlocodes = $result->fetch_all(MYSQLI_ASSOC);

$xml = new SimpleXMLElement('<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" />');
foreach($unlocodes as $entry) {
    $countryUrl = $xml->addChild('url');
    $country = $entry['country'];
    $location = $entry['location'];
    $countryLoc = $countryUrl->addChild('loc', "https://unlocode.info/$country$location");
    $countryUrl->addChild('lastmod', $unlocodeLastMod);
    $countryUrl->addChild('priority', "0.5");
}

header('Content-type: text/xml');
print($xml->asXML());
?>