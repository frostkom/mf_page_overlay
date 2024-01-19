jQuery(function($)
{
	$(document).on('click', "#overlay_page .button_close, #overlay_page a[href='#']", function(e)
	{
		$(this).parents("#overlay_page").fadeOut().parents("body").removeClass('has_page_overlay');

		return false;
	});
});