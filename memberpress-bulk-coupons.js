(function($) {
  $(document).ready(function() {
// TODO make this localized
	$('#mepr-coupons-form')
		.prepend('<div class="mepr-options-pane">Number of Coupons: <input type="number" min="1" max="50" name="_mepr_bulk_number_of_coupons" value="1"></div>');

});

})(jQuery);
