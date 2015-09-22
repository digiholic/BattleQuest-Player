<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>BattleQUEST Dungeon Player</title>
 
    <!-- Bootstrap -->
    <link href="css/bootstrap.min.css" rel="stylesheet">
	<link href="css/dungeon.css" rel="stylesheet">
	<link href="css/sprites.css" rel="stylesheet">
    <link href="css/jquery-ui.css" rel="stylesheet">
    
  </head>
  <body>
	  <?php
		$dungeon = (isset($_COOKIE['dungeon']) && !empty($_COOKIE['dungeon'])) ? $_COOKIE['dungeon'] : false;
		$floor = (isset($_COOKIE['floor']) && !empty($_COOKIE['floor'])) ? $_COOKIE['floor'] : false;
		
		
		echo '<div id="battlequest-title" class="bordered-window">';
		echo '<img class="battlequest-bg" src="./assets/bqbg.png"></img>';
		echo '<h1>BATTLEQUEST</h1>';
		echo '<br /><br />';
		echo '<div class="game-buttons">';
		if ($dungeon && $floor){
			echo '<a class="new-game btn btn-default" href="./start.php" onclick="return confirm(\'You already have saved data. Starting a new game will overwite that data. Continue anyway?\')">New Game</a><br />';
			echo '<a class="load-game btn btn-default" href="./load.php">Continue</a><br />';
		} else {
			echo '<a class="new-game btn btn-default" href="./start.php">New Game</a><br />';
		}
		echo '</div>';
		echo '</div>';
	  ?>
	<!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script>
    <!-- Include all compiled plugins (below), or include individual files as needed -->
    <script src="js/bootstrap.min.js"></script>
    
    <script src="js/jquery-1.11.3.min.js"></script>
    <script src="js/jquery-ui.js"></script>
	
	<script src="js/newgame.js"></script>
    
  </body>
</html>
