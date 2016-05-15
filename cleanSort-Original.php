<?php

//Error checking
error_reporting(E_ALL);
ini_set('html_errors', 1);
ini_set("log_errors", 1);
ini_set("track_errors", 1);
ini_set("error_log", "php-error.log");

require('/home/kricrone/things.php');

if($conn = pg_connect("host=recall.ils.indiana.edu port=5433 user=kricrone password=".$password)) {
	//Do nothing
} else {
	die('PSQL connection could not be completed');
}


ini_set('memory_limit', -1);
ini_set('max_execution_time', 999999);


//clean and sort data into array then send to csv
function contents($x){
	echo '<pre>';
	print_r($x);
	echo '</pre>';
}

//get contents of csv file to clean
$fp = fopen('INStatutesFinal.csv', 'r');
//build a loop to actually pull the info into an array
$file = array();

while(!feof($fp)){
	$file[] = fgetcsv($fp);  //everytime this is invoked, it will read 1 line of the file.
}
//contents($file);
fclose($fp);
$z = 0;
$cleanStats = array();
$find = array('/ style=".*?"/', '/<a name=".*?">/', '/&zwnj;/', '/class=".*?"/', '/<\\/a>/');
foreach($file as $y){
	$cleanStats[] = preg_replace($find, '', $y[0]);
}

fclose($file);

foreach($cleanStats as $key => $val) {
	if($key >= 182170  && $key <= 182180) {
		echo htmlspecialchars($val)."<BR>";
	} elseif($key >182180){
		exit;
	}
}


$recordPosition = 'title';
$dummy = array_fill_keys(array('title', 'subtitle', 'content'), array());


foreach($cleanStats as $key => $val) {
	
	
	if(strpos($val, '<p><br') !== false){
		$val = '<p><br/></p>';
	} elseif(strpos($val, '<p >') !== false) {
		$val = preg_replace('<p >', '<p>', $val);
	}
		

	if($recordPosition == 'content' && $val == '<p><br/></p>'){
		$recordPosition = 'title';
		continue;

	} elseif($recordPosition == 'title' && strpos($val, '<h2>I') !== false) {  //dump to PSQL and start new $dummy with Title
		$dummy['content'] = implode(', ', $dummy['content']);
		$dummy['title'] = implode(', ', $dummy['title']);
		$dummy['subtitle'] = implode(', ', $dummy['subtitle']);

		//Write $dummy to PSQL
		$result = pg_prepare($conn, "", "INSERT INTO instats (title, subtitle, content) VALUES ($1, $2, $3)");
		$result = pg_execute($conn, "", array_values($dummy));

		//reset array and record position
		$dummy = array_fill_keys(array('title', 'subtitle', 'content'), array());
		
		//save title value and set recordPosition to subtitle
		$val = str_replace('<h2>', '', $val);
		$val = str_replace('</h2>', '', $val);
		$dummy['title'][] = $val;
		$recordPosition = 'subtitle';
	} elseif($recordPosition == 'title' && strpos($val, '<p>') !== false) {
		if($val == '<p><br/></p>') {
			$recordPosition = 'title';
			continue;
		} else {
			$val = str_replace('<p>', '', $val);
			$val = str_replace('</p>', '', $val);
			$dummy['content'][] = $val;
		}
		
	} elseif($recordPosition == 'subtitle' && strpos($val, '<h2>') !== false) {	//Subtitle
		$val = str_replace('<h2>', '', $val);
		$val = str_replace('</h2>', '', $val);
		$dummy['subtitle'][] = $val;
		$recordPosition = 'content';

	} elseif($recordPosition == 'subtitle' && strpos($val, '<p>') !== false) {
		if($val == '<p><br/></p>'){
			$recordPosition = 'title';
			continue;

		} else {
			$val = str_replace('<p>', '', $val);
			$val = str_replace('</p>', '', $val);
			$dummy['content'][] = $val;
			$recordPosition = 'content';
		}
		

	} elseif($recordPosition == 'content' && strpos($val, '<p>') !== false) { //Content
		if($val == '<p><br/></p>') {
			$recordPosition = 'title';
			continue;
		} else {
			$val = str_replace('<p>', '', $val);
			$val = str_replace('</p>', '', $val);
			$dummy['content'][] = $val;
		}

	} elseif($recordPosition == 'content' && strpos($val, '<h2>') !== false) {
		if(strpos($val, '<h2>I') !==false) {
			$dummy['content'] = implode(', ', $dummy['content']);
			$dummy['title'] = implode(', ', $dummy['title']);
			$dummy['subtitle'] = implode(', ', $dummy['subtitle']);

			//Write $dummy to PSQL
			$result = pg_prepare($conn, "", "INSERT INTO test2 (title, subtitle, content) VALUES ($1, $2, $3)");
			$result = pg_execute($conn, "", array_values($dummy));

			//reset array and record position
			$dummy = array_fill_keys(array('title', 'subtitle', 'content'), array());

			//save title value and reset recordPosition to subtitle
			$val = str_replace('<h2>', '', $val);
			$val = str_replace('</h2>', '', $val);
			$dummy['title'][] = $val;
			$recordPosition = 'subtitle';

		} else {
			$val = str_replace('<h2>', '', $val);
			$val = str_replace('</h2>', '', $val);
			$dummy['subtitle'][] = $val;
			$recordPosition = 'content';
		}


	} elseif(is_empty($val)){
		exit;

	} else {
		die("Inconsistent structure at row $key => $value");
	}
	
}
echo 'finished without error';


/****************************************************
To write the contents of the PSQL to csv:
first:
$result = pg_query("SELECT * FROM dylan");   //anything we type in here will run as if we typed it into putty
$dylanAlbums = pg_fetch_all($result);

This will output an array that I can loop through and then use fputcsv

**********************************************/

