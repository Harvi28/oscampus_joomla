jQuery(window).load(function(){
	var $container = jQuery('.oscampus_portfolio_items');
	$container.isotope({
		itemSelector: '.osc-portfolio-element-item',
		layoutMode: 'fitRows'
	});
	jQuery('#osc-portfolio-filters button').click(function() {
		jQuery('#osc-portfolio-filters button').removeClass('osc-btn-active');
		jQuery(this).addClass('osc-btn-active');
		var selector = jQuery(this).attr('data-filter');
		$container.isotope({
			filter: selector
		});
		return false;
	});
});