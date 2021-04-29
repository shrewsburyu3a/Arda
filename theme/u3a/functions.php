<?php

/* Arda v1.0
 * Copyright 2021 Mike Curtis (mike@computermike.biz)
 *
 * This file is part of Arda.
 *   Arda is free software: you can redistribute it and/or modify
 *   it under the terms of the GNU Affero General Public License version 3
 *   as published by the Free Software Foundation
 *
 *   Ardais distributed in the hope that it will be useful,
 *   but WITHOUT ANY WARRANTY; without even the implied warranty of
 *   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *   GNU Affero General Public License for more details.
 *
 *   You can get a copy The GNU Affero General Public license from
 *   http://www.gnu.org/licenses/agpl-3.0.html
 *
 */

add_action('wp_enqueue_scripts', 'u3a_enqueue_styles');

function u3a_enqueue_styles()
{

	$parent_style = 'spacious';

	wp_enqueue_style($parent_style, get_template_directory_uri() . '/style.css');
	wp_enqueue_style('u3a', get_stylesheet_directory_uri() . '/style.css', array($parent_style),
	  wp_get_theme()->get('Version'));
//	wp_enqueue_script("u3a-alerts", "https://unpkg.com/sweetalert/dist/sweetalert.min.js");
}

/**
 * function to show the footer info, copyright information
 */
if (!function_exists('spacious_footer_copyright'))
{

	function spacious_footer_copyright()
	{
//		$site_link = '<a href="' . esc_url(home_url('/')) . '" title="' . esc_attr(get_bloginfo('name', 'display')) . '" ><span>' . get_bloginfo('name', 'display') . '</span></a>';
		$site_link = '<a href="http://computermike.biz" title="Computermike" ><span>Mike Curtis</span></a>';

		$wp_link = '<a href="' . esc_url('https://wordpress.org') . '" target="_blank" title="' . esc_attr__('WordPress',
			 'u3a') . '"><span>' . __('WordPress', 'u3a') . '</span></a>';
		$k_link = '<a href="' . esc_url('https://krystal.co.uk') . '" target="_blank" title="' . esc_attr__('Krystal', 'u3a') . '"><span>' . __('Krystal',
			 'u3a') . '</span></a>';

		$tg_link = '<a href="' . esc_url('https://themegrill.com/themes/spacious') . '" target="_blank" title="' . esc_attr__('ThemeGrill',
			 'spacious') . '" rel="author"><span>' . __('ThemeGrill', 'spacious') . '</span></a>';
		$ms_link = '<a href="' . esc_url('https://computermike.biz/themes/u3a') . '" target="_blank" title="' . esc_attr__('computermike',
			 'u3a') . '" rel="author"><span>' . __('computermike', 'u3a') . '</span></a>';

		$default_footer_value = sprintf(__('Copyright &copy; %1$s %2$s.', 'spacious'), date('Y'), $site_link) . ' ' . sprintf(__('Powered by %s.',
				'u3a'), $wp_link) . ' ' . sprintf(__('Theme: %1$s by %2$s based on %3$s by %4$s.', 'u3a'), 'U3A', $ms_link,
			 'Spacious', $tg_link)
		  . sprintf(__(' Hosted by %s.', 'u3a'), $k_link) . " UK Registered Charity no: 1148657";

		$u3a_footer_copyright = '<div class="copyright"><span class="arda" title="The action in JRR Tolkein\'s Lord of the Rings takes place in the age of Arda, aka the Third Age of Middle Earth">Arda</span>' . $default_footer_value . '</div>';
		echo $u3a_footer_copyright;
	}

}

if (!function_exists('u3a_render_header_logo'))
{

	function u3a_render_header_logo()
	{
		$url = get_theme_file_uri("header-logo.png");
		$div = '<div id="u3a-header-div"><img id="u3a-header-img" src="' . $url . '"/></div>';
		echo $div;
	}

}


