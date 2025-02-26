<?php

class mf_page_overlay
{
	var $meta_prefix = 'mf_page_overlay_';
	var $body_class;
	var $footer_output;

	function __construct(){}

	function cron_base()
	{
		global $wpdb;

		$obj_cron = new mf_cron();
		$obj_cron->start(__CLASS__);

		if($obj_cron->is_running == false)
		{
			/* Set No Index on overlay pages */
			#########################
			if(is_plugin_active("mf_base/index.php"))
			{
				global $obj_base;

				if(!isset($obj_base))
				{
					$obj_base = new mf_base();
				}

				$result = $wpdb->get_results($wpdb->prepare("SELECT meta_value FROM ".$wpdb->posts." LEFT JOIN ".$wpdb->postmeta." ON ".$wpdb->posts.".ID = ".$wpdb->postmeta.".post_id WHERE post_type = %s AND meta_key = %s AND meta_value > 0 GROUP BY meta_value", 'page', $this->meta_prefix.'page_id'));

				foreach($result as $r)
				{
					$post_id = $r->meta_value;

					$page_index = get_post_meta($post_id, $obj_base->meta_prefix.'page_index', true);

					if($page_index != 'noindex')
					{
						$obj_base->set_noindex_on_page($post_id);
					}
				}
			}
			#########################
		}

		$obj_cron->end();
	}

	function get_overlay_pages()
	{
		global $wpdb;

		$out = array();

		$result = $wpdb->get_results($wpdb->prepare("SELECT meta_value FROM ".$wpdb->posts." INNER JOIN ".$wpdb->postmeta." ON ".$wpdb->posts.".ID = ".$wpdb->postmeta.".post_id WHERE post_type = %s AND meta_key = %s AND meta_value > 0 GROUP BY meta_value", 'page', $this->meta_prefix.'page_id'));

		foreach($result as $r)
		{
			if($r->meta_value > 0)
			{
				$out[] = $r->meta_value;
			}
		}

		return $out;
	}

	function display_post_states($post_states, $post)
	{
		global $wpdb;

		$result = $wpdb->get_results($wpdb->prepare("SELECT post_title FROM ".$wpdb->posts." INNER JOIN ".$wpdb->postmeta." ON ".$wpdb->posts.".ID = ".$wpdb->postmeta.".post_id WHERE meta_key = %s AND meta_value = '%d'", $this->meta_prefix.'page_id', $post->ID));

		if($wpdb->num_rows > 0)
		{
			$post_titles = "";

			foreach($result as $r)
			{
				$post_titles .= ($post_titles != '' ? ", " : "").$r->post_title;
			}

			$post_states[$this->meta_prefix.'page_id'] = sprintf(__("Overlay on %s", 'lang_page_overlay'), $post_titles);
		}

		return $post_states;
	}

	function rwmb_meta_boxes($meta_boxes)
	{
		if(IS_EDITOR)
		{
			$post_id = check_var('post');

			if($post_id > 0 && in_array($post_id, $this->get_overlay_pages()))
			{
				$arr_fields = array(
					array(
						'name' => __("Hide when accepted", 'lang_page_overlay'),
						'id' => $this->meta_prefix.'hide_when_accepted',
						'type' => 'select',
						'options' => get_yes_no_for_select(),
						'std' => 'no',
					),
				);
			}

			else
			{
				$arr_fields = array(
					array(
						'name' => __("Page", 'lang_page_overlay')." <a href='".admin_url("post-new.php?post_type=page&post_title=".__("Popup", 'lang_page_overlay')."&content=.button_close")."'><i class='fa fa-plus-circle fa-lg'></i></a>",
						'id' => $this->meta_prefix.'page_id',
						'type' => 'page',
					),
				);
			}

			$meta_boxes[] = array(
				'id' => $this->meta_prefix.'settings',
				'title' => __("Page Overlay", 'lang_page_overlay'),
				'post_types' => array('page'),
				'context' => 'side',
				'priority' => 'low',
				'fields' => $arr_fields,
			);
		}

		return $meta_boxes;
	}

	function wp_head()
	{
		global $post;

		if(isset($post->ID) && $post->ID > 0) // && !is_user_logged_in()
		{
			$overlay_page_id = get_post_meta($post->ID, $this->meta_prefix.'page_id', true);

			if($overlay_page_id > 0)
			{
				ob_start();

				query_posts(array(
					'post_type' => 'page',
					'p' => $overlay_page_id,
				));

				while(have_posts())
				{
					the_post();
					the_content();
				}

				$post_content = ob_get_contents();
				ob_end_clean();

				wp_reset_query();

				if($post_content != '')
				{
					$overlay_hide_when_accepted = get_post_meta($overlay_page_id, $this->meta_prefix.'hide_when_accepted', true);

					$this->body_class = "has_page_overlay";

					$plugin_include_url = plugin_dir_url(__FILE__);

					mf_enqueue_style('style_page_overlay', $plugin_include_url."style.css");
					mf_enqueue_script('script_page_overlay', $plugin_include_url."script.js");

					$this->footer_output = "<div id='overlay_page' data-overlay_page_id='".$overlay_page_id."' data-overlay_hide_when_accepted='".$overlay_hide_when_accepted."' class='overlay_container modal disable_close'>
						<div>".$post_content."</div>
					</div>";

					if(IS_SUPER_ADMIN && $overlay_hide_when_accepted == 'yes')
					{
						$this->footer_output .= "<div id='overlay_page_accepted'>
							<span class='fa-stack fa-2x' title='".__("You have accepted. Do you wish to remove this acceptance?", 'lang_page_overlay')."'>
								<i class='fas fa-cookie-bite fa-stack-1x'></i>
								<i class='fas fa-ban fa-stack-2x red'></i>
							</span>
						</div>";
					}
				}
			}
		}
	}

	function body_class($classes)
	{
		if(isset($this->body_class) && $this->body_class != '')
		{
			$classes[] = $this->body_class;
		}

		return $classes;
	}

	function wp_footer()
	{
		if(isset($this->footer_output) && $this->footer_output != '')
		{
			echo $this->footer_output;
		}
	}
}