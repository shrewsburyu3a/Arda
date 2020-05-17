<?php

add_action('wp_enqueue_scripts', 'u3a_enqueue_styles');

function u3a_enqueue_styles()
{

	$parent_style = 'spacious';

	wp_enqueue_style($parent_style, get_template_directory_uri() . '/style.css');
	wp_enqueue_style('u3a', get_stylesheet_directory_uri() . '/style.css', array($parent_style), wp_get_theme()->get('Version'));
//	wp_enqueue_script("u3a-alerts", "https://unpkg.com/sweetalert/dist/sweetalert.min.js");
}

/**
 * function to show the footer info, copyright information
 */
if (!function_exists('spacious_footer_copyright')) :

	function spacious_footer_copyright()
	{
		$site_link = '<a href="' . esc_url(home_url('/')) . '" title="' . esc_attr(get_bloginfo('name', 'display')) . '" ><span>' . get_bloginfo('name', 'display') . '</span></a>';

		$wp_link = '<a href="' . esc_url('https://wordpress.org') . '" target="_blank" title="' . esc_attr__('WordPress', 'u3a') . '"><span>' . __('WordPress', 'u3a') . '</span></a>';
		$k_link = '<a href="' . esc_url('https://krystal.co.uk') . '" target="_blank" title="' . esc_attr__('Krystal', 'u3a') . '"><span>' . __('Krystal', 'u3a') . '</span></a>';

		$tg_link = '<a href="' . esc_url('https://themegrill.com/themes/spacious') . '" target="_blank" title="' . esc_attr__('ThemeGrill', 'spacious') . '" rel="author"><span>' . __('ThemeGrill', 'spacious') . '</span></a>';
		$ms_link = '<a href="' . esc_url('https://mullsoft.co.uk/themes/u3a') . '" target="_blank" title="' . esc_attr__('MullSoft', 'u3a') . '" rel="author"><span>' . __('MullSoft', 'u3a') . '</span></a>';

		$default_footer_value = sprintf(__('Copyright &copy; %1$s %2$s.', 'spacious'), date('Y'), $site_link) . ' ' . sprintf(__('Powered by %s.', 'u3a'), $wp_link) . ' ' . sprintf(__('Theme: %1$s by %2$s based on %3$s by %4$s.', 'u3a'), 'U3A', $ms_link, 'Spacious', $tg_link)
		  . sprintf(__(' Hosted by %s.', 'u3a'), $k_link);

		$u3a_footer_copyright = '<div class="copyright"><span class="arda" title="The action in JRR Tolkein\'s Lord of the Rings takes place in the age of Arda, aka the Third Age of Middle Earth">Arda</span>' . $default_footer_value . '</div>';
		echo $u3a_footer_copyright;
	}


















endif;

