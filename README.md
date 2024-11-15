# About #

This is the source code of the website [unlocode.info](https://unlocode.info/). It is a very convenient way to view all the info of a specific UN/LOCODE.

### Why make this open source? ###

I don't expect anyone to use this code to use a 1:1 copy of [unlocode.info](https://unlocode.info/) using this repo, so why make this open source? 2 reasons:

1. I hope to get PR's. PHP isn't my strong suit. So any PR to improve the code is welcome!
2. For posterity. If I ever stop hosting this site for whatever reason, somebody else can snatch up the domain and keep on hosting this site.

### Hosting the site ###

You unfortunately can't run this out of the box. First of all: the database.php is added to .gitignore because that contains credentials. It looks like this:

```
<?php
function setupDb() {
    $host='localhost';
    $user='my_database_user';
    $password='my_password';
    $database='my_database';

    // Show errors when there's something wrong with what we're doing to MySQL
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    
    $connection = $mysqli = new mysqli($host, $user, $password, $database);
    $connection->set_charset("utf8");
    return $connection;
}
?>
```

Also, the database needs to be filled. You can use the CSV import in phpMyAdmin for this. You can use [datasets/un-locode](https://github.com/datasets/un-locode) for as a source of the CSVs.
