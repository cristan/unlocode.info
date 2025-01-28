<?php

require 'secrets.php';

function setupDb()
{
    // Show errors when there's something wrong with what we're doing to MySQL
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

    global $db_host, $db_user, $db_password, $db_database;

    $connection = $mysqli = new mysqli($db_host, $db_user, $db_password, $db_database);
    $connection->set_charset('utf8');

    return $connection;
}
