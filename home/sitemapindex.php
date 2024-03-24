<?php
include('../include.php');
include('sitemapinclude.php');
include('../database.php');

$xml = new SimpleXMLElement('<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"/>');

// Sitemap 1: home and the countries
$sitemap1 = $xml->addChild('sitemap');
$sitemap1Loc = $sitemap1->addChild('loc', "https://unlocode.info/sitemap1.xml");


// Sitemap 2-x: the unlocodes


$connection = setupDb();

// Current amount of results: 115928
$sql = "SELECT COUNT(DISTINCT country, location) FROM `CodeList`";
$result = $connection->query($sql);
$row = $result->fetch_row();
$numEntries = $row[0];
$numSitemaps = $numEntries / $numResultsPerSitemap;


foreach(range(2,$numSitemaps+2) as $index) {
    $sitemap1 = $xml->addChild('sitemap');
    $sitemap1Loc = $sitemap1->addChild('loc', "https://unlocode.info/sitemap$index.xml");
}
//SELECT COUNT(DISTINCT country, location) FROM `CodeList`;
/*
<sitemap>
    <loc>https://unlocode.info/sitemap1.xml</loc>
  </sitemap>
*/

header('Content-type: text/xml');
print($xml->asXML());
?>