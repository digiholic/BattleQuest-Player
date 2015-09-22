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
    
    <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script>
    <!-- Include all compiled plugins (below), or include individual files as needed -->
    <script src="js/bootstrap.min.js"></script>
    
    <script src="js/jquery-1.11.3.min.js"></script>
    <script src="js/jquery-ui.js"></script>
	
  </head>
  <body>
  <h1 id="load-text">LOADING...</h1>
  <?php
  $dungeon = (isset($_COOKIE['dungeon']) && !empty($_COOKIE['dungeon'])) ? $_COOKIE['dungeon'] : false;
  $floor = (isset($_COOKIE['floor']) && !empty($_COOKIE['floor'])) ? $_COOKIE['floor'] : false;
  
  echo $dungeon;
  echo $floor;
  if ($dungeon && $floor){
	  echo '<script>$(document).ready(function() { window.location.replace("./play.php?dungeon='.$dungeon.'&floor='.$floor.'");  });</script>';
  }
  ?>
  
  </body>
</html>
