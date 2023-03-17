<?php

class mf_page_overlay
{
	function __construct()
	{
		$this->meta_prefix = 'mf_page_overlay_';
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