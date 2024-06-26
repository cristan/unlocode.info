<?php
include('../include.php');
include('sitemapinclude.php');
include('../countryList.php');

$xml = new SimpleXMLElement('<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" />');

// Home
$homeUrl = $xml->addChild('url');
$homeLoc = $homeUrl->addChild('loc', "https://www.unlocode.info");
$homeUrl->addChild('lastmod', $homeLastMod);
$homeUrl->addChild('priority', "1.0");

// About
$aboutUrl = $xml->addChild('url');
$aboutLoc = $aboutUrl->addChild('loc', "https://www.unlocode.info/about");
$aboutLoc->addChild('lastmod', $homeLastMod);
$aboutLoc->addChild('priority', "1.0");

// Countries
foreach($countries as $countryCode => $countryName) {
    $countryUrl = $xml->addChild('url');
    $countryLoc = $countryUrl->addChild('loc', "https://unlocode.info/country/$countryCode");
    $countryUrl->addChild('lastmod', $countryLastMod);
    $countryUrl->addChild('priority', "0.8");
}

header('Content-type: text/xml');
print($xml->asXML());

?>