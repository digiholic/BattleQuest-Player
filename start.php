<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Bootstrap 101 Template</title>
 
    <!-- Bootstrap -->
    <link href="css/bootstrap.min.css" rel="stylesheet">
	<link href="css/dungeon.css" rel="stylesheet">
	<link href="css/sprites.css" rel="stylesheet">
    <link href="css/jquery-ui.css" rel="stylesheet">
    
	</head>
	<body>

	<?php
	
	function get_from_db($db, $key, $value){
		$json_object = file_get_contents('./json/database.json');
		$result_object = json_decode($json_object,true)[$db];
		return dbfilter($result_object, $key, $value);
	}
		
	function dbfilter($arr, $key, $value) {
		$result = array();
		if (empty($key) || empty($value)){
			return $arr;
		}
		foreach ($arr as $obj){
			if ($obj[$key] === $value){
				array_push($result, $obj);
			}
		}
		if (count($result) == 0){
			return array();
		} else {
			return $result;
		}
	}
		
	function sort_by_key($arr, $field){
		$names = array();
		foreach ($arr as $key => $row){
			$names[$key] = $row[$field];
		}
		array_multisort($names, SORT_ASC, $arr);
		return $arr;
							
	}
		
    if (isset($_SERVER['HTTP_COOKIE'])) {
    $cookies = explode(';', $_SERVER['HTTP_COOKIE']);
    foreach($cookies as $cookie) {
        $parts = explode('=', $cookie);
        $name = trim($parts[0]);
        setcookie($name, '', time()-1000);
        setcookie($name, '', time()-1000, '/');
		}
	}
	
	echo '<div id="dungeon-picker" class="bordered-window opacity80">';
		$dungeon_list = array('crypt','spire','lab','well','tomb','gate');
		
			
		foreach ($dungeon_list as $dungeon) {
			$db_dungeon = json_decode(file_get_contents('./json/'.$dungeon.'.json'),true)['dungeon'];
			$result_floor = $db_dungeon['floor'][0];
			
			echo '<span class="bordered-window dungeon-info-panel '.$db_dungeon['tileset'].'">';
			echo '	<h1 class="dungeon-name">'.$db_dungeon['name'].'</h1>';
			echo '	<div class="dungeon-desc opacity80">'.$db_dungeon['description'].'</div>';
			echo '	<a class="start-button text-button dungeon-play-button" onclick="playDungeon(\''.$dungeon.'\',0,'.$db_dungeon['total_characters'].','.$db_dungeon['starting_gold'].')">Play Dungeon</a></td>';
			echo '	<div class="dungeon-info-bar">';
			echo '		<div class="dungeon-players"><span class="glyphicon glyphicon-user"></span>'.$db_dungeon['total_characters'].'</div>';
			echo '		<div class="dungeon-gold"><img src="./assets/Items/Gold.png" class="info-gold"></img>'.$db_dungeon['starting_gold'].'</div>';
			echo '		<div class="dungeon-tag">';
			if ($db_dungeon['tag']) {
				echo 'Solo'; } else { echo 'Co-Op'; };
			echo '		</div>';
			echo '	</div>';
			echo '</span>';
		}
		
	echo '</div>';
	
	
	echo '<div id="player-picker" class="bordered-window opacity80">';
	echo '	<h1>SELECT YOUR FIGHTER</h1>';
	echo '	<div class="current-selections"></div>';
	echo '	<div id="hero-filters">';
	echo '		<h3>Filters</h3>';
	echo '		<div class="filter-name">';
	echo '			Search: <input class="name-input" type="text"></input>';
	echo '		</div>';
	echo '		<div class="filter-checkboxes">';
	echo '		<span class="filter-set">';
	echo '			<input type="checkbox" value="war" checked>War of Indines</input><br />';
	echo '			<input type="checkbox" value="devastation" checked>Devastation of Indines</input><br />';
	echo '			<input type="checkbox" value="fate" checked>Fate of Indines</input><br />';
	echo '			<input type="checkbox" value="promo" checked>Promotional Materials</input><br />';
	echo '		</span>';
	echo '		<span class="filter-flight">';
	echo '			<input type="checkbox" value="novice" checked>Novice</input><br />';
	echo '			<input type="checkbox" value="beginner" checked>Beginner</input><br />';
	echo '			<input type="checkbox" value="intermediate" checked>Intermediate</input><br />';
	echo '			<input type="checkbox" value="advanced" checked>Advanced</input><br />';
	echo '			<input type="checkbox" value="master" checked>Master</input><br />';
	echo '		</span>';
	echo '		</div>';
	echo '	</div>';
	
	echo '  <br /><div class="btn btn-primary">NEXT</div>';
	
	echo '	<div class="fighter-select">';
	
	$result_hero = get_from_db('hero','','');
	$result_hero = sort_by_key($result_hero,'name');
	
	foreach ($result_hero as $db_hero) {
		echo '	<div class="select-panel">';
		echo '		<div class="select-portrait '.$db_hero['pClass'].'"></div>';
		echo '		<div class="hero-name">'.$db_hero['name'].'</div>';
		echo '		<div class="hero-set hidden">'.$db_hero['game'].'</div>';
		echo '		<div class="hero-flight hidden">'.$db_hero['flight'].'</div>';
		echo '	</div>';
	}
	
	echo '	</div>';
	echo '</div>';
	?>

	<!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script>
    <!-- Include all compiled plugins (below), or include individual files as needed -->
    <script src="js/bootstrap.min.js"></script>
    
    <script src="js/jquery-1.11.3.min.js"></script>
    <script src="js/jquery-ui.js"></script>
	
	<script src="js/dungeon.js"></script>
	<script src="js/newgame.js"></script>
    
  </body>
</html>
