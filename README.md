# unlocode.info #

[![Uptime Robot status](https://img.shields.io/uptimerobot/status/m798005161-86a84a64d960f891f0134601)](https://dashboard.uptimerobot.com/monitors/798005161)

This is the source code of the website [unlocode.info](https://unlocode.info/). It is a very convenient way to view all the info of a specific UN/LOCODE.

### Why make this open source? ###

I don't expect anyone to use this code to use a 1:1 copy of [unlocode.info](https://unlocode.info/) using this repo, so why make this open source? 2 reasons:

1. I hope to get PR's. PHP isn't my strong suit. So any PR to improve the code is welcome.
2. For posterity. If I ever stop hosting this site for whatever reason, somebody else can snatch up the domain and keep on hosting this site.

### Hosting the site ###

It takes a little bit of effort to host this site. First of all: create a secrets.php by using secrets.sample.php as its basis. Enter your database info and your Google Maps key there.

Also, we need database tables:
```
CREATE TABLE `CodeList` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ch` text NOT NULL,
  `country` varchar(2) NOT NULL,
  `location` varchar(3) NOT NULL,
  `name` text NOT NULL,
  `nameWoDiacritics` text NOT NULL,
  `subdivision` text NOT NULL,
  `status` text NOT NULL,
  `function` text NOT NULL,
  `date` text NOT NULL,
  `IATA` text NOT NULL,
  `coordinates` text NOT NULL,
  `remarks` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `country` (`country`,`location`),
  KEY `IATA` (`IATA`(768)),
  KEY `country_2` (`country`),
  KEY `location` (`location`),
  KEY `subdivision` (`subdivision`(768))
)
```

```
CREATE TABLE `subdivision` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `countryCode` varchar(2) NOT NULL,
  `code` varchar(3) NOT NULL,
  `name` text NOT NULL,
  `type` text NOT NULL,
  PRIMARY KEY (`id`)
)
```

The database also needs to be filled. You can use the CSV import in phpMyAdmin for this. You can use [datasets/un-locode](https://github.com/datasets/un-locode) for as a source of the CSVs. 

For the table `subdivision`, use subdivision-codes.csv, remove the first line with the headers and use this as the column names: `countryCode,code,name,type`
For the table `CodeList`, use code-list.csv, remove the first line with the headers and use this as the column names: `ch,country,location,name,nameWoDiacritics,subdivision,status,function,date,IATA,coordinates,remarks`
