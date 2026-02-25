<?php

function dbConnect() {
    $host = "localhost";
    $user = "root";
    $pass = ""; 
    $dbname = "php_exam_db";

    $mysqli = new mysqli($host, $user, $pass, $dbname);

    if ($mysqli->connect_error) {
        die("Connection failed: " . $mysqli->connect_error);
    }

    return $mysqli;
}