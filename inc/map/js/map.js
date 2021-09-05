// D3 Map
///

function setPathClass(num) {
	let n = new Number(num)
	if ( n == 0 )
		return 'map-municipio map-municipio-empty'
	else
		return 'map-municipio map-municipio-populated'

}

const municipios = d3.json("https://hablacanarias.es/wp-json/map/users");
const w = 600, h = 270;

let svg = d3.select("#map").append('svg')
	.attr("preserveAspectRatio", "xMinYMin meet")
	.attr("viewBox", "0 0 " + w + " " + h)
	.classed("svg-content", true);

//const projection = d3.geoMercator().translate([w/2, h/2]).scale(9000).center([-15.7,28.2]);
const projection = d3.geoEqualEarth();

Promise.all([municipios]).then(function(values){

	let m = topojson.feature(values[0],values[0].objects.canarias);

	projection.fitSize([w-10, h-10], m);
	let geoGenerator = d3.geoPath().projection(projection);

	// municipios
	let paths = svg.append('g').attr('class','map-municipios').selectAll('path')
		.data(m.features)
		.enter()
		.append('path')
		.attr('d', geoGenerator);

	paths
		.attr('class',function(d){ return setPathClass(d.properties.user_count) } )
		.attr('data-filter',function(d){ return '.'+d.properties.slug } )
		// on mouseover event
		.on('mouseover', function(d) {
			tooltip.html('<div><strong>'+d.properties.etiqueta+'</strong><br>'+d.properties.user_count+'</div>').transition().duration(200).style('display', 'block')
		})
		// on mouseout event
		.on('mouseout', function() {
			tooltip.style('display', 'none')
		})
		// on mouse move event
		.on('mousemove', function() {
			tooltip.style('left', (d3.event.pageX+10) + 'px').style('top', (d3.event.pageY+10) + 'px')
		});
		// on mouse click event
		//.on('click', function() {
		//})


	// Isotope filtrable mosaic
	///
	(function($) {

		// init Isotope
		var $grid = $('.mosac').isotope({
			// options
			itemSelector: '.mosac-item',
			layout: 'masonry',
		});

		// filters
		$('.filter-group').on( 'click','button', function() {
			var filterValue = $(this).attr('data-filter');
			$grid.isotope({ filter: filterValue });
			$('.filter-group-btn').prop('disabled',false).removeClass('active');
			$(this).prop("disabled", true).addClass('active');
			return false;
		});

		$('.map-municipio-populated').on( 'click', function() {
			var filterValue = $(this).attr('data-filter');
			$grid.isotope({ filter: filterValue });
			$('.filter-group-btn').prop('disabled',false).removeClass('active');
			return false;
		});
	})(jQuery);

})

let tooltip = d3.select('body').append('div').attr('id', 'map-tooltip').attr('class','tooltip').attr('style', 'position: absolute; display: none;');

(function($) {

	$(document).ready(function(){
		$('.feedback-close').on('click',function(){
			$('.feedback').fadeOut('slow');
		})
	});

})(jQuery);
