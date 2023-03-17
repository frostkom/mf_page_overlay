jQuery(function($)
{
	$(document).on('click', "#overlay_page .button_close", function(e)
	{
		if(e.target == e.currentTarget)
		{
			$(this).parents("#overlay_page").fadeOut().parents("body").removeClass('has_page_overlay');

			return false;
		}
	});
});