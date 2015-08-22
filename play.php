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
			if (!isset($key)){
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
		
		$dungeon = (isset($_GET['dungeon']) && !empty($_GET['dungeon'])) ? $_GET['dungeon'] : 'ancella';
		$floor = (isset($_GET['floor'])) ? $_GET['floor'] : 0;
		
		setcookie('dungeon',$dungeon);
		setcookie('floor',$floor);
		
		//DEBUG COOKIES
		//setcookie('items','0,1,3:0,2,1');
		//setcookie('gold',100);
		//setcookie('health','20:20:20');
		
		//Cookie Info
		$health = (isset($_COOKIE['health']) && !empty($_COOKIE['health'])) ? $_COOKIE['health'] : '';
		$heros = (isset($_COOKIE['heros']) && !empty($_COOKIE['heros'])) ? $_COOKIE['heros'] : '';
		$items = (isset($_COOKIE['items']) && !empty($_COOKIE['items'])) ? $_COOKIE['items'] : '';
		$gold = (isset($_COOKIE['gold']) && !empty($_COOKIE['gold']))? $_COOKIE['gold'] : 0;
		$weapons = (isset($_COOKIE['weapons']) && !empty($_COOKIE['weapons'])) ? $_COOKIE['weapons'] : '';
		
		$players = array();
		
		$monsters = array();
		
		$number = 0;
		
		$hero_list = split(':',$heros);
		$health_list = split(':',$health);
		
		//Load from DB
		$db_dungeon = json_decode(file_get_contents('./json/'.$dungeon.'.json'),true)['dungeon'];
		$db_floor = $db_dungeon['floor'][$floor];
		
		//Get Armor for player HP setting
		$result_item = get_from_db('item','type','Armor');
		
		foreach ($hero_list as $hero){
			//If their health is not set, set it to 20, else use the set number
			if (!array_key_exists($number, $health_list) || $health_list[$number] == ''){
				$hp = 20;
			} else {
				$hp = $health_list[$number];
			}
			
			//set them as active or inactive based on if the dungeon is tag or simultaneous
			$activeState = false;
			if (!$db_dungeon['tag']){
				$activeState = true;
			}
			
			//build the hero object
			$hero = array(  "name" => $hero,
							"currentHealth" => $hp,
							"maxHealth" => 20,
							"pClass" => 'player-'.strtolower($hero),
							"location" => $db_floor['startlocation'],
							"number" => $number,
							"yOffset" => -10 + (5*$number),
							"xOffset" => 20 + (-10*$number),
							"active" => $activeState,
							"inventory" => array(),
							"weapons" => array());
		
			array_push($players, $hero);
		
			$number += 1;
		}
		
		//No matter what the active state of the players are, the first one is always active
		$players[0]['active'] = true;
		
		
		if ($weapons === ''){
			//if weapons are not set, default them to standard bases
			for ($i = 0; $i < count($players); $i++){
				$weapons .= '64,65,66,67,68,69:';
				array_push($players[$i]['weapons'],'64','65','66','67','68','69');
			}
			$weapons = rtrim($weapons,':');
			setcookie('weapons',$weapons);
		} else {
			//rebuild weapons from list
			$weapons_array = explode(':',$weapons);
			for ($i = 0; $i < count($players); $i++){
				$player_weapons = explode(',',$weapons_array[$i]);
				foreach ($player_weapons as $wep){
					array_push($players[$i]['weapons'],$wep);
				}
			}
		}
		
		//Decode item cookie into item information
		$item_array = array();
		if ($items != 'null' && strlen($items) > 0){
			$its = explode(':',$items);
			foreach ($its as $it){
				list($hero_id,$item_id,$count) = explode(',',$it);
				array_push($item_array, array("hero_id" => $hero_id, "item_id" => $item_id, "count" => $count));
			}
		}
		
		//Set max health based off of armor
		foreach($result_item as $armor){
			foreach ($item_array as $it){
				if ($armor['ID'] == $it['item_id']){
					//The description has the max life included
					$newHealth = (int) preg_replace('/\D/', '', $armor['description']);
					if ($newHealth > $players[$it['hero_id']]['maxHealth']){
						$players[$it['hero_id']]['maxHealth'] = $newHealth;
					}
				}
			}
		}
		
		foreach ($item_array as $it){
			array_push($players[$it['hero_id']]['inventory'],$it);
		}
		
		$description = $db_floor['description'];
		
		$floor_monsters = array();
		
		if (strlen($db_floor['monsters']) > 0){
			$mons = explode(';',$db_floor['monsters']);
			
			foreach ($mons as $mon){
				list($monster_id, $location) = explode(',',$mon);
				array_push($floor_monsters, array("monster_id" => $monster_id,"location"=> $location));
			}
		}
		
		foreach ($floor_monsters as $monster){
			$db_bank = $db_dungeon['monster'][$monster['monster_id']];
			
			$mon = array(   "name" => $db_bank['name'],
							"currentHealth" => $db_bank['maxHealth'],
							"maxHealth" => $db_bank['maxHealth'],
							"pClass" => $db_bank['pClass'],
							"location" => $monster['location'],
							"number" => $number,
							"attacks" => array(),
							"attributes" => array());
			
			$result_attack = $db_bank['attack'];
			
			foreach ($result_attack as $db_attack) {
				$attack = array(    "name" => $db_attack['name'],
									"range" => $db_attack['attackrange'],
									"power" => $db_attack['power'],
									"priority" => $db_attack['priority'],
									"description" => $db_attack['description'],
									"chance" => $db_attack['chance']);
				
				array_push($mon['attacks'],$attack);
			}
			
			$result_attribute = $db_bank['attribute'];
			
			foreach ($result_attribute as $db_attribute){
				$attribute = array ("name" => $db_attribute['name'],
									"description" => $db_attribute['description']);
				array_push($mon['attributes'],$attribute);
			}
			
			$number += 1;
			array_push($monsters, $mon);
		}
		
		$names = array();
		foreach ($monsters as $monster){
			if (in_array($monster['name'],$names)){
				$name = $monster['name'];
				$letter = 'A';
				foreach ($monsters as &$mon2){
					if ($mon2['name'] == $name) {
						$mon2['name'] = $mon2['name'].' '.$letter;
						$letter++;
					}
				}
			} else {
				array_push($names,$monster['name']);
			}
		}
		
		echo '<div id="player-window" class="column-window">';
		echo '	<div class="gold-window bordered-window opacity80">';
		echo '		<img src="./assets/Items/Gold.png" title="Open Armory" data-toggle="modal" data-target="#armoryModal"></img>';
		echo '		<div class="gold-count"><span class="gold-number">'.$gold.'</span>';
		echo '		<div class="gold-change">';
		echo '			<span class="sub-gold">';
		echo '				<span class="text-button">-10</span>';
		echo '				<span class="text-button">-5</span>';
		echo '				<span class="text-button">-1</span>';
		echo '			</span>';
		echo '			<span class="add-gold">';
		echo '				<span class="text-button">+1</span>';
		echo '				<span class="text-button">+5</span>';
		echo '				<span class="text-button">+10</span>';
		echo '			</span>';
		echo '		</div>';
		echo '		</div>';
		echo '	</div>';
		
		echo '	<div class="marker-window bordered-window opacity80">';
		echo '		<img class="marker-image" src="./assets/Icons/Target.png" title="Add Marker" data-toggle="modal" data-target="#markerModal"></img>';
		echo '		<div class="marker-text">Add Marker</div>';
		echo '  </div>';
		
		foreach ($players as $player){
			$active = ($player['active'] ? 'active-player' : 'inactive-player');
			$str = '';
			$str .= '<div class="game-panel opacity80 char-'.$player['number'].' '.$active.'">';
			$str .=	'	<div class="'.$player['pClass'].' panel-sprite flipped-image" title="'.$player['name'].'"></div>';
			$str .=	'	<h1 class="player-name name-plate">'.$player['name'].' </h1>';
			$str .=	'	<div class = "healthbar">';
			$str .=	'		<span class="plus-button glyphicon glyphicon-plus text-button" title="Add Health"></span>';
			$str .=	'	<span class="progress">';
			$width = (($player['currentHealth']) / ($player['maxHealth'])) * 100;
			$str .=	'		<div class="progress-bar player-healthbar" role="progressbar" style="width: '.$width.'%;">';
			
			/* Armor Bar, it's clunky and I think I'll do something else.
			if ($player['currentHealth'] > 20){
				$str .=	'	<span class="progress" style="background-color: #008000">';
				$width = (($player['currentHealth']-20) / 30) * 100;
				$str .=	'		<div class="progress-bar player-healthbar" role="progressbar" style="width: '.$width.'%; background-color:#c0c0c0;">';
			}
			else {
				$str .=	'	<span class="progress">';
				$width = (($player['currentHealth']) / 20) * 100;
				$str .=	'		<div class="progress-bar player-healthbar" role="progressbar" style="width: '.$width.'%">';
			}
			**/
			$str .=	'				<span> '.$player['currentHealth'].' / '.$player['maxHealth'].'</span>';
			$str .=	'			</div>';
			$str .=	'		</span>';
			$str .=	'		<span class="minus-button glyphicon glyphicon-minus text-button" title="Subtract Health"></span>';
			$str .=	'	</div>';
			$str .=	'	<div class="arrow-bar hidden">';
			$str .=	'		<span class="left-arrow glyphicon glyphicon-arrow-left text-button" title="Move Left"></span>';
			$str .=	'		<span class="right-arrow glyphicon glyphicon-arrow-right text-button" title="Move Right"></span>';
			$str .=	'	</div>';
			$str .= '	<div class="tag-bar hidden">';
			$str .= '		<span class="tag-button glyphicon glyphicon-share text-button" title="Tag in"></span>';
			$str .= '	</div>';
			$str .= '	<div class="expand-inventory text-button hidden"><span class="glyphicon glyphicon-triangle-right"></span> View Item Effects</div>';
			
			$str .= '	<div class="inventory">';
			foreach ($player['inventory'] as $item){
				$db_item = get_from_db('item','ID',$item['item_id'])[0];
				
				$itemType = $db_item['type'];
				
				$str .= '	<div class="inventory-line inventory-'.strtolower($itemType).' itemdb-'.$item['item_id'].'">';
				$str .= '		<div class="item-header">';
				if ($itemType == 'Consumable' || $itemType == 'Key'){
					$str .= '	<span class="item-count">'.$item['count'].'</span>';
				} elseif ($itemType == 'Trinket'){
					$str .= '	<span class="item-count item-cooldown" style="display:none">0</span>';
				}
				$str .= '		<span class="item-small '.$db_item['css-class'].'"></span>';
				$str .= '		<span class="item-name">'.$db_item['name'].'</span>';
				$str .= '	</div>';
				$str .= '	<div class="item-desc opacity80" style="display:none">'.$db_item['description'].'</div>';
				$str .= '	</div>';
				
			}
			$str .= '	</div>';
			$str .= '</div>';
			echo $str;
		}
		echo '</div>';
		echo '<div id="monster-window" class="column-window">';
		echo '<div class="current-char-number hidden">'.$number.'</div>';
		foreach ($monsters as $monster){
			$str = '';
			$str .= '<div class="monster-panel game-panel opacity80 char-'.$monster['number'].'">';
			$str .= '	<div class="hide-monster text-button glyphicon glyphicon-remove"></div>';
			$str .= '	<div class="'.$monster['pClass'].' panel-sprite" title="'.$monster['name'].'"></div>';
			$str .= '	<h1 class="player-name name-plate"> '.$monster['name'].' </h1>';
			$str .= '	<div class = "healthbar">';
			$str .= '		<span class="plus-button glyphicon glyphicon-plus text-button" title="Add Health"></span>';
			$str .= '		<span class="progress">';
			$str .= '			<div class="progress-bar monster-healthbar" role="progressbar" style="width: 100%">';
			$str .= '				<span> '.$monster['maxHealth'].' / '.$monster['maxHealth'].'</span>';
			$str .= '			</div>';
			$str .= '		</span>';
			$str .= '		<span class="minus-button glyphicon glyphicon-minus text-button" title="Subtract Health"></span>';
			$str .= '	</div>';
			$str .= '	<div class="arrow-bar hidden">';
			$str .= '		<span class="left-arrow glyphicon glyphicon-arrow-left text-button" title="Move Left"></span>';
			$str .= '		<span class="right-arrow glyphicon glyphicon-arrow-right text-button" title="Move Right""></span>';
			$str .= '	</div>';
			$str .= '	<img class="roll-attack btn btn-default" src="./assets/Icons/1Dice.png" title="Roll a new attack (without advancing the beat counter)"></img>';
			
			if (count($monster['attributes']) > 0){
				$str .= '<div class="attribute-group">';
				foreach ($monster['attributes'] as $attribute){
					$str .= '<div class="monster-attributes">';
					$str .= '<span class="attribute-name">'.$attribute['name'].'</span>';
					$str .= '<span class="glyphicon glyphicon-plus expand-attribute text-button" title="Expand attribute"></span>';
					$str .= '<div class="attribute-description collapse">';
					$str .= '	<p>'.$attribute['description'].'</p>';
					$str .= '</div>';
					$str .= '</div>';
				}
				$str .= '</div>';
			}
			$str .= '	<div class="monster-attacks hidden">';
			$str .= '		<p class="collapse-toggle">';
			$str .= '			<span class="glyphicon glyphicon-plus text-button"></span>';
			$str .= '			View Attacks';
			$str .= '		</p>';
			$str .= '		<div class="collapse-attacks collapse">';
			
			foreach ($monster['attacks'] as $attack){
				$str .= '   	<div class="attack-panel">';
				$str .= '			<div class="attack-name">'.$attack['name'].'</div>';
				$str .= '			<span class="attack-range attack-stat">'.$attack['range'].'</span';
				$str .= '			><span class="attack-power attack-stat">'.$attack['power'].'</span';
				$str .= '			><span class="attack-priority attack-stat">'.$attack['priority'].'</span>';
				$str .= '			<div class="attack-desc"><div class="attack-desc-text">'.$attack['description'].'<div class="attack-chance">'.$attack['chance'].'</div></div></div>';
				$str .= '		</div>';
			}
			$str .= '		</div>';
			$str .= '	</div>';
			$str .= '</div>';
			echo $str;
		}
		if ($db_floor['adds']){
			echo '<div class="add-monsters-window bordered-window opacity80" title="Add a new monster" data-toggle="modal" data-target="#monsterModal">';
			echo '	<div class="add-plus-button btn btn-success"><span class="glyphicon glyphicon-plus"></span></div>';
			echo '	<div class="add-monster-text">Add a monster</div>';
			echo '</div>';				
		}
		echo '<div class="roll-die-window bordered-window opacity80" title="Roll a die">';
		echo '	<img class="roll-die" src="./assets/Icons/1Dice.png" title="Roll a die"></img>';
		echo '	<div class="roll-die-text">Roll a die.';
		echo '	<div class="roll-result">0</div></div>';
		echo '</div>';
		echo '</div>';
		
		//Junctions that end here (previous floors)
		echo '<div id="mainGameWindow" class="row-window '.$db_dungeon['tileset'].'" style="display: none">';
		
		$junction_result = array();
		foreach ($db_dungeon['floor'] as $id => $next_floor){
			$nexts = split(',',$next_floor['next']);
			foreach ($nexts as $next){
				if ($next != ""){
					if (intval($next) == $floor){
						array_push($junction_result,$id);
					}
				}
			}
		}
		
		if (count($junction_result) != 0){
			echo '  <div class="previous-floors bordered-window opacity80">';
			echo '		<span class="glyphicon glyphicon-backward floor-backward"></span>';
			echo '		<span><select class="dropdown floor-selector">';
			foreach ($junction_result as $db_junction) {
				$prev = $db_dungeon['floor'][intval($db_junction)];
				echo '		<option value="'.$db_junction.'">'.$prev['name'].'</option>';
			}
			echo '		</select></span>';
			echo '	</div>';
		}
		
		$junction_result = split(',',$db_floor['next']);
		//Gets rid of empty strings but not zeroes.
		$junction_result = array_diff($junction_result, array(""));
		
		if (count($junction_result) != 0){
			echo '  <div class="next-floors bordered-window opacity80">';
			echo '		<span><select class="dropdown floor-selector">';
			foreach ($junction_result as $db_junction) {
				$next = $db_dungeon['floor'][intval($db_junction)];
				echo '		<option value="'.$db_junction.'">'.$next['name'].'</option>';
			}
			echo '		</select></span>';
			echo '		<span class="glyphicon glyphicon-forward floor-forward"></span>';
			echo '	</div>';
		}
		
		echo '	<div class="restart-floor bordered-window opacity80">';
		echo '		<span class="glyphicon glyphicon-refresh text-button" title="Restart Floor"></span>';
		echo '  </div>';
		
		echo '	<div class="space-row">';
		
		$decision = false;
		$number = 1;
		if ($db_floor['floor_size'] == "small"){
			$maxSize = 5;
		} elseif ($db_floor['floor_size'] == "medium") {
			$maxSize =7;
		} elseif ($db_floor['floor_size'] == "large") {
			$maxSize = 9;
		} else {
			$decision = true;
		}
		if (!$decision){
			$spaces = array();
			$bgs = array();
			
			$space_classes = explode(';',$db_floor['space_classes']);
			$space_backgrounds = explode(';',$db_floor['bg_classes']);
			foreach ($space_classes as $space_class){
				$space = explode(',',$space_class);
				if (count($space) == 2){
					$spaces[$space[0]] = $space[1];
				}
			}
			
			foreach ($space_backgrounds as $space_background){
				$bg = explode(',',$space_background);
				if (count($bg) == 2){
					$bgs[$bg[0]] = $bg[1];
				}
			}
			
			while ($number <= $maxSize){
				//For dungeons that alternate even and odd
				$evenoddclass = 'space-odd';
				if ($number % 2 == 0){
					$evenoddclass = 'space-even';
				}
				
				if (array_key_exists($number,$spaces)){
					echo '<span class="space '.$db_dungeon['tileset'].' space-'.$number.' '.$evenoddclass.' '.$spaces[$number].'">';
				} else {
					echo '<span class="space '.$db_dungeon['tileset'].' space-'.$number.' '.$evenoddclass.'">';
				}
				
				if (array_key_exists($number,$bgs)){
					echo '<span class="space-bg '.$db_dungeon['tileset'].' '.$bgs[$number].'"></span>';
				} else {
					echo '<span class="space-bg '.$db_dungeon['tileset'].'"></span>';
				}
				echo '<span class="space-dropzone"></span>';
				foreach ($players as $player){
					if ($player['location'] == $number){
						if ($player['active']){
							echo '<div class="game-sprite flipped-image char-'.$player['number'].' '.$player['pClass'].'" style="background-position: '.$player['xOffset'].'px '.$player['yOffset'].'px; z-index: '.($player['yOffset'] + 10).'">';
							echo '</div>';
						}
					}
				}
				foreach ($monsters as $monster){
					if ($monster['location'] == $number){
						//$xOff = rand(-10, 10);
						$xOff = 0;
						$yOff = rand(-10, 10);
				
						echo '<div class="game-sprite char-'.$monster['number'].' '.$monster['pClass'].'" style="background-position: '.$xOff.'px '.$yOff.'px; z-index: '.($yOff + 10).'">';
						echo '</div>';
					}
				}
				echo '</span>';
				$number++;
			}
		}
		echo '	</div>'; // end space-row
			
		if ($decision){
			echo '	<div class="description-window decision-window opacity80">'.$description.'</div>';
		} else {
			echo '	<div class="description-window opacity80">'.$description.'</div>';
		}
		echo '</div>'; // end mainGameWindow
		?>
		
		<div id="attack-window" class="row-window bordered-window opacity80" style="display: none">
			<!-- foreach attack -->
			<span class="beat-count">0</span>
			<img class="roll-attacks btn btn-default" src="./assets/Icons/2Dice.png" title="Roll new attacks"></img>
		</div>
		
			
	<!-- Armory Modal -->
	<div id="armoryModal" class="modal fade" role="dialog">
		<div class="modal-dialog bordered-window">
			
		<!-- Modal content-->
			<div class="armory-window">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal">&times;</button>
					<?php
					foreach ($players as $player){
						$selected = ($player['number'] == 0 ? 'selected':'');
						echo '<div class="armory-player '.$selected.' player-'.strtolower($player['name']).' char-'.$player['number'].'"></div>';
					}
					?>
				</div>
				<?php
					echo '<div class="inventory-scroll">';
					foreach (array("Consumable","Trinket","Armor","Relic","Key") as $type){
						$result_item = get_from_db('item','type',$type);
						
						echo '<h2>'.$type.'s</h2>';
						echo '<div class="section-'.strtolower($type).'">';
						foreach ($result_item as $db_item) {
							if (strpos($type, 'Weapon') == false){
								echo '<div class="armory-panel armory-'.strtolower($type).'">';
							}
							else {
								echo '<div class="armory-panel armory-'.strtolower(substr($type,0,strpos($type,' '))).'">';	
							}
							echo '	<div class="shop-title">'.$db_item['name'].'</div>';
							$string = str_replace('"', "", $db_item['description']);
							echo '	<div class="armory-item '.$db_item['css-class'].' itemdb-'.$db_item['ID'].'" title="'.strip_tags($string).'"></div>';
							echo '  <div class="armory-desc" style="display:none">'.$db_item['description'].'</div>';
							echo '	<div class="shop-tab">';
							echo '		<span class="shop-cost">';
							echo '			<span class="cost-icon"></span>';
							echo '			<span class="cost-total">'.$db_item['cost'].'</span>';
							echo '		</span>';
							echo '		<span class="glyphicon glyphicon-plus text-button" title="Add to inventory (without spending gold)"></span>';
							echo '		<span class="glyphicon glyphicon-usd text-button" title="Purchase item"></span>';
							echo '	</div>';
							echo '</div>';
						}
						echo '</div>';
					}
					echo '<h2>Weapons</h2>';
					echo '<div class="weapon-window">';
					echo '	<div class="weapons-column opacity80">';
					
					foreach (array("Beta","Delta","EX","Almighty","Weapon","Special") as $type){
						if ($type == "Weapon" || $type == "Special"){
							$result_weapons = get_from_db('weapon','type',$type);
							
							//sort by name
							$result_weapons = sort_by_key($result_weapons,'name');
						} else {
							$result_weapons = get_from_db('weapon','type',$type);
						}
						echo '<div class="group-line group-'.strtolower($type).'"><span class="glyphicon glyphicon-triangle-right"></span>'.$type.'</div>';
						
						echo '<div class="weapon-group group-'.strtolower($type).' collapse">';
						foreach ($result_weapons as $db_weapon) {
							echo '	<div class="weapon-line dbweapon-'.$db_weapon['ID'].' color-'.$db_weapon['color'].' type-'.strtolower($db_weapon['type']).'">'.$db_weapon['name'].'</div>';
						}
						echo '</div>';
					}
					
					echo '	</div>';
					
					echo '	<div class="purchase-column">';
					echo '		<div class="buy-type">';
					echo '			<span class="add-button selected glyphicon glyphicon-plus" title="Add to inventory (without spending gold)"></span>';
					echo '			<span class="buy-button glyphicon glyphicon-usd" title="Purchase weapon"></span>';
					echo '		</div>';
					echo '		<div class="weapon-button-row">';
					echo '			<span class="match-weapon btn btn-default disabled"><span class="glyphicon glyphicon-retweet" title="Replace matching base"></span></span>';
					echo '			<span class="match-cost weapon-cost"> 5</span><br />';
					echo '		</div>';
					echo '		<div class="weapon-button-row">';
					echo '			<span class="replace-weapon btn btn-default disabled"><span class="glyphicon glyphicon-random" title="Replace any base"></span></span>';
					echo '			<span class="replace-cost weapon-cost">10</span><br />';
					echo '		</div>';
					echo '		<div class="weapon-button-row">';
					echo '			<span class="add-weapon btn btn-default disabled"><span class="glyphicon glyphicon-chevron-right" title="Add to bases"></span></span>';
					echo '			<span class="add-cost weapon-cost">25</span><br />';
					echo '		</div>';
					echo '  </div>';
									   
					foreach ($players as $player){
						$class='hidden';
						if ($player['number'] == 0){
							$class='active';
						}
						echo '<div class="current-weapons '.$class.' char-'.$player['number'].' opacity80">';
						foreach ($player['weapons'] as $i=>$weapon){
							$db_weapon = get_from_db('weapon','ID',$weapon)[0];
							echo '<div class="weapon-line dbweapon-'.$db_weapon['ID'].' color-'.$db_weapon['color'].' type-'.strtolower($db_weapon['type']).'">'.$db_weapon['name'].'</div>';
						}
						echo '</div>';
							
					}
					
					echo '</div>';
					echo '</div>';
				?>
			</div>
		</div>
	</div>
    
    <!-- Monster Modal -->
	<div id="monsterModal" class="modal fade" role="dialog">
		<div class="modal-dialog bordered-window">
		
		<!-- Modal content-->
			<div class="armory-window">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal">&times;</button>
					<h1>Add a new Monster</h1>
				</div>
				<?php
				echo '<div class="monsters-scroll">';
				
				$dungeon_monsters = $db_dungeon['monster'];
				foreach ($dungeon_monsters as $id=>$db_dungeon_monster){
					
					echo '<div class="picker-panel dbmonster-'.$id.' opacity80">';
					echo '	<div class="picker-header">'.$db_dungeon_monster['name'].'</div>';
					echo '	<div class="picker-sprite '.$db_dungeon_monster['pClass'].'"></div>';
					echo '	<div class="picker-footer btn btn-primary">Add Monster</div>';
					echo '</div>';
					
					
					echo '<div class="add-monster-panel hidden dbmonster-'.$id.'">';
					$str = '';
					$str .= '<div class="monster-panel game-panel opacity80">';
					$str .= '	<div class="hide-monster text-button glyphicon glyphicon-remove"></div>';
					$str .= '	<div class="'.$db_dungeon_monster['pClass'].' panel-sprite" title="'.$db_dungeon_monster['name'].'"></div>';
					$str .= '	<h1 class="player-name name-plate"> '.$db_dungeon_monster['name'].' </h1>';
					$str .= '	<div class = "healthbar">';
					$str .= '		<span class="plus-button glyphicon glyphicon-plus text-button" title="Add Health"></span>';
					$str .= '		<span class="progress">';
					$str .= '			<div class="progress-bar monster-healthbar" role="progressbar" style="width: 100%">';
					$str .= '				<span> '.$db_dungeon_monster['maxHealth'].' / '.$db_dungeon_monster['maxHealth'].'</span>';
					$str .= '			</div>';
					$str .= '		</span>';
					$str .= '		<span class="minus-button glyphicon glyphicon-minus text-button" title="Subtract Health"></span>';
					$str .= '	</div>';
					$str .= '	<div class="arrow-bar hidden">';
					$str .= '		<span class="left-arrow glyphicon glyphicon-arrow-left text-button" title="Move Left"></span>';
					$str .= '		<span class="right-arrow glyphicon glyphicon-arrow-right text-button" title="Move Right""></span>';
					$str .= '	</div>';
					$str .= '	<img class="roll-attack btn btn-default" src="./assets/Icons/1Dice.png" title="Roll a new attack (without advancing the beat counter)"></img>';
			
					$result_monster_attribute = $db_dungeon_monster['attribute'];
					if (count($result_monster_attribute) > 0){
						$str .= '<div class="attribute-group">';
						foreach ($result_monster_attribute as $attribute) {
							$str .= '<div class="monster-attributes">';
							$str .= '<span class="attribute-name">'.$attribute['name'].'</span>';
							$str .= '<span class="glyphicon glyphicon-plus expand-attribute text-button" title="Expand attribute"></span>';
							$str .= '<div class="attribute-description collapse">';
							$str .= '	<p>'.$attribute['description'].'</p>';
							$str .= '</div>';
							$str .= '</div>';
						}
						$str .= '</div>';
					}
					
					$str .= '	<div class="monster-attacks hidden">';
					$str .= '		<p class="collapse-toggle">';
					$str .= '			<span class="glyphicon glyphicon-plus text-button"></span>';
					$str .= '			View Attacks';
					$str .= '		</p>';
					$str .= '		<div class="collapse-attacks collapse">';
					
					
					$result_monster_attacks = $db_dungeon_monster['attack'];
					foreach ($result_monster_attacks as $attack){
						$str .= '   	<div class="attack-panel">';
						$str .= '			<div class="attack-name">'.$attack['name'].'</div>';
						$str .= '			<span class="attack-range attack-stat">'.$attack['attackrange'].'</span';
						$str .= '			><span class="attack-power attack-stat">'.$attack['power'].'</span';
						$str .= '			><span class="attack-priority attack-stat">'.$attack['priority'].'</span>';
						$str .= '			<div class="attack-desc"><div class="attack-desc-text">'.$attack['description'].'<div class="attack-chance">'.$attack['chance'].'</div></div></div>';
						$str .= '		</div>';
					}
					$str .= '		</div>';
					$str .= '	</div>';
					$str .= '</div>';
					echo $str;
					echo '</div>';
				}
				
				echo '</div>';
				?>
			</div>
		</div>
		
	</div>
    
    <div id="markerModal" class="modal fade" role="dialog">
		<div class="modal-dialog modal-sm bordered-window opacity80">
		
		<!-- Modal content-->
			<div class="marker-modal-window">
				<div class="modal-header opacity80 hidden">
				</div class="modal-body">
					<button type="button" class="close" data-dismiss="modal" style="color:black">&times;</button>
					<span class="marker-label">Label: <input type="text"></input></span>
					<span class="make-marker text-button"><span class="glyphicon glyphicon-ok"></span></span>
			</div>
		</div>
		
	</div>
    
    
    <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script>
    <!-- Include all compiled plugins (below), or include individual files as needed -->
    <script src="js/jquery-1.11.3.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
    
    <script src="js/jquery-ui.js"></script>
	<script src="js/jquery.textfill.js"></script>
	
	
	<script src="js/dungeon.js"></script>
  </body>
</html>
