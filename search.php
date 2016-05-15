<html><head><title></title></head>
<body>

<?php

function contents($x){
	echo '<pre>';
	print_r($x);
	echo '</pre>';
}

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

$word = $_POST['search'];

$result = pg_query("SELECT * FROM instats WHERE content LIKE '%".$word."%'") or die('Query failed: '.pg_last_error());
while($row = pg_fetch_array($result, NULL, PGSQL_ASSOC)) {
	$data[] = $row;
}

$searchResult = pg_fetch_all($result);




//checks to see if post is empty.  If not, prints contents of post
if(!empty($_POST)) {
	contents($_POST);
}



echo '<form method="POST" action="'.$_SERVER['PHP_SELF'].'">';

echo '<h3> Enter one word to search Indiana Code Titles 1-7 </h3>';
echo '<input type="text" name="search">';
echo '<br><br>';

echo '<input type="Submit" value="Submit">';
echo '</form>';

contents($searchResult);

?>

</body>
</html>