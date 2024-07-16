<?php
    $servername = 'localhost';
    $username = 'root';
    $password = '';
    $dbname = 'lottery';

    $connect = new mysqli($servername, $username, $password, $dbname);
    $connect->set_charset("utf8");

    // $servername = 'myadminphp.bowlab.net';
    // $username = 'u583789277_DBWebtechG3';
    // $password = 'Cookcook66';
    // $dbname = 'u583789277_DBWebtechG3';

    // $connect = new mysqli($servername, $username, $password, $dbname);
    // $connect->set_charset("utf8");
?>