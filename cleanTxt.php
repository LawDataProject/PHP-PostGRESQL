<?php

ini_set('memory_limit', -1);
ini_set('max_execution_time', 999999);

//Error checking
error_reporting(E_ALL);
ini_set('html_errors', 1);
ini_set("log_errors", 1);
ini_set("track_errors", 1);
ini_set("error_log", "php-error.log");

function contents($x){
	echo '<pre>';
	print_r($x);
	echo '</pre>';
}

//get contents of csv file to clean
$fileName = 'INStatutes-raw.csv';
$fp = fopen($fileName, 'r');
//build a loop to actually pull the info into an array
$file = array();



while(!feof($fp)){
	$file[] = fgetcsv($fp);  //everytime this is invoked, it will read 1 line of the file.
}
//contents($file);
fclose($fp);
$z = 0;
$cleanStats = array();
foreach($file as $y){
	$y[0] = strtolower($y[0]);
	$cleanStats[] = preg_replace('/ style=".*?"/', '', $y[0]);
}

fclose($file);
//contents($cleanStats);

//Get the text of statutes in a text file without the tags
$txtArray = array();
foreach($cleanStats as $y){
	if(strpos($y, '<p>') !==FALSE){
		$y = str_replace('<p>', '', $y);
		$y = str_replace('</p>', '', $y);
		if($y!= '<br/>'){
			if(strpos($y, 'local acts') !== FALSE || strpos($y, 'acts') !==FALSE || strpos($y, 'p.')!==FALSE){
				continue;
			} else {
				$txtArray[] = $y;			
			}
		} else {
			continue;
		}
	}
}

echo 'this works';
contents($txtArray);
$outputFile = 'INStatutes-textOnly.txt';
file_put_contents($outputFile, implode(' \n ',$txtArray));


