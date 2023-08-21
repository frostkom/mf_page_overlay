<?php

class mf_page_overlay
{
	function __construct()
	{
		$this->meta_prefix = 'mf_page_overlay_';
	}

	function cron_base()
	{
		global $wpdb;

		$obj_cron = new mf_cron();
		$obj_cron->start(__CLASS__);

		if($obj_cron->is_running == false)
		{
			/* Set No Index on overlay pages */
			#########################
			if(is_plugin_active("mf_theme_core/index.php"))
			{
				global $obj_theme_core;

				if(!isset($obj_theme_core))
				{
					$obj_theme_core = new mf_theme_core();
				}

				$result = $wpdb->get_results($wpdb->prepare("SELECT meta_value FROM ".$wpdb->posts." LEFT JOIN ".$wpdb->postmeta." ON ".$wpdb->posts.".ID = ".$wpdb->postmeta.".post_id WHERE post_type = %s AND meta_key = %s AND meta_value > 0 GROUP BY meta_value", 'page', $this->meta_prefix.'page_id'));

				foreach($result as $r)
				{
					$post_id = $r->meta_value;

					$page_index = get_post_meta($post_id, $obj_theme_core->meta_prefix.'page_index', true);

					if($page_index != 'noindex')
					{
						//do_log("Set No Index on ".$post_id);
						$obj_theme_core->set_noindex_on_page($post_id);
					}
				}
			}
			#########################
		}

		$obj_cron->end();
	}

	function rwmb_meta_boxes($meta_boxes)
	{
		if(IS_EDITOR)
		{
			$meta_boxes[] = array(
				'id' => $this->meta_prefix.'settings',
				'title' => __("Page Overlay", 'lang_page_overlay'),
				'post_types' => array('page'),
				'context' => 'side',
				'priority' => 'low',
				'fields' => array(
					array(
						'name' => __("Page", 'lang_page_overlay')." <a href='".admin_url("post-new.php?post_type=page&post_title=".__("Popup", 'lang_page_overlay')."&content=.button_close")."'><i class='fa fa-plus-circle fa-lg'></i></a>",
						'id' => $this->meta_prefix.'page_id',
						'type' => 'page',
					),
				)
			);
		}

		return $meta_boxes;
	}

	function wp_head()
	{
		global $post;

		if(isset($post->ID) && $post->ID > 0)
		{
			$page_overlay = get_post_meta($post->ID, $this->meta_prefix.'page_id', true);

			if($page_overlay > 0)
			{
				$plugin_include_url = plugin_dir_url(__FILE__);
				$plugin_version = get_plugin_version(__FILE__);

				mf_enqueue_style('style_page_overlay', $plugin_include_url."style.css", $plugin_version);
				mf_enqueue_script('script_page_overlay', $plugin_include_url."script.js", $plugin_version);

				$this->body_class = "has_page_overlay";

				$this->footer_output = "<div id='overlay_page' class='overlay_container modal disable_close'>
					<div>".apply_filters('the_content', mf_get_post_content($page_overlay))."</div>
				</div>";
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