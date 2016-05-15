<?php

//Error checking
error_reporting(E_ALL);
ini_set('html_errors', 1);
ini_set("log_errors", 1);
ini_set("track_errors", 1);
ini_set("error_log", "php-error.log");


function contents($x) {
	echo '<pre>';
	print_r($x);
	echo '</pre>';
}


//get contents of html file
$fileName = 'TITLE7_title7.html';
$x = file_get_contents($fileName);

//clean contents of html file
preg_match_all('/(<h2|<p).*?(h2>|p>)/', $x, $statutes);
echo htmlspecialchars($statutes[0][1]);

//write contents to csv file
$file = fopen('INStatutesFinal.csv', 'a');
$z = 0;
$statRev = array();
foreach($statutes[0] as &$y){
	$y = preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $y);
	fputcsv($file, explode(',', $y));
}

fclose($file);
contents($statutes);




/******************************************************


$data = array();
$z = 0;
foreach($links[0] as &$y){
	//echo htmlspecialchars($y).'<br>';
	preg_match_all('/".*?"/', $y, $html);
	preg_match_all('/>.*?</', $y, $title);
	$data[$z]['html'] = str_replace('"', '', $html[0][0]);
	$data[$z]['title'] = str_replace('>', '', $title[0][0]);
	$data[$z]['title'] = str_replace('<', '', $data[$z]['title']);
	$z++;
}
contents($data);
*************************************************************************************/

?>