<?php

define("MYSQL_SERVER", "localhost");
define("MYSQL_USER", "root");
define("MYSQL_PASSWORD", "11121975");
define("MYSQL_TRD_DB", "TRD");
define("MYSQL_TRD_MAIN_TABLE", "history");

$connloc = new mysqli(MYSQL_SERVER, MYSQL_USER, MYSQL_PASSWORD);
$r = mysqli_query($connloc, "CREATE DATABASE IF NOT EXISTS " . MYSQL_TRD_DB);
$sql = "CREATE TABLE IF NOT EXISTS " . MYSQL_TRD_DB . "." . MYSQL_TRD_MAIN_TABLE . " (`id` bigint(20) NOT NULL AUTO_INCREMENT, `datetime` datetime, `name` varchar(64), `bid` float, `ask` float, `diff` float, PRIMARY KEY (`id`)) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1";
$r = mysqli_query($connloc, $sql);

$importfile = $argv[1];

$handle = fopen($importfile,"r");

if ($handle == false){
	exit;
}

while(!feof($handle)){
	$line = fgets($handle);
	$array=explode(" ", $line);
	if (count($array)==5){
		$datetime = $array[0];
		$name = $array[1];
		$bid = (float) $array[2];
		$ask = (float) $array[3];
		$diff = (float) $array[4];
		echo $datetime . "\r\n";
		$sql = "SELECT * FROM " . MYSQL_TRD_DB . "." . MYSQL_TRD_MAIN_TABLE . " WHERE name like '%" . $name . "%' and datetime = '" . $datetime . "'";
		$r = mysqli_query($connloc, $sql);
		echo "num rows = " . $r->num_rows . "\r\n";
		if ($r->num_rows==0){
			$sql = "INSERT INTO " . MYSQL_TRD_DB . "." . MYSQL_TRD_MAIN_TABLE . " (datetime, name, bid, ask, diff) VALUES ('".$datetime."','".$name."','".$bid."','".$ask."','".$diff."')";
			$r = mysqli_query($connloc, $sql);
			echo "One row inserted.\r\n";
		}
	}
	echo $line;
}

fclose($handle);

