var dungeon = '';
var floor = 0;
var players= '';
var inventory = '';
var gold = '';

var playDungeon = function (dId, fId, players, gld){
	for (var i=0; i<players;i++){
		if (i == 0){
			$('.current-selections').append('<div class="selection selected slot-'+i+'"></div>');
		} else {
			$('.current-selections').append('<div class="selection slot-'+i+'"></div>');
		}
	}
	if (players > 1){
		$('#player-picker').find('h1').text($('#player-picker').find('h1').text()+'S');
	}
	$('#dungeon-picker').hide();
	$('#player-picker').show();
	dungeon = dId;
	floor = fId;
	gold = gld;
}

var getNameFilter = function(){
	var name = $('.filter-name input').val();
	return name.toLowerCase();
}

var getCheckedSets = function(){
	var sets = [];
	$('.filter-set').find('input:checked').each( function() {
		sets.push($(this).val());
	});
	return sets;
}

var getCheckedFlights = function(){
	var flights = [];
	$('.filter-flight').find('input:checked').each( function() {
		flights.push($(this).val().toLowerCase());
	});
	console.log(flights);
	return flights;
}

var filterPlayers = function( name, sets, flights ) {
	$('.fighter-select').children().each(function() {
		var fromSet = $(this).find('.hero-set').text().toLowerCase();
		var visible = false;
		for (var i=0;i<sets.length;i++){
			if (fromSet.startsWith(sets[i])){
				visible = true;
			}
		}
		
		var flight = $(this).find('.hero-flight').text().toLowerCase();
		console.log(flight);
		if (flights.indexOf(flight) != -1) {
			visible = visible && true;
		} else {
			visible = false;
		}
		
		if (name != ''){
			var hero_name = $(this).find('.hero-name').text().toLowerCase();
			
			if (hero_name.startsWith(name)){
				visible = visible && true;
			} else {
				visible = false;
			}
		}
		
		//check name and flights for more visible
		if (visible){
			$(this).fadeIn(200);
		} else {
			$(this).fadeOut(200);
		}
	});
	
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


$(document).ready( function(){
	$('#player-picker .btn').click( function() {
		var valid = true;
		var plString = '';
		$('.current-selections').children().each( function() {
			var playerClass = searchClass($(this), 'player-');
			
			if (!playerClass){
				valid = false;
			}
			var name = $('.select-portrait.'+playerClass).parent().find('.hero-name');
			plString += name.text();
			plString += ':';
		});
		
		plString = plString.substring(0,plString.length-1);
		if (valid){
			document.cookie = "heros="+plString;
			document.cookie = "gold="+gold;
			window.location.href = './play.php?dungeon='+dungeon+'&floor='+floor;
		} else {
			alert("You have not selected enough heroes.")
		}
		
	});
	
	$('#hero-filters').delegate('input','click',function() {
		filterPlayers(getNameFilter(), getCheckedSets(), getCheckedFlights() );
	});
	
	$('.name-input').on('change keyup paste', function(){
		filterPlayers(getNameFilter(), getCheckedSets(), getCheckedFlights() );
	});
	
	$('.select-panel').click( function() {
		var portrait = $(this).find('.select-portrait');
		var pClass = searchClass(portrait,'player-');
		
		var selector = $('.current-selections').find('.selected');
		if (selector.length !== 0){
			var oldPClass = searchClass(selector, 'player-');
		
			if (oldPClass){
				$('.current-selections').find('.selected').removeClass(oldPClass);
			}
			
			$('.current-selections').find('.selected').addClass(pClass);
			
			var slot = searchClass($('.current-selections').find('.selected'),'slot-');
			slot = slot.split('-')[1];
			console.log(slot);
			
			$('.name-input').val('');
			$('.name-input').trigger('change');
			
			var nextSlot = $('.slot-'+(+slot+1));
			if (nextSlot.length !== 0){
				$('.current-selections').find('.selected').toggleClass('selected');
				nextSlot.toggleClass('selected');
			} else {
				$('.current-selections').find('.selected').toggleClass('selected');
				$('.slot-0').toggleClass('selected');
			}
		}		
	});
	
	$('.current-selections').delegate('.selection','click', function() {
		if (!$(this).hasClass('selected')){
			$('.selection.selected').removeClass('selected');
		}
		$(this).toggleClass('selected');
	});
	
});
