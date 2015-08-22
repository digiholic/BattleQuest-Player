if (jQuery.ui) {



var getCharClass = function( dom ) {
	var charClass = function( str ) {
		if (str.startsWith('char')){
			return true;
		} return false;
	}
	
	var classes = dom.attr('class').split(/\s+/);
    classes = classes.filter(charClass);
    if (classes.length > 0){
		return classes[0];
	} return false;

}

var searchClass = function( dom, cl) {
	var findClass = function( str ) {
		if (str.startsWith(cl)){
			return true;
		} return false;
	}
	
	var classes = dom.attr('class').split(/\s+/);
    classes = classes.filter(findClass);
    if (classes.length > 1){
		return classes;
	} else if (classes.length > 0){
		return classes[0];
	}
	return false;

}

var getCookie = function (name) {
    var re = new RegExp(name + "=([^;]+)");
    var value = re.exec(document.cookie);
    return (value != null) ? unescape(value[1]) : null;
}

var restartFloor = function () {
	var oldSave = getCookie('startSave');
	if (oldSave == null){
		
		oldSave = getCookie('heros')+'||||';
	}
	var oldState = oldSave.split('|');
	
	for (var i=0;i<oldState.length;i++){
		if (oldState[i] == 'null'){
			oldState[i] = '';
		}
	}
	var heros = oldState[0];
	var health = oldState[1];
	var items = oldState[2];
	var gold = oldState[3];
	var weapons = oldState[4];
	
	document.cookie = "heros="+heros;
	document.cookie = "health="+health;
	document.cookie = "items="+items;
	document.cookie = "gold="+gold;
	document.cookie = "weapons="+weapons
}

$('#mainGameWindow').ready(function() {
	var windowWidth = parseInt($('#mainGameWindow').css('width'),10);
	var number = $(this).find('.space').length;
	
	$(this).find('.space').each( function() {
		ratio = (windowWidth / number) / $(this).width();
		$(this).width(windowWidth / number);
		$(this).height($(this).height() * ratio);
		
		$(this).children().each ( function() {
			$(this).width(windowWidth / number);
			$(this).height($(this).height() * ratio);
		});
		
		$(this).find('.space-bg').css('bottom',$(this).height());
	});
	
	$('#mainGameWindow').css('background-size',$('.space').width()+'px');
	
});

$(document).ready(function() {
  $('#mainGameWindow').fadeIn({duration: 800, queue: false}).css('display', 'none').slideDown(500).css('display','block');
  //$('#mainGameWindow').slideDown(600).css('display','inline-block');
  $('#attack-window').fadeIn({duration: 800, queue: false}).css('display', 'none').slideDown(500).css('display','block');
  //$('#attack-window').slideDown(600).css('display','inline-block');
  
  var deselect = function ( charClass ){
	  $('.game-sprite.selected').removeClass('selected');
	  $('.game-panel.selected').find('.arrow-bar').addClass('hidden');
	  $('.game-panel.selected').find('.tag-bar').addClass('hidden');
	  $('.game-panel.selected').find('.monster-attacks').addClass('hidden');
	  $('.game-panel.selected').find('.expand-inventory').toggleClass('hidden');
	  $('.game-panel.selected').removeClass('selected');
  }
  
  var select = function ( charClass ) {
	  if (!$('.game-panel.'+charClass).hasClass('selected')){
		//deselect
		deselect( charClass );
	  }
	  
	  //select
	  if ($('.game-panel.'+charClass).hasClass('inactive-player')){
		$('.game-panel.'+charClass).find('.tag-bar').toggleClass('hidden');  
	  } else {
		$('.game-panel.'+charClass).find('.arrow-bar').toggleClass('hidden');
	  }
	  $('.game-panel.'+charClass).find('.monster-attacks').toggleClass('hidden');
	  $('.game-panel.'+charClass).find('.expand-inventory').toggleClass('hidden');
	  $('.game-panel.'+charClass).toggleClass('selected');
	  $('.game-sprite.'+charClass).toggleClass('selected');
  }

  var tagPlayer = function( tagging ){
	var tagged = $('.active-player');
	var taggingChar = getCharClass( tagging );
	var taggedChar = getCharClass( tagged );
	var taggingHero = searchClass( tagging.find('.panel-sprite'), 'player-');
	var taggedHero = searchClass( tagged.find('.panel-sprite'), 'player-');
	deselect( taggingChar );
	tagged.toggleClass('inactive-player').toggleClass('active-player');
	tagging.toggleClass('inactive-player').toggleClass('active-player');
	$('.game-sprite.'+taggedChar).toggleClass(taggingChar).toggleClass(taggedChar).toggleClass(taggingHero).toggleClass(taggedHero);
	select(taggingChar);
  }
  
  var checkForArmor = function( charID ){
	  var inventory = getCookie('items');
	  inventory = inventory.split(':');
  }
  
  $('.tag-button').click(function() {
	tagPlayer( $(this).parent().parent());  
  });
  
  $('#monster-window').delegate('.panel-sprite','click', function() {
    select(getCharClass($(this).parent()));
  });  
  
  $('#player-window').delegate('.panel-sprite','click', function() {
    select(getCharClass($(this).parent()));
  });
  
  $('#mainGameWindow').delegate('.game-sprite','click', function() {
    select(getCharClass($(this)));
  });

  var changeHealth = function( prog, amt ) {
	var bar = prog.find('.progress-bar');
    var health = bar.find('span');
    var healthString = health.html().split('/');
    var maxHealth = parseInt(healthString[1]);
    var currentHealth = parseInt(healthString[0]);
    if ((amt > 0 && currentHealth < maxHealth) || (amt < 0 && currentHealth > 0)){
      currentHealth = currentHealth + amt
      health.html(currentHealth + ' / ' + maxHealth);
    }
    /* Armor bar, not using after all
    if (currentHealth > 20){
		prog.css('background-color','#008000');
		bar.css('background-color','#c0c0c0');
		var healthPercent = ((currentHealth - 20) / 30) * 100;
        bar.width(healthPercent+'%');
	} else {
		prog.css('background-color','#800000');
		bar.css('background-color','#008000');
		var maxim = Math.min(20,maxHealth);
		var healthPercent = (currentHealth / maxim) * 100;
        bar.width(healthPercent+'%');
	}
    */
    var healthPercent = (currentHealth / maxHealth) * 100;
    bar.width(healthPercent+'%');
        
    changeHealthCookie();
  };
  

  var changeHealthCookie = function (){
	var healthVals = '';
	$('#player-window').find('.game-panel').each( function () {
		var health = $(this).find('.healthbar').find('.progress').find('.player-healthbar').children(':first').text().split('/')[0];
		health = health.trim();
		healthVals += health+':';
	});
	healthVals = healthVals.substr(0, healthVals.length - 1);
	document.cookie = "health="+healthVals;
  }  

  var changeMaxHealth = function( prog, max){
	var bar = prog.find('.progress-bar');
    var health = bar.find('span');
    var healthString = health.html().split('/');
    var maxHealth = parseInt(healthString[1]);
    var currentHealth = parseInt(healthString[0]);
    health.html(currentHealth + ' / ' + max);
    
    if (maxHealth >= max){
		return false;
	}
	
    var healthPercent = (currentHealth / max) * 100;
    bar.width(healthPercent+'%');
        
    changeHealthCookie();
    return true;
  };
  
  $('body').delegate('.plus-button', 'click', function() {
    changeHealth($(this).parent().find('.progress'), 1);
  });
  
  $('body').delegate('.minus-button', 'click', function() {
    changeHealth($(this).parent().find('.progress'), -1);
  });
  
  var moveSpace = function( self, inc ) {
	  var panel = $(self).parent().parent();
	  var charClass = getCharClass(panel);
	  var locSpace = searchClass($('.space').find('.'+charClass).parent(),'space-')[0];
	  var loc = locSpace.split('-')[1];
	  var newloc = (+loc) + (+inc);
	  var size = $('.space-row > span').length;
	  
	  if (newloc >= 1 && newloc <= size){
		$('.space').find('.'+charClass).detach().appendTo($('.space-row').find('.space-'+newloc))
	  }
  }
  
  $('body').delegate('.left-arrow', 'click', function() {
	  moveSpace(this, -1);
  });
  
  $('body').delegate('.right-arrow', 'click', function() {
	  moveSpace(this, 1);
  });
  
  $('body').delegate('.hide-monster','click', function() {
	 var name = $(this).parent().find('.name-plate').text();
	 if (name == 'Confirm?'){
		 $(this).parent().fadeOut("fast", function(){
			 var charClass = getCharClass($(this));
			 
			 var panel = $('.attack-panel').find('.attack-monster.'+charClass)
			 panel.parent().fadeOut("fast", function(){
				 $(this).remove();
			 });
			 
			 $('.'+charClass).fadeOut("fast", function(){
				 $(this).remove();
			 });
			 $(this).remove();
			 
		});
	 }
	 $(this).parent().find('.name-plate').text('Confirm?');
	 $(this).toggleClass('glyphicon-remove');
	 $(this).toggleClass('glyphicon-ok');
	 $(this).parent().find('.panel-sprite').hide();
	 $(this).parent().css('min-height', '0px');
	 var self = this;
	 setTimeout(function() {
		 $(self).parent().find('.name-plate').text(name);
		 $(self).toggleClass('glyphicon-remove');
		 $(self).toggleClass('glyphicon-ok');
		 $(self).parent().find('.panel-sprite').show();
		 $(self).parent().css('min-height', '150px');
		 },3000);
  });
  
  $('#attack-window').delegate('.hide-attack','click', function() {
	 $(this).parent().hide(400,function() {
		$(this).remove(); 
	 });
  });
  
  $('#attack-window').delegate('.attack-monster','click', function() {
	 select(getCharClass($(this))); 
  });
  
  $('body').delegate('.collapse-toggle','click', function() {
	 $(this).parent().find('.collapse-attacks').toggleClass('collapse');
	 $(this).find('.glyphicon').toggleClass('glyphicon-plus').toggleClass('glyphicon-minus'); 
  });
  
  $('body').delegate('.expand-attribute', 'click', function() {
	  $(this).parent().find('.attribute-description').toggleClass('collapse');
	  $(this).toggleClass('glyphicon-plus');
	  $(this).toggleClass('glyphicon-minus');
  });
  
  $('.roll-attacks').click(function() {
	  var attackPanel = $(this).parent();
	  var otherPanels = attackPanel.find('.attack-panel');
	  if (otherPanels.length > 0){
		  var conf = confirm('You have unresolved monster attacks this beat. Roll a new beat anyway?');
		  if (conf){
			  otherPanels.each( function() {
				  $(this).hide("fast", function() {
					$(this).remove();
				  });
			  });  
		  } else {
			  return;
		  }
	  }
	  
	  var beats = attackPanel.find('.beat-count').text(+attackPanel.find('.beat-count').text()+1);
	  
	  
	  var attacks = [];
	  
	  //for each set of monster attacks
	  $('.collapse-attacks').each( function() {
		//Need to ignore the ones in the add-monster modal
		if (!$(this).parent().parent().parent().hasClass('add-monster-panel')){
			var chances = [];
			//build the roll array
			$(this).find('.attack-panel').each( function( index ) {
				for (var i=0; i<+$(this).find('.attack-desc').find('.attack-chance').text();i++){
					chances.push(index);
				}
			});
			
			var roll = Math.floor(Math.random()*chances.length);
			var attack = $(this).children().eq(chances[roll]).clone();
			attack.prepend('<div class="hide-attack glyphicon glyphicon-remove"></div>');
			attack.prepend('<div class="attack-monster btn btn-default '+getCharClass($(this).parent().parent())+'"><span>'+$(this).parent().parent().find('.name-plate').text()+'</span></div>');
			attack.find('.attack-chance').remove();
			attack.width(180);
				
			attacks.push(attack);
		}
	  });
	  //sort on priority function
	  var comparePriority = function( panel1, panel2 ){
		  return (+panel2.find('.attack-priority').text() - +panel1.find('.attack-priority').text());
	  }
	  
	  attacks.sort( comparePriority );
	  
	  //resize the name plate
	  for (var i=0;i<attacks.length;i++){
		$('#attack-window').append(attacks[i]);
		attacks[i].find('.attack-monster').textfill({ maxFontPixels: 20});
		attacks[i].hide();
	  }
	  
	  //make them visible
	  for (var i=0;i<attacks.length;i++){
		attacks[i].slideDown(300);
	  }
	  
	  //update trinket cooldowns
	  $('.active-player').find('.item-cooldown').each(function() {
		 var cd = $(this).text();
		 $(this).text(+cd-1);
		 if (cd == 1){
			 $(this).hide();
		 } 
	  });
  });
  
  $('body').delegate('.roll-attack','click',function() {
	  //for each set of monster attacks
	  var chances = [];
	  //build the roll array
	  $(this).parent().find('.attack-panel').each( function( index ) {
		for (var i=0; i<+$(this).find('.attack-desc').find('.attack-chance').text();i++){
			chances.push(index);
		}
	  });
		
	  var roll = Math.floor(Math.random()*chances.length);
	  var attack = $(this).parent().find('.collapse-attacks').children().eq(chances[roll]).clone();
	  attack.prepend('<div class="hide-attack glyphicon glyphicon-remove"></div>');
	  attack.prepend('<div class="attack-monster btn btn-default '+getCharClass($(this).parent())+'"><span>'+$(this).parent().find('.name-plate').text()+'</span></div>');
	  attack.find('.attack-chance').remove();
	  attack.width(180);
			
	  
	  //resize the name plate
	  
	  var priority = attack.find('.attack-priority').text();
	  var insertPoint = null;
	  $('#attack-window').find('.attack-panel').each( function() {
		var newprio = $(this).find('.attack-priority').text();
		if (newprio < priority){
			insertPoint = $(this);
			console.log(insertPoint);
			return false;
		}
	  });
	  if (insertPoint != null){
		attack.insertBefore(insertPoint);  
	  } else {
		$('#attack-window').append(attack);  
	  }
	  attack.find('.attack-monster').textfill({ maxFontPixels: 20});
	  attack.hide();
	  attack.slideDown(300);
  });
  
  $('.inventory').delegate('.inventory-consumable','click',function() {
	 var itemCount = $(this).find('.item-count').text();
	 if (itemCount >= 1){
		$(this).find('.item-count').text(+itemCount - 1); 
		if (itemCount == 1){
			$(this).fadeOut(400, function() {
				$(this).remove();
			});
		}
		
		var dbid = searchClass($(this),'itemdb');
		dbid = dbid.split('-')[1];
		var charID = getCharClass($(this).parent().parent()).split('-')[1];
		changeInventory(charID,dbid, -1);
	 }
  });
  
  $('.inventory').delegate('.inventory-key','click',function() {
	 var itemCount = $(this).find('.item-count').text();
	 if (itemCount >= 1){
		$(this).find('.item-count').text(+itemCount - 1); 
		if (itemCount == 1){
			$(this).fadeOut(400, function() {
				$(this).remove();
			});
		}
		
		var dbid = searchClass($(this),'itemdb');
		dbid = dbid.split('-')[1];
		var charID = getCharClass($(this).parent().parent()).split('-')[1];
		changeInventory(charID,dbid, -1);
	 }
  });
  
  $('.armory-player').click(function() {
	 if (!$(this).hasClass('selected')){
		 $('.armory-player.selected').removeClass('selected');
	 }
	 $(this).addClass('selected');
	 
	 var charClass = getCharClass($(this));
	 $('.current-weapons.active').toggleClass('active').toggleClass('hidden');
	 $('.current-weapons.'+charClass).toggleClass('active').toggleClass('hidden');
  });
  
  var changeInventory = function( charID, id, val) {
	var inventory = getCookie('items');
	var found = false;
	var finalval = val;
	var newItems = '';
	var charClass = 'char-'+charID;
	
	if (inventory != null){
		var items = inventory.split(':');
		 
		for (var i=0;i<items.length;i++){
			var itemInfo = items[i].split(',');
			if (itemInfo[0] == charID && itemInfo[1] == id){
				found = true;
				itemInfo[2] = +itemInfo[2] + val;
				finalval = itemInfo[2];
				itemInfo[2] = itemInfo[2].toString();
				if (itemInfo[2] <= 0){
					itemInfo = '';
				} else {
					newItems += itemInfo.join();
					newItems += ':';
				}
			} else {
				newItems += itemInfo.join();
				newItems += ':';
			}
		 }
	 }
	 if (!found){
		 newItems += charID+','+id+','+val+':';
	 }
	 var typeclass = searchClass($('.armory-item.itemdb-'+id.toString()).parent(),'armory-')[1];
	 var itemclass = searchClass($('.armory-item.itemdb-'+id.toString()),'item-');
	 var itemname = $('.itemdb-'+id.toString()).parent().find('.shop-title').text();
	 var description = $('.itemdb-'+id.toString()).parent().find('.armory-desc').html();
	 newItems = newItems.substr(0, newItems.length-1);
	 document.cookie = "items="+newItems;
	 createOrUpdateInventory(charClass, typeclass.split('-')[1], itemclass, description, id, itemname, finalval);
  }
  
  var armorDict = {}
  armorDict['Leather Armor'] = 25;
  armorDict['Bronze Armor'] = 30;
  armorDict['Iron Armor'] = 35;
  armorDict['Steel Armor'] = 40;
  armorDict['Diamond Armor'] = 45;
  armorDict['Vital Silver Armor'] = 50;
  
  var createOrUpdateInventory = function(charClass, typeclass, itemclass, description, id, name, count){
	var inventory = $('.'+charClass).find('.inventory');
	var found = false;
	var newArmor = true;
	
	if (typeclass == 'armor'){
		var prog = $('.game-panel.'+charClass).find('.healthbar').find('.progress');
		newArmor = changeMaxHealth(prog, armorDict[name]);
	}
	
	inventory.children().each(function () {
		if (newArmor){
			if ($(this).hasClass('inventory-armor')){
				$(this).remove();
			}
		}
		if ($(this).hasClass('itemdb-'+id.toString())){
			$(this).find('.item-count').text(count.toString());
			found = true;
		}
	});
	
	if (!newArmor){
		return;
	}
	if (!found){
		var str = '';
		str += '<div class="inventory-line inventory-'+typeclass+' itemdb-'+id.toString()+'">';
		str += '<div class="item-header">';
		if (typeclass == 'consumable' || typeclass == 'key'){
			str += '<span class="item-count">'+count.toString()+'</span>';
		} else if (typeclass == 'trinket'){
			str += '<span class="item-count item-cooldown" style="display:none">0</span>';
		}
		str += '<span class="item-small '+itemclass+'"></span>';
		str += '<span class="item-name">'+name+'</span>';
		str += '</div>';
		if (inventory.parent().find('.expand-inventory').find('.glyphicon').hasClass('glyphicon-triangle-bottom')){
			str += '<div class="item-desc opacity80">'+description+'</div>';
		} else {
			str += '<div class="item-desc opacity80" style="display:none">'+description+'</div>';	
		}
		str += '</div>';
		inventory.append(str);
	}
	
	
  }
  
  $('.shop-tab').delegate('.glyphicon','click',function() {
	 if ($(this).hasClass('glyphicon-usd')){
		var gold = +$('.gold-number').text();
		var cost = $(this).parent().find('.cost-total').text();
		if (gold >= cost){
			$('.gold-number').text(+gold-cost);
			document.cookie = "gold="+(+gold-cost);
		} else {
			return;
		}
	 }
	 
	 
	 var item = $(this).parent().parent().find('.armory-item');
	 var dbid = searchClass(item,'itemdb');
	 dbid = dbid.split('-')[1];
	 var charID = getCharClass($('.armory-player.selected')).split('-')[1];
	 
	 changeInventory(charID,dbid, 1);
  });
  
  $('.inventory').delegate('.inventory-trinket','click',function() {
	 var itemCount = $(this).find('.item-count').text();
	 if (itemCount <= 0){
		 $(this).find('.item-count').text('3');
		 $(this).find('.item-count').show();
	 } else {
		 $(this).find('.item-count').text($(this).find('.item-count').text()-1);
		 if ($(this).find('.item-count').text() === '0'){
			 $(this).find('.item-count').hide();
		 }
	 }
  });
  
  $('.game-panel').delegate('.expand-inventory','click',function() {
	  var glyph = $(this).find('.glyphicon');
	  
	  if (glyph.hasClass('glyphicon-triangle-right')){
		  $(this).parent().find('.item-desc').each( function() {
			 $(this).show(300); 
		  });
	  } else {
		  $(this).parent().find('.item-desc').each( function() {
			 $(this).hide(300); 
		  });
	  }
	  glyph.toggleClass('glyphicon-triangle-right');
	  glyph.toggleClass('glyphicon-triangle-bottom');
  });
  
  var QueryString = function () {
  // This function is anonymous, is executed immediately and 
  // the return value is assigned to QueryString!
  var query_string = {};
  var query = window.location.search.substring(1);
  var vars = query.split("&");
  for (var i=0;i<vars.length;i++) {
    var pair = vars[i].split("=");
        // If first entry with this name
    if (typeof query_string[pair[0]] === "undefined") {
      query_string[pair[0]] = pair[1];
        // If second entry with this name
    } else if (typeof query_string[pair[0]] === "string") {
      var arr = [ query_string[pair[0]], pair[1] ];
      query_string[pair[0]] = arr;
        // If third or later entry with this name
    } else {
      query_string[pair[0]].push(pair[1]);
    }
  } 
    return query_string;
  } ();
  
  $('.buy-type').click(function() {
	  $('.buy-type').find('.add-button').toggleClass('selected');
	  $('.buy-type').find('.buy-button').toggleClass('selected')
  });
  
  var updateWeaponButtons = function(){
	var selectedWeapon = $('.weapons-column').find('.selected');
	var swapWeapon = $('.current-weapons.active').find('.selected');
	
	if (selectedWeapon.length === 0){
		$('.match-cost').css('visibility','hidden');
		$('.replace-cost').css('visibility','hidden');
		$('.add-cost').css('visibility','hidden');
		
		$('.match-weapon').addClass('disabled');
		$('.replace-weapon').addClass('disabled');
		$('.add-weapon').addClass('disabled');
	} else {
		if (selectedWeapon.hasClass('type-weapon') || selectedWeapon.hasClass('type-special')){
			$('.match-cost').css('visibility','hidden');
			$('.replace-cost').css('visibility','visible');
			$('.add-cost').css('visibility','visible');
		
			$('.match-weapon').addClass('disabled');
			$('.replace-weapon').removeClass('disabled');
			$('.add-weapon').removeClass('disabled');
		} else if (selectedWeapon.hasClass('type-beta') || selectedWeapon.hasClass('type-delta')){
			$('.match-cost').text(' 5').css('visibility','visible');
			$('.replace-cost').css('visibility','visible');
			$('.add-cost').css('visibility','visible');
		
			$('.match-weapon').removeClass('disabled');
			$('.replace-weapon').removeClass('disabled');
			$('.add-weapon').removeClass('disabled');
		} else if (selectedWeapon.hasClass('type-ex') || selectedWeapon.hasClass('type-almighty')){
			var text = (selectedWeapon.hasClass('type-ex')) ? '30' : '50';
			$('.match-cost').text(text).css('visibility','visible');
			$('.replace-cost').css('visibility','hidden');
			$('.add-cost').css('visibility','hidden');
		
			$('.match-weapon').removeClass('disabled');
			$('.replace-weapon').addClass('disabled');
			$('.add-weapon').addClass('disabled');
		}
	}
	//If you could replace a weapon, but you have no target selected
	if (!$('.replace-weapon').hasClass('disabled') && swapWeapon.length === 0){
		$('.replace-weapon').addClass('disabled');
	}
  }
  
  var updateWeaponCookie = function(){
	var str = '';
	$('.current-weapons').each( function() {
		
		$(this).find('.weapon-line').each( function() {
			var dbid = searchClass($(this),'dbweapon-').split('-')[1];
			str += dbid+',';
		});
		str = str.substr(0,str.length-1);
		str += ':';
	});
	str = str.substr(0,str.length-1);
	document.cookie ="weapons="+str;
  }
  
  $('.weapon-line').click(function() {
	  //Select the line
	  if (!$(this).hasClass('selected')){
		  var parent = $(this).parent();
		  if (searchClass(parent, 'weapon-group')){
			  parent = parent.parent();
		  }
		  parent.find('.selected').toggleClass('selected');
	  }
	  $(this).toggleClass('selected');
	  
	  //Activate/deactivate the buttons
	  updateWeaponButtons();
  });
  
  $('.match-weapon').click(function() {
	if ($(this).hasClass('disabled')){
		return;
	}
	
	var color = searchClass($('.weapons-column').find('.selected') ,'color-');
	var oldWeapon = $('.current-weapons.active .type-standard.'+color);
	if (oldWeapon.length !== 0){
		var buy = $('.buy-button').hasClass('selected');
		if (buy){
			var gold = +$('.gold-number').text();
			var cost = 5;
			
			var type = searchClass($('.weapons-column').find('.selected'),'type-');
			if (type == 'type-ex'){
				cost = 30;
			} else if (type == 'type-almighty'){
				cost = 50;
			}
			
			if (gold >= cost){
				$('.gold-number').text(+gold-cost);
				document.cookie = "gold="+(+gold-cost);
			} else {
				return;
			}
		}
		
		
		
		oldWeapon.remove();
		$('.weapons-column').find('.selected').clone().toggleClass('selected').appendTo($('.current-weapons.active'));
	}
	updateWeaponCookie();
  });
  
  $('.replace-weapon').click(function() {
	if ($(this).hasClass('disabled')){
		return;
	}
	
	var oldWeapon = $('.current-weapons.active').find('.selected');
	if (oldWeapon.length !== 0){
		var buy = $('.buy-button').hasClass('selected');
		if (buy){
			var gold = +$('.gold-number').text();
			var cost = 10;
			if (gold >= cost){
				$('.gold-number').text(+gold-cost);
				document.cookie = "gold="+(+gold-cost);
			} else {
				return;
			}
		}
	
		
		oldWeapon.remove();
		$('.weapons-column').find('.selected').clone().toggleClass('selected').appendTo($('.current-weapons.active'));
	}
	updateWeaponCookie(); 
  });
  
  $('.add-weapon').click(function() {
	 if ($(this).hasClass('disabled')){
		return;
	}
	
	var buy = $('.buy-button').hasClass('selected');
	if (buy){
		var gold = +$('.gold-number').text();
		var cost = 25;
		
		if (gold >= cost){
			$('.gold-number').text(+gold-cost);
			document.cookie = "gold="+(+gold-cost);
		} else {
			return;
		}
	}
	
	$('.weapons-column').find('.selected').clone().toggleClass('selected').appendTo($('.current-weapons.active')); 
	updateWeaponCookie();
  });
  
  $('.floor-forward').click(function() {
	 var val = $(this).parent().find('.floor-selector').val();
	 
	 var oldCookie = getCookie('heros')+'|';
	 oldCookie += getCookie('health')+'|';
	 oldCookie += getCookie('items')+'|';
	 oldCookie += getCookie('gold')+'|';
	 oldCookie += getCookie('weapons');
	 
	 document.cookie = "startSave="+oldCookie;
	 
	 window.location.replace('./play.php?dungeon='+QueryString['dungeon']+'&'+'floor='+val);
  });
  
  $('.floor-backward').click(function() {
	 var val = $(this).parent().find('.floor-selector').val();
	 
	 var oldCookie = getCookie('heros')+'|';
	 oldCookie += getCookie('health')+'|';
	 oldCookie += getCookie('items')+'|';
	 oldCookie += getCookie('gold')+'|';
	 oldCookie += getCookie('weapons');
	 
	 document.cookie = "startSave="+oldCookie;
	 
	 window.location.replace('./play.php?dungeon='+QueryString['dungeon']+'&'+'floor='+val); 
  });

  $('.restart-floor .glyphicon').click( function() {
	 var result = confirm('Restart the current floor? (All progress will be reverted to what you started the floor with)');
	 if (result){
		restartFloor();
		location.reload();
	 }
	 
  });
  
  $('.gold-change').delegate('.text-button','click',function() {
	 var val = $(this).text();
	 var gold = $('.gold-number').text();
	 var goldTotal = Math.max(0,+gold + +val);
	 $('.gold-number').text(goldTotal);
	 document.cookie = "gold="+(goldTotal);
  });
  
  $('.group-line').click( function() {
	 var groupClass = searchClass($(this), 'group-')[1];
	 $('.weapon-group.'+groupClass).toggleClass('collapse');
	 $(this).find('.glyphicon').toggleClass('glyphicon-triangle-right').toggleClass('glyphicon-triangle-bottom');
	 $(this).toggleClass('active');
  });
  
  $('.make-marker').click( function() {
	 var count = $('.marker').length;
	 
	 $('.space.space-1').append('<div class="marker mark-'+count+'" style="width: '+$('.space').width()+'px"><span class="marker-label">'+$(this).parent().find('.marker-label').find('input').val()+'</span></div>');
	 $('#markerModal').modal('toggle');
	 var marker = $('.space-1').find('.marker');
	 marker.draggable({
		opacity: 0.7,
		helper: "clone",
		revert: "invalid"
	 });
  });
  
  $('.picker-footer').click( function() {
	 var dbClass = searchClass($(this).parent(),'dbmonster-');
	 var panel = $('.add-monster-panel.'+dbClass).find('.game-panel').clone();
	 var currentChar = $('.current-char-number').text();
	 panel.addClass('char-'+currentChar);
	 $('.current-char-number').text(+currentChar+1);
	 
	 var pClass = searchClass(panel.find('.panel-sprite'),'monster-');
	 var yOff = Math.floor(Math.random()*20)-10;
	 $('.space-1').append('<div class="game-sprite char-'+currentChar+' '+pClass+'" style="background-position: 0px '+yOff+'px; z-index: '+(+yOff + 10)+'">');
	 
	 $('.space-1').find('.char-'+currentChar).draggable({
		opacity: 0.7,
		helper: "clone",
		revert: "invalid"
	 });

	 $('#monster-window').append(panel,$('.add-monsters-window'));
  });

  $('.roll-die').click(function () {
	 var roll = Math.floor(Math.random() * 6) + 1;
	 $('.roll-result').text(roll);
  });
  
});

var dist = +$('.space').width();

$('.game-sprite').draggable({
	opacity: 0.7,
	helper: "clone",
	revert: "invalid"
});

$('.space-dropzone').droppable({
	tolerance: "intersect",
	drop: function( event, ui ) {
		ui.draggable.detach().appendTo($(this).parent());
	}
});

$('.marker-window').droppable({
	accept: ".marker",
	tolerance: "touch",
	activeClass: "active",
	drop: function( event, ui ) {
		ui.draggable.remove();
	}
});
}
