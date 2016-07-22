<?php

/*DELETE history FROM history
LEFT OUTER JOIN (
SELECT MIN(id) as id, datetime, bid, ask, diff
FROM history
GROUP BY datetime, bid, ask, diff
) as t1
ON history.id = t1.id
WHERE t1.id IS NULL*/

define("MYSQL_SERVER", "localhost");
define("MYSQL_USER", "");
define("MYSQL_PASSWORD", "");
define("MYSQL_TRD_DB", "TRD");
define("MYSQL_TRD_MAIN_TABLE", "history");
$connloc = new mysqli(MYSQL_SERVER, MYSQL_USER, MYSQL_PASSWORD);
$r = mysqli_query($connloc, "CREATE DATABASE IF NOT EXISTS " . MYSQL_TRD_DB);
$sql = "CREATE TABLE IF NOT EXISTS " . MYSQL_TRD_DB . "." . MYSQL_TRD_MAIN_TABLE . " (`id` bigint(20) NOT NULL AUTO_INCREMENT, `datetime` datetime, `name` varchar(64), `bid` float, `ask` float, `diff` float, PRIMARY KEY (`id`)) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1";
$r = mysqli_query($connloc, $sql);

//$scannedsymbol = "AUDCAD";

$previous_symbol_bid = array();

while (true) {
	$page = file_get_contents("https://rates.fxcm.com/RatesXML");
	//echo $page;

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
		//if ($symbol == $scannedsymbol) {
			if ($bid != $previous_symbol_bid[$symbol]){
				$diff = 'NULL';
				if ($previous_symbol_bid[$symbol] != ''){
					$diff = (float) $bid - (float) $previous_symbol_bid[$symbol];
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
				$previous_symbol_bid[$symbol] = $bid;
			}
		//}
	}
	sleep(1);
}
?>
