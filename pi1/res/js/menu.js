jQuery(document).ready(function(){


	initMenus();

	function initMenus() {
		jQuery('ul.tx-nkwsubmenu-pi1-l1 ul.js').hide(); // hides all ULs with class "js"
		jQuery.each(jQuery('ul.tx-nkwsubmenu-pi1-l1'), function(){
			jQuery('#' + this.id + ' ul.go').show();
		});
		jQuery('ul.tx-nkwsubmenu-pi1-l1 li a.tx-nkwsubmenu-pi1-trigger').click(
			function() {
				var checkElement = jQuery(this).next();
				var parent = this.parentNode.parentNode.id;
				if((checkElement.is('ul')) && (!checkElement.is(':visible'))) {
					jQuery('#' + parent + ' ul.js:visible').slideUp('normal');
					checkElement.slideDown('normal');
					return false;
				}
			}
			);
	}
});