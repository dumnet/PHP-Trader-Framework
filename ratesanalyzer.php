<?php

/*	ratesanalyzer.php
	IKYTraderFramework.
	This tool helps you get a clearer view of what is in the database trd table history
*/

define("MYSQL_SERVER", "localhost");
define("MYSQL_USER", "");
define("MYSQL_PASSWORD", "");
define("MYSQL_TRD_DB", "TRD");
define("MYSQL_TRD_MAIN_TABLE", "history");

$connloc = new mysqli(MYSQL_SERVER, MYSQL_USER, MYSQL_PASSWORD);
$r = mysqli_query($connloc, "CREATE DATABASE IF NOT EXISTS " . MYSQL_TRD_DB);
$sql = "CREATE TABLE IF NOT EXISTS " . MYSQL_TRD_DB . "." . MYSQL_TRD_MAIN_TABLE . " (`id` bigint(20) NOT NULL AUTO_INCREMENT, `datetime` datetime, `name` varchar(64), `bid` float, `ask` float, `diff` float, PRIMARY KEY (`id`)) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1";
$r = mysqli_query($connloc, $sql);

$objDateTime = new DateTime('NOW');
$strdt = $objDateTime->format(DateTime::ISO8601);
$strdtday = substr($strdt, 0, 10);
//echo $strdt . "\r\n";

$sql = "SELECT distinct(name) FROM " . MYSQL_TRD_DB . "." . MYSQL_TRD_MAIN_TABLE;
$r = mysqli_query($connloc, $sql);
//echo mysqli_error($connloc) . "\r\n";
if ($r->num_rows>0){
	echo "Number of distinct pairs in table = " . $r->num_rows . "\r\n";
	while($row = $r->fetch_assoc()){

		$name = $row["name"];
		echo formatstr($name,10) . " ";
		$sql = "SELECT count(*) FROM " . MYSQL_TRD_DB . "." . MYSQL_TRD_MAIN_TABLE . " WHERE name like '%" . $name . "%'";
		$ratesnum = mysqli_query($connloc, $sql);
		if ($ratesnum->num_rows>0){
			if ($row = $ratesnum->fetch_assoc()){
				$c = $row['count(*)'];
				echo "(" . formatstr($c,6) . ") ";
			}
		}


		//get the last known rate and get the last known day date for this rate
		$lastday = "";
		$sql = "SELECT * FROM " . MYSQL_TRD_DB . "." . MYSQL_TRD_MAIN_TABLE . " WHERE name like '%" . $name . "%' ORDER BY datetime DESC";
		$lastbidrate = mysqli_query($connloc, $sql);
		if ($lastbidrate->num_rows>0){
			if ($row = $lastbidrate->fetch_assoc()){
				$datetime = $row['datetime'];
				$bid = $row['bid'];
				$ask = $row['ask'];
				$diff = $row['diff'];
				echo "[last bid = " . formatstr($bid,16) . "] [last ask = " . $ask . "] [last diff = " . $diff . "] ";

				$lastday = substr($datetime, 0, 10);
				$lastbid = $bid;

				$sql = "SELECT avg(bid) FROM " . MYSQL_TRD_DB . "." . MYSQL_TRD_MAIN_TABLE . " WHERE name like '%" . $name . "%' AND datetime like '%" . $lastday . "%' ";
				$avgbid = mysqli_query($connloc, $sql);
				if ($avgbid->num_rows>0){
					if ($row = $avgbid->fetch_assoc()){
						$avgday = $row['avg(bid)'];
						echo " [last day avg bid = " . $avgday . "] [last known bid = " .  $lastbid. "] ";
						if ($lastbid>$avgday){
							echo "[trend = +++] ";
						} else if ($lastbid==$avgday){
							echo "[trend = 000] ";
						} else if ($lastbid<$avgday){
							echo "[trend = ---] ";
						}
						echo "\r\n";
					}
				}

			}
		}
		

	}
	echo "\r\n";
}

function formatstr($str, $len){
	$resultat = substr($str, 0, $len-1);

	for ($i=0;$i<$len-strlen($resultat)-1;$i++){
		$resultat .= " ";		
	}	

	return $resultat;

};


