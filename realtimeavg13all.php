<?php

/*	realtimeavg13all.php
	If you want to scan the trend for one pair only then use it as follows : php realtimeavg13all.php | grep EURUSD
	(replace EURUSD with the needed pair to scan the trend).
	IKYTraderFramework.
	Trend detector based on the average value of ask rate of the current date.
	Prints --- if the average value of the rate is downtrend.
	Prints +++ if the average value of the rate is uptrend.
	Assumes that only one type of value is in database (here : DAX30).
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

$page = file_get_contents("https://rates.fxcm.com/RatesXML");
//echo $page;

$previous_symbol_avg_ask = array();

$xml = new SimpleXMLElement($page);
$result = $xml->xpath('/Rates/Rate');
//echo 'result count = ' . count($result);
	while(true){
	for($i=0;$i<count($result);$i++){
		$symbol = (string) $result[$i]->xpath('@Symbol')[0];
		$bid = (string) $result[$i]->xpath('Bid')[0];
		$ask = (string) $result[$i]->xpath('Ask')[0];

		$scannedsymbol = $symbol;
		//echo 'working with : ' . $scannedsymbol . "\r\n";



		$sql = "SELECT avg(ask) FROM " . MYSQL_TRD_DB . "." . MYSQL_TRD_MAIN_TABLE . " WHERE name like '%" . $scannedsymbol . "%' and datetime like '" . $strdtday . "%'";
		$r = mysqli_query($connloc, $sql);
		//echo mysqli_error($connloc) . "\r\n";
		if ($r->num_rows>0){
			while($row = $r->fetch_assoc()){
				$avg_ask = $row["avg(ask)"];
				// echo $avg_ask . " : ";
				if ($previous_symbol_avg_ask[$symbol] != ""){

					$objDateTime = new DateTime('NOW');
					$strdt = $objDateTime->format(DateTime::ISO8601);

					$sql = "SELECT * FROM " . MYSQL_TRD_DB . "." . MYSQL_TRD_MAIN_TABLE . " where name like '%" . $scannedsymbol . "%' ORDER BY datetime DESC";
					$s = mysqli_query($connloc, $sql);
					$last_ask = "";
					$last_bid = "";
					$last_diff = "";
					$name = "";
					if ($s->num_rows>0){
						$row = $s->fetch_assoc();
						$last_ask = $row["ask"];
						$last_bid = $row["bid"];
						$last_diff = $row["diff"];
						$name = $row["name"];
					}

					if ($avg_ask>$previous_symbol_avg_ask[$symbol]){
						echo $strdt . " " . $name . " " . $avg_ask . " avg ask +++" . " ; last ask = " . $last_ask . " ; last bid = " . $last_bid . "  \r\n";
					} else if ($avg_ask<$previous_symbol_avg_ask[$symbol]){
						echo $strdt . " " . $name . " " . $avg_ask . " avg ask ---" . " ; last ask = " . $last_ask . " ; last bid = " . $last_bid . " \r\n";
					} else {
						//echo "\r\n";//echo "0" . "\r\n";
					}
				} else {
					//echo "\r\n";
				}
				$previous_symbol_avg_ask[$symbol] = $avg_ask;
			}
		}
	}
	sleep(1);
}

