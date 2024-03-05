jQuery(function($)
{
	var dom_overlay_page = $("#overlay_page"),
		overlay_page_id = dom_overlay_page.data('overlay_page_id'),
		overlay_hide_when_accepted = dom_overlay_page.data('overlay_hide_when_accepted');

	if(overlay_hide_when_accepted == 'yes')
	{
		var dom_overlay_page_accepted = $("#overlay_page_accepted");

		dom_overlay_page.each(function()
		{
			if(document.cookie.indexOf("cookie_page_overlay_" + overlay_page_id + "=") !== -1)
			{
				dom_overlay_page.fadeOut().parents("body").removeClass('has_page_overlay');

				if(dom_overlay_page_accepted.length > 0)
				{
					dom_overlay_page_accepted.fadeIn();
				}
			}

			else
			{
				dom_overlay_page.fadeIn();

				if(dom_overlay_page_accepted.length > 0)
				{
					dom_overlay_page_accepted.fadeOut();
				}
			}
		});

		if(dom_overlay_page_accepted.length > 0)
		{
			dom_overlay_page_accepted.on('click', "span", function()
			{
				document.cookie = "cookie_page_overlay_" + overlay_page_id + "=true; path=/; max-age=0";
				document.cookie = "cookie_page_overlay_" + overlay_page_id + "=true; max-age=0";

				dom_overlay_page.fadeIn().parents("body").addClass('has_page_overlay');
				dom_overlay_page_accepted.fadeOut();

				return false;
			});
		}
	}

	else
	{
		dom_overlay_page.fadeIn();
	}

	dom_overlay_page.on('click', ".button_close, a[href='#']", function()
	{
		dom_overlay_page.fadeOut().parents("body").removeClass('has_page_overlay');

		if(overlay_hide_when_accepted == 'yes')
		{
			var d = new Date();
			d.setTime(d.getTime() + (365 * 24 * 60 * 60 * 1000));

			document.cookie = "cookie_page_overlay_" + overlay_page_id + "=true; path=/; expires=" + d.toUTCString();

			if(dom_overlay_page_accepted.length > 0)
			{
				dom_overlay_page_accepted.fadeIn();
			}
		}

		return false;
	});
});