<?php
require_once("connection.inc");
////print(@$_GET['x']);
//print('&nbsp;');
//print(@$_GET['cookie_ok']);


$query          = "UPDATE visitors SET cookie_ok = " . $_GET['cookie_ok']. " WHERE ID = " . $_GET['x'] . ";";
//print($query);
$conn -> query($query);