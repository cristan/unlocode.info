# unlocode.info #

[![Uptime Robot status](https://img.shields.io/uptimerobot/status/m798005161-86a84a64d960f891f0134601)](https://dashboard.uptimerobot.com/monitors/798005161)

This is the source code of the website [unlocode.info](https://unlocode.info/). It is a very convenient way to view all the info of a specific UN/LOCODE.

### Why make this open source? ###

I don't expect anyone to use this code to use a 1:1 copy of [unlocode.info](https://unlocode.info/) using this repo, so why make this open source? 2 reasons:

1. I hope to get PR's. PHP isn't my strong suit. So any PR to improve the code is welcome.
2. For posterity. If I ever stop hosting this site for whatever reason, somebody else can snatch up the domain and keep on hosting this site.

### Hosting the site ###

It takes a little bit of effort to host this site. First of all: you need to create a secrets.php. It looks like this:

```
<?php
$db_host='localhost';
$db_user='my_database_user';
$db_password='my_database_password';
$db_database='my_database';
$maps_key='my_maps_key';
?>
```

Also, the database needs to be filled. You can use the CSV import in phpMyAdmin for this. You can use [datasets/un-locode](https://github.com/datasets/un-locode) for as a source of the CSVs. I've added indices to country, location, subdivision and IATA.
