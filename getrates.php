<?php

define("MYSQL_SERVER", "mysqlhostname");
define("MYSQL_USER", "mysqlusername");
define("MYSQL_PASSWORD", "mysqlpassword");
define("MYSQL_TRD_DB", "TRD");
define("MYSQL_TRD_MAIN_TABLE", "history");

$connloc = new mysqli(MYSQL_SERVER, MYSQL_USER, MYSQL_PASSWORD);
$r = mysqli_query($connloc, "CREATE DATABASE IF NOT EXISTS " . MYSQL_TRD_DB);
$sql = "CREATE TABLE IF NOT EXISTS " . MYSQL_TRD_DB . "." . MYSQL_TRD_MAIN_TABLE . " (`id` bigint(20) NOT NULL AUTO_INCREMENT, `datetime` datetime, `name` varchar(64), `bid` float, `ask` float, `diff` float, PRIMARY KEY (`id`)) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1";
$r = mysqli_query($connloc, $sql);

$previous_ger30_bid = '';

while (true) {

	$page = file_get_contents("https://rates.fxcm.com/RatesXML");

	$xml = new SimpleXMLElement($page);

	$result = $xml->xpath('/Rates/Rate');

	//echo 'result count = ' . count($result);

	for($i=0;$i<count($result);$i++){
		$symbol = (string) $result[$i]->xpath('@Symbol')[0];
		$bid = (string) $result[$i]->xpath('Bid')[0];
		$ask = (string) $result[$i]->xpath('Ask')[0];
		//echo $symbol . ' ' . $bid . ' ' . $ask . "\r\n";

		$objDateTime = new DateTime('NOW');
		//echo $objDateTime->format('c'); // ISO8601 formated datetime
		$strdt = $objDateTime->format(DateTime::ISO8601);

		if ($symbol == "GER30") {
			if ($bid != $previous_ger30_bid){

				$diff = 'NULL';
				if ($previous_ger30_bid != ''){
					$diff = (float) $bid - (float) $previous_ger30_bid;
					if ($diff>0) {
						$diff = '+' . (string) $diff; 
					}
				}

				$fp = fopen($symbol . ".txt", "a");
				if ($fp != false){
					$str = $strdt . ' ' . $symbol . ' ' . $bid . ' ' . $ask . ' ' . (string) $diff . "\r\n";
					echo $str;
					fwrite($fp, $str);
					fclose($fp);
				}

				$sql="INSERT INTO " . MYSQL_TRD_DB . "." . MYSQL_TRD_MAIN_TABLE . " (datetime, name, bid, ask, diff) VALUES ('" . $strdt . "','" . $symbol . "'," . $bid . "," . $ask . "," . $diff . ")";
				$r = mysqli_query($connloc, $sql);

   				//echo "Error MySQL: " .  mysqli_error($connloc);

				$previous_ger30_bid = $bid;
			}
		}
	}

	sleep(1);

}

//echo $page;


?>

