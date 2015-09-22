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
	if ($_SERVER['REQUEST_METHOD'] === 'POST') {
		echo '<h1>POST DATA</h1>';
	}
	
	$servername = "localhost";
	$username = "root";

	$db_handle = mysql_connect($servername,$username,'');
	$db_found = mysql_select_db('bcdungeon',$db_handle);
    
	if ($db_found){
		echo '<div id="build-window" class="bordered-window opacity80">';
			$SQL_dungeon = "SELECT * FROM dungeon WHERE share=1";
			$result_dungeon = mysql_query($SQL_dungeon);
			
			echo '	<table id="dungeon-table">';
			echo '		<tr><td>Name</td><td>Description</td><td>Number of Characters</td><td>Tag Dungeon</td><td></td></tr>';
			while ($db_dungeon = mysql_fetch_assoc($result_dungeon)){
				$SQL_floor = "SELECT * FROM floor WHERE dungeon_id=".$db_dungeon['ID']." AND starting_floor=1";
				$result_floor = mysql_query($SQL_floor);
				$db_floor = mysql_fetch_assoc($result_floor);
				
				echo '	<tr>';
				echo '		<td>'.$db_dungeon['name'].'</td>';
				echo '		<td><div class="list-description">'.$db_dungeon['description'].'</div></td>';
				echo '		<td>'.$db_dungeon['total_characters'].'</td>';
				if ($db_dungeon['tag']){
					echo '	<td><div class="glyphicon glyphicon-ok"></div></td>';
				} else {
					echo '	<td><div class="glyphicon glyphicon-remove"></div></td>';
				}
				echo '		<td><a class="edit-button edit-'.$db_dungeon['ID'].'" onclick="editDungeon('.$db_dungeon['ID'].','.$db_floor['ID'].')">Edit Dungeon</a></td>';
				echo '	</tr>';
			}
			echo '	</table>';
			echo '	<div class="new-button btn btn-primary" data-toggle="modal" data-target="#createModal">Create a New Dungeon</div>';
		echo '</div>';
	}
	?>
	
	<div id="createModal" class="modal fade" role="dialog">
		<div class="modal-dialog bordered-window">
			
		<!-- Modal content-->
			<div class="armory-window">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal">&times;</button>
					<h1>Create a Dungeon</h1>
				</div>
				
				<div id="new-window" class="bordered-window opacity80">
					<form class="form-horizontal">
					<fieldset>

					<!-- Text input-->
					<div class="control-group">
					  <label class="control-label" for="dungeon-name">Dungeon Name</label>
					  <div class="controls">
						<input id="dungeon-name" name="dungeon-name" type="text" placeholder="" class="input-large" required="">
						
					  </div>
					</div>

					<!-- Textarea -->
					<div class="control-group">
					  <label class="control-label" for="dungeon-description">Description</label>
					  <div class="controls">                     
						<textarea id="dungeon-description" name="dungeon-description"></textarea>
					  </div>
					</div>

					<!-- Text input-->
					<div class="control-group">
					  <label class="control-label" for="dungeon-gold">Starting Gold</label>
					  <div class="controls">
						<input id="dungeon-gold" name="dungeon-gold" type="text" placeholder="" class="input-small number" required="">
						
					  </div>
					</div>

					<!-- Text input-->
					<div class="control-group">
					  <label class="control-label" for="dungeon-players">Number of Players</label>
					  <div class="controls">
						<input id="dungeon-players" name="dungeon-players" type="text" placeholder="" class="input-mini number" required="">
						
					  </div>
					</div>

					<!-- Multiple Radios -->
					<div class="control-group">
					  <label class="control-label" for="dungeon-tag">Type</label>
					  <div class="controls">
						<label class="radio" for="dungeon-tag-0">
						  <input type="radio" name="dungeon-tag" id="dungeon-tag-0" value="Tag Dungeon" checked="checked">
						  Tag Dungeon
						</label>
						<label class="radio" for="dungeon-tag-1">
						  <input type="radio" name="dungeon-tag" id="dungeon-tag-1" value="Simultaneous Dungeon">
						  Simultaneous Dungeon
						</label>
					  </div>
					</div>

					</fieldset>
					<button type="submit" class="btn btn-default">Submit</button>
				</form>

				
				</div>
				
				
				
			</div>
		</div>
	</div>
    
	
	
	
	<!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script>
	<!-- Include all compiled plugins (below), or include individual files as needed -->
	<script src="js/bootstrap.min.js"></script>
   
	<script src="js/jquery-1.11.3.min.js"></script>
	<script src="js/jquery-ui.js"></script>
	<script src="js/jquery.textfill.js"></script>
	
	
	<script src="js/build.js"></script>
  </body>
  
</html>
