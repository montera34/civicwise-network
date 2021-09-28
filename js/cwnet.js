// D3 Map
///

function setPathClass(num) {
	let n = new Number(num)
	if ( n == 0 )
		return 'map-location map-location-empty'
	else
		return 'map-location map-location-populated'

}

const countries = d3.json("https://civicwise.org/wp-json/map/wisers");
const w = 600, h = 270;

let svg = d3.select("#map").append('svg')
	.attr("preserveAspectRatio", "xMinYMin meet")
	.attr("viewBox", "0 0 " + w + " " + h)
	.classed("svg-content", true);

//const projection = d3.geoMercator().translate([w/2, h/2]).scale(9000).center([-15.7,28.2]);
const projection = d3.geoEqualEarth();

Promise.all([countries]).then(function(values){

	let m = topojson.feature(values[0],values[0].objects.world);

	projection.fitSize([w-10, h-10], m);
	let geoGenerator = d3.geoPath().projection(projection);

	// countries
	let paths = svg.append('g').attr('class','map-locations filter-group').attr('data-filter-group','location').selectAll('path')
		.data(m.features)
		.enter()
		.append('path')
		.attr('d', geoGenerator);

	paths
		.attr('class',function(d){ return setPathClass(d.properties.user_count) } )
		.attr('data-filter',function(d){ return '.location-'+d.properties.slug } )
		// on mouseover event
		.on('mouseover', function(e,d) {
			if ( d.properties.user_count == 1 )
				l = 'wiser'
			else
				l = 'wisers'
			tooltip.html('<div>'+d.properties.name+'<br>'+d.properties.user_count+' '+l+'</div>').transition().duration(200).style('display', 'block')
		})
		// on mouseout event
		.on('mouseout', function() {
			tooltip.style('display', 'none')
		})
		// on mouse move event
		.on('mousemove', function(e) {
			tooltip.style('left', (e.pageX+10) + 'px').style('top', (e.pageY+10) + 'px')
		})
		// on mouse click event
		.on('click', function(e,d) {
			if ( d.properties.user_count == 0 )
				return;
			sidebar.html('<button class="filter-btn filter-group-btn disabled">'+d.properties.name+'</button><button class="filter-btn filter-group-btn filter-btn-reset" data-filter=""><i class="icon-cancel"></i></button>').transition().duration(200).style('display', 'block')
		});

	// Isotope filtrable mosaic
	///
	(function($) {

		// init Isotope
		var $grid = $('.mosac').isotope({
			// options
			itemSelector: '.mosac-item',
			layout: 'masonry',
		});

		// store filters for each group
		var filters = {};

		// filters as buttons
		$('.filters').on( 'click', 'button.filter-group-btn', function( event ) {
			var $button = $( event.currentTarget );
			// get group key
			var $buttonGroup = $button.parents('.filter-group');
			var filterGroup = $buttonGroup.attr('data-filter-group');
			// set filter for group
			filters[ filterGroup ] = $button.attr('data-filter');
			// combine filters
			var filterValue = concatValues( filters );
			// set filter for Isotope
			$grid.isotope({ filter: filterValue });
		});

		// change is-checked class on buttons
		$('.filter-group').each( function( i, buttonGroup ) {
			var $buttonGroup = $( buttonGroup );
			$buttonGroup.on( 'click', 'button', function( event ) {
				$buttonGroup.find('.disabled').removeClass('disabled');
				var $button = $( event.currentTarget );
				$button.addClass('disabled');
			});
		});

		// filters as map areas
		$('.map-locations').on( 'click', 'path', function(event) {
			if ( $(this).hasClass('map-location-empty') || $(this).hasClass('disabled') )
				return;

			// change buttons status
			$('.map-locations path').removeClass('disabled');
			$(this).addClass('disabled');

			var $button = $( event.currentTarget );
			// get group key
			var $buttonGroup = $button.parents('.filter-group');
			var filterGroup = $buttonGroup.attr('data-filter-group');
			// set filter for group
			filters[ filterGroup ] = $button.attr('data-filter');
			// combine filters
			var filterValue = concatValues( filters );
			// set filter for Isotope
			$grid.isotope({ filter: filterValue });
		});

		$('#map-sidebar').on( 'click', '.filter-btn-reset', function(event) {
			var $button = $( event.currentTarget );
			filters[ 'location' ] = $button.attr('data-filter');
			// combine filters
			var filterValue = concatValues( filters );
			// set filter for Isotope
			$grid.isotope({ filter: filterValue });
			$('.map-locations path').removeClass('disabled');
			$('#map-sidebar').hide();

		});

		// dropdown filters
		$('.dropdown').on('click','.dropdown-btn',function(event) {
			var $button = $( event.currentTarget );
			var target = $button.attr('data-target');
			$(target).toggle();
		});
		$('.dropdown-content').on('click','.filter-btn',function(event) {
			var $button = $( event.currentTarget );
			var target = $button.attr('data-target');
			$(target).hide();
			$(this).parents('.dropdown').children('.dropdown-btns').find('.filter-group-btn').remove();
			$(this).parents('.dropdown').children('.dropdown-btns').append('<button class="filter-btn filter-group-btn disabled">'+$button.text()+'</button><button class="filter-btn filter-group-btn filter-btn-reset" data-filter="" data-target="'+target+'"><i class="icon-cancel"></i></button>');
		});
		$('.dropdown').on( 'click', '.filter-btn-reset', function(event) {
			var $button = $( event.currentTarget );
			var target = $button.attr('data-target');
			var $buttonGroup = $(target).children('.filter-group');
			var filterGroup = $buttonGroup.attr('data-filter-group');
			filters[ filterGroup ] = $button.attr('data-filter').substring(1);
			// combine filters
			var filterValue = concatValues( filters );
			// set filter for Isotope
			$grid.isotope({ filter: filterValue });
			$buttonGroup.find('.disabled').removeClass('disabled');
			$buttonGroup.children().first().children('button').addClass('disabled');
			$(this).hide();
			$(this).siblings('.filter-group-btn').hide();

		});

	})(jQuery);

	// flatten object by concatting values
	function concatValues( obj ) {
		var value = '';
		for ( var prop in obj ) {
			value += obj[ prop ];
		}
		return value;
	}

})

let tooltip = d3.select("body").append('div').attr('id', 'map-tooltip').attr('class','tooltip button filter-btn disabled').attr('style', 'position: absolute; display: none;');
let sidebar = d3.select("#map").append('div').attr('id', 'map-sidebar').attr('class','map-aside filter-group').attr('style', 'display: none;');

// tippy tooltips
tippy('[data-tippy-content]', {
	theme: 'material',
});

(function($){

	$(document).ready(function(){
	
		// multiple select fields
		$('.multiple-select select').multipleSelect({
			filter: true,
			width: '100%'
		});
	
		// read only fields
		// input fields
		$(".gform_wrapper .read-only input").attr("readonly", "");
		// textarea fields
		$(".gform_wrapper .read-only textarea").attr("readonly", "");
		$(".gform_wrapper .read-only textarea").text(stripHTMLTags);
		$(".gform_wrapper .striptags textarea").text(stripHTMLTags);
	
	});	

	function stripHTMLTags(){
		return $(this).text().replace(/(<([^>]+)>)/ig,"");
	}

})(jQuery);

function searchInterests() {
	var input, filter, ul, li, a, i;
	input = document.getElementById("interests-search");
	filter = input.value.toUpperCase();
	div = document.getElementById("interests-container");
	a = div.getElementsByClassName("filter-group-btn");
	for (i = 0; i < a.length; i++) {
		txtValue = a[i].textContent || a[i].innerText;
		if (txtValue.toUpperCase().indexOf(filter) > -1) {
			a[i].style.display = "";
		} else {
			a[i].style.display = "none";
		}
	}
}
