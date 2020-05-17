<?php

/**
 * Plugin Name: U3A Website
 * Plugin URI: https://mullsoft.co.uk/u3a-website/
 * Description: Sets up and maintains website for U3As.
 * Author: MullSoft Ltd.
 * Author URI: https://mullsoft.co.uk/
 * Version: 1.0
 * Text Domain: u3a-website
 *
 * Copyright: (c) 2019 MullSoft Ltd. (info@mullsoft.co.uk)
 *
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 *
 * @author    MullSoft Ltd.
 * @copyright Copyright (c) 2019, MullSoft Ltd.
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 *
 */
defined('ABSPATH') or exit;
require_once(ABSPATH . 'wp-config.php');
require_once 'U3ADatabase.php';
require_once 'u3a_information.php';
require_once 'u3a_shortcodes.php';
require_once 'u3a_actions.php';
require_once 'u3a_ajax.php';

if (!function_exists('write_log'))
{

	function write_log($log, ...$rest)
	{
		if (true === WP_DEBUG)
		{
			$t = "[" . gettype($log) . "] ";
			if (is_array($log) || is_object($log))
			{
				error_log($t . print_r($log, true));
			}
			else
			{
				error_log($t . $log);
			}
			if ($rest)
			{
				foreach ($rest as $r)
				{
					$t = "[" . gettype($r) . "] ";
					if (is_array($r) || is_object($r))
					{
						error_log($t . print_r($r, true));
					}
					else
					{
						error_log($t . $r);
					}
				}
			}
		}
	}

}

if (!function_exists('audit_log'))
{

	function audit_log($log, ...$rest)
	{
		$audit_path = plugin_dir_path(__FILE__) . "logs/auditlog_" . date("Ymd") . ".log";
		$audit_file = fopen($audit_path, "a");
//		write_log("attempting writing to " . $audit_path);
		if ($audit_file)
		{
			$prefix = "";
			$current_wp_user = wp_get_current_user();
			if ($current_wp_user && $current_wp_user->ID)
			{
				$lg = $current_wp_user->user_login;
				$prefix .= "\n" . $lg . date("Y-m-d H:i:s") . " :- ";
			}
//			write_log("writing to " . $audit_path . ": " . U3A_Utilities::as_string($log));
			fwrite($audit_file, $prefix . U3A_Utilities::as_string($log));
			foreach ($rest as $r)
			{
				fwrite($audit_file, $prefix . U3A_Utilities::as_string($r));
			}
			fclose($audit_file);
		}
	}

}

global $wp_roles;

/**
 * On activation:
 * 1. load all groups from db
 * 2 foreach group
 * 	create member role if not exist
 * 	add all members to member role
 * 	create coordinator role if not exist
 * 	add coordinator(s) to coordinator role
 * 	create page if not exist
 * 	    group name
 * 	    coordinator
 * 	    contact
 * 	    description
 * 	    meets when and where
 * 	create documents page if not exist
 * 	create gallery page if not exist
 * 	restrict access to documents and gallery to members
 */
function u3a_on_activation()
{
	write_log("U3A name " . U3A_Information::u3a_name() . " active");
	if (get_option("enable_management") === false)
	{

		// The option hasn't been added yet. We'll add it with $autoload set to 'no'.
		$deprecated = null;
		$autoload = 'yes';
		add_option("enable_management", "no", $deprecated, $autoload);
	}
	update_option("youtube_playlistid", "PLrM7DAAMAfswLQtvB3jY9rLyK-6eCBIsq");
	update_option("youtube_key", "AIzaSyCHzrVtlUHZXytCYbn-iznEikzRAgTrKHY");
	update_option("youtube_channelid", "UCGNN5yYKjW3kexi4r9AkXeQ");
	update_option("video_source", "youtube");
	$grps1 = U3A_Row::load_array_of_objects("U3A_Groups");
	$grps = $grps1["result"];
	$grp_pages = U3A_Information::u3a_group_pages($grps, true);
	$groups_page = U3A_Information::u3a_groups_page();
	$newsletter_page = U3A_Information::u3a_newsletter_page();
	$meetings_page = U3A_Information::u3a_meetings_page();
	$join_page = U3A_Information::u3a_join_page();
	$join_page = U3A_Information::u3a_committee_page();
	$mg_page = U3A_Information::u3a_manage_groups_page();
	$mm_page = U3A_Information::u3a_manage_members_page();
	$vid_page = U3A_Information::u3a_videos_page();
	$coord_page = U3A_Information::u3a_coordinators_page();
	$admin_page = U3A_Information::u3a_administration_page();
	$mbrs_page = U3A_Information::u3a_allmembers_page();
	$hlp_page = U3A_Information::u3a_help_page();
	$hlpv_page = U3A_Information::u3a_help_videos_page();
	$pr_page = U3A_Information::u3a_payment_return_page();
	$pc_page = U3A_Information::u3a_payment_complete_page();
	$pv_page = U3A_Information::u3a_payment_void_page();
	$my_page = U3A_Information::u3a_members_personal_page();
	wp_schedule_event(time(), 'daily', 'u3a_daily_check');
}

function u3a_on_deactivation()
{
	write_log("U3A name " . U3A_Information::u3a_name() . " no longer active");
}

add_action('u3a_daily_check', 'u3a_daily_run');

function u3a_daily_run()
{
	write_log("daily run");
	U3A_Tasks::get_to_do_procedures_run_and_update();
}

add_filter("um_custom_error_message_handler", 'u3a_login_error', 100);

function u3a_login_error($str)
{
	$ret = "";
//    switch ($str)
	if (isset($_REQUEST['err']) && !empty($_REQUEST['err']))
	{
		switch ($_REQUEST['err']) {
			case 'invalid_membership_number':
				$ret = "Invalid membership number.";
				break;
			case 'wrong_email':
				$ret = "The email provided is not the same as the one held in the database";
				break;
			case 'no_member':
				$ret = "There is no known member with that number";
				break;
			default:
				$ret = "There has been an error " . $_REQUEST['err'] . ".";
				break;
		}
	}
	return $ret;
}

register_activation_hook(__FILE__, "u3a_on_activation");

register_deactivation_hook(__FILE__, "u3a_on_deactivation");

add_action('init', 'custom_login');

function custom_login()
{
	global $pagenow;
	if ('wp-login.php' == $pagenow)
	{
		wp_redirect('/index.php');
		exit();
	}
}

/**
 * add user validation hook on register
 */
add_action('um_submit_form_errors_hook__registration', 'u3a_is_member', 10, 1);

//add_action('um_before_new_user_register', 'u3a_is_member', 10, 1);
//update_user_meta( int $user_id, string $meta_key, mixed $meta_value, mixed $prev_value = '' )

add_action('um_after_save_registration_details', 'u3a_set_user_meta', 10, 2);

add_action('um_on_login_before_redirect', 'u3a_check_roles', 10, 1);

add_action('um_after_register_fields', 'u3a_add_a_hidden_field_to_register');

add_action('um_registration_after_auto_login', 'u3a_registration_complete');

function u3a_add_a_hidden_field_to_register($args)
{
	echo '<input class="um-form-field um-error not-required " type="hidden" name="user_email-47" id="user_email-47" value="no-reply@shrewsburyu3a.org.uk" placeholder="" data-validate="unique_email" data-key="user_email" />';
}

function u3a_check_roles($user_id)
{
//	global $ultimatemember;
// your code here
	$mbr = U3A_Information::u3a_user_from_id($user_id);
//	write_log(current_theme_supports('title-tag'));
	if ($mbr)
	{
//		$role1 = get_role("um_group-coordinator");
//		$role1->add_cap('unfiltered_upload');
//		$role2 = get_role("um_committee-member");
//		$role2->add_cap('unfiltered_upload');
//		$role3 = get_role("um_member-only");
//		$role3->add_cap('unfiltered_upload');
		$wpusr = new WP_User($user_id);
//		um_fetch_user($wpusr->ID);
		$wpusr->remove_role("um_member");
		$wpusr->remove_role("subscriber");
		$wpusr->remove_role("editor");
		$wpusr->remove_role("contributor");
		$wpusr->remove_role("author");
		if (U3A_Group_Members::is_a_coordinator($mbr) || U3A_Committee::is_committee_member($mbr))
		{
//			write_log("special");
			$wpusr->remove_role("um_member-only");
			if (U3A_Group_Members::is_a_coordinator($mbr))
			{
//				write_log("coordinator");
				$wpusr->add_role("um_group-coordinator");
//				$ultimatemember->user->set_role('um_group-coordinator');
			}
			if (U3A_Committee::is_committee_member($mbr))
			{
//				write_log("committee");
				$wpusr->add_role("um_committee-member");
//				$ultimatemember->user->set_role('um_committee-member');
			}
			if (U3A_Committee::is_webmanager($mbr) || U3A_Permissions::has_permission("site administration", $mbr))
			{
//				write_log("administrator");
				$wpusr->add_role("um_site-administrator");
//				$ultimatemember->user->set_role('um_site-administrator');
				$wpusr->add_role("administrator");
			}
			else
			{
				$wpusr->remove_role("administrator");
			}
		}
		else
		{
//			write_log("just a member");
			$wpusr->remove_role("um_group-coordinator");
			$wpusr->remove_role("um_committee-member");
			$wpusr->remove_role("um_site-administrator");
			$wpusr->remove_role("administrator");
			$wpusr->add_role("um_member-only");
		}
//		write_log($wpusr);
//		um_fetch_user($user_id);
//		write_log($user_id);
//		write_log($ultimatemember);
//		write_log($ultimatemember->classes["user"]);
//		// Change user role
//		$ultimatemember->classes["user"]->set_role('member');
//		if (U3A_Group_Members::is_coordinator($mbr))
//		{
//			$ultimatemember->classes["user"]->set_role('group-coordinator');
//		}
//		if (U3A_Committee::is_committee_member($mbr))
//		{
//			$ultimatemember->classes["user"]->set_role('committee-member');
//		}
	}
}

function u3a_set_user_meta($user_id, $args)
{
	global $ultimatemember;
	$user_number = $args["user_login"];
	$user_email = $args["user_email"];
	$real_email = $args["real_email"];
	$mbr = U3A_Row::load_single_object("U3A_Members", ["membership_number" => $user_number]);
//	write_log("u3a_set_user_meta");
//	write_log($args);

	if ($mbr)
	{
		update_user_meta($user_id, "first_name", $mbr->forename);
		update_user_meta($user_id, "last_name", $mbr->surname);
		$wpusr = new WP_User($user_id);
//		$wpusr->add_role("um_member");
		if (U3A_Group_Members::is_coordinator($mbr) || U3A_Committee::is_committee_member($mbr))
		{
			if (U3A_Group_Members::is_coordinator($mbr))
			{
				$wpusr->add_role("um_group-coordinator");
			}
			if (U3A_Committee::is_committee_member($mbr))
			{
				$wpusr->add_role("um_committee-member");
			}
			if (U3A_Committee::is_webmanager($mbr) || U3A_Permissions::has_permission("site administration", $mbr))
			{
				$wpusr->add_role("um_site-administrator");
				$wpusr->add_role("administrator");
			}
			else
			{
				$wpusr->remove_role("administrator");
			}
		}
		else
		{
			$wpusr->add_role("um_member-only");
		}
		update_option("real-email-" . $user_id, $real_email);
//		$result = wp_update_user(["ID" => $user_id, "user_email" => $real_email]);
//		write_log($result);
//		$usr = new WP_User($user_id);
//		um_fetch_user($user_id);
//		write_log($ultimatemember);
//// Change user role
//		$ultimatemember->classes["user"]->set_role('member');
//		if (U3A_Group_Members::is_coordinator($mbr))
//		{
//			$ultimatemember->classes["user"]->set_role('group-coordinator');
//		}
//		if (U3A_Committee::is_committee_member($mbr))
//		{
//			$ultimatemember->classes["user"]->set_role('committee-member');
//		}
//		$usr->add_role($role);
	}
}

function u3a_registration_complete($user_id)
{
	$real_email = get_option("real-email-" . $user_id);
	$u3a_wpusr = U3A_Row::load_single_object("WP_Users", ["ID" => $user_id]);
//	write_log("u3a_registration_complete");
//	write_log($user_id);
//	write_log($u3a_wpusr);
	if ($u3a_wpusr)
	{
		$u3a_wpusr->user_email = $real_email;
		$u3a_wpusr->save();
//		write_log($u3a_wpusr);
	}
}

function u3a_is_member($args)
{
	write_log($args);
	$user_number = $args["user_login"];
	$user_email = $args["user_email"];
	$real_email = $args["real_email"];
	$mbr = U3A_Row::load_single_object("U3A_Members", ["membership_number" => $user_number, "status" => 'Current']);
//	global $ultimatemember;
//	write_log("u3a_is_member");
//	write_log($args);

	if ($mbr)
	{
		$mbremail = strtolower($mbr->email);
		if (($mbremail != strtolower($user_email)) && ($mbremail != strtolower($real_email)))
		{
			exit(wp_redirect(add_query_arg('err', 'wrong_email')));
//			$ultimatemember->form->add_error('user_registration', 'Your email is not the same as the one held in the database');
		}
	}
	else
	{
		exit(wp_redirect(add_query_arg('err', 'invalid_membership_number')));
//		$ultimatemember->form->add_error('user_registration', 'invalid membership number');
	}
}

//add_filter('wp_nav_menu_items', 'new_nav_menu_items');
//
//function new_nav_menu_items($items)
//{
//	write_log("new_nav_menu_items", is_string($items) ? "string" : "not string");
//	write_log($items);
//	$newitems = $items;
//	$lookfor1 = "{user_avatar_small}";
//	$lookfor2 = "{first_name} {last_name}";
//	$lookfor3 = '<ul class="sub-menu">';
//	$len1 = strlen($lookfor1);
//	$len2 = strlen($lookfor2);
//	$len3 = strlen($lookfor3);
//	$where1 = strpos($items, $lookfor1);
//	$where2 = 0;
//	if ($where1 === FALSE)
//	{
//		$where1 = strpos($items, $lookfor2);
//		if ($where1 !== FALSE)
//		{
//			$where2 = $where1 + $len2;
//		}
//	}
//	else
//	{
//		$where2 = $where1 + $len1;
//	}
//	if ($where2)
//	{
//		$where3 = strpos($items, $lookfor3, $where2);
//		if ($where3 !== FALSE)
//		{
//
//		}
//	}
//	return $items;
//}

add_filter('um_nav_menu_roles_item_visibility', 'u3a_menu_item_visibility', 10, 2);

function u3a_menu_item_visibility($visible, $item)
{
	$ret = false;
	$mbr = U3A_Information::u3a_logged_in_user();
	switch ($item->title) {
		case "admin":
		case "user":
			{
//				$ret = (U3A_Group_Members::is_a_coordinator($mbr) || U3A_Committee::is_committee_member($mbr));
				$ret = false;
				break;
			}
		case "beacon":
			{
//				if ($mbr == null)
//				{
//					$ret = false;
//				}
//				else
//				{
//					if ($item->url === '#')
//					{
//						$ret = (U3A_Group_Members::is_a_coordinator($mbr) || U3A_Committee::is_committee_member($mbr));
//					}
//					else
//					{
//						$ret = !U3A_Group_Members::is_a_coordinator($mbr) && !U3A_Committee::is_committee_member($mbr);
//					}
//				}
				$ret = false;
				break;
			}
		case "coordinators":
			{
				$ret = ($mbr != null) && (U3A_Group_Members::is_a_coordinator($mbr) || U3A_Committee::is_committee_member($mbr));
				break;
			}
		case "help":
		case "help documents":
		case "help videos":
		case "newsletters":
		case "committee":
		case "videos":
			{
				$ret = $mbr != null;
				break;
			}
		case "administration":
			{
//				write_log("admin");
//				write_log($item);
				$ret = ($mbr != null) && U3A_Information::u3a_has_permission($mbr->get_real_member(), "site administration");
				if ($ret)
				{
					$ret = U3A_Utilities::u3a_check_menu_item($item);
				}
				break;
			}
		case "my page":
		case "profile":
		case "logout":
			{
//				write_log("admin");
//				write_log($item);
				$ret = ($mbr != null);
				if ($ret)
				{
					$ret = U3A_Utilities::u3a_check_menu_item($item);
				}
				break;
			}
		case "{first_name} {last_name}":
			{
//				write_log("names");
//				write_log($item);
				$ret = false;
				if ($mbr != null)
				{
					$ret = true;
					$current_wp_user = wp_get_current_user();
					if (get_user_meta($current_wp_user->ID, "profile_photo", true))
					{
						$ret = false;
					}
				}
				break;
			}
		case "{user_avatar_small}":
			{
//				write_log("image");
//				write_log($item);
				$ret = false;
				if ($mbr != null)
				{
					$current_wp_user = wp_get_current_user();
					if (get_user_meta($current_wp_user->ID, "profile_photo", true))
					{
						$ret = true;
					}
				}
				break;
			}
		case "login":
		case "membership":
			{
				$ret = $mbr == null;
				break;
			}

		case "webmail":
			{
//				write_log("admin");
//				write_log($item);
				$ret = ($mbr != null) && U3A_Committee::is_committee_member($mbr);
				if ($ret)
				{
					$ret = U3A_Utilities::u3a_check_menu_item($item);
				}
				break;
			}
		case "members":
			{
				$ret = U3A_Committee::is_committee_member($mbr) ? true : false;
				break;
			}
		case "home":
		case "groups":
		case "meetings":
		case "fb":
		default:
			{
				$ret = true;
			}
	}
	return $ret;
}

//add_action('wp_head', 'my_custom_styles', 100);
//
//function my_custom_styles()
//{
// echo "<style>*{color: red}</style>";
//}

add_filter('um_user_register_submitted__email', 'u3a_register_submitted__email', 10, 1);

function u3a_register_submitted__email($user_email)
{
	write_log("u3a_register_submitted__email");
	write_log($user_email);
	return "mike@mullsoft.co.uk";
}

add_filter('um_field_default_value', 'u3a_field_default_value', 10, 2);

function u3a_field_default_value($default, $data, $type)
{
	write_log("u3a_field_default_value");
	write_log($default);
	write_log($data);
	write_log($type);
	return $default;
}

// Register and load the widget
function u3a_load_widget()
{
	register_widget('U3APageLinkWidget');
}

add_action('widgets_init', 'u3a_load_widget');

class U3APageLinkWidget extends WP_Widget
{

	function __construct()
	{
		parent::__construct(false, 'Page Link Widget');
	}

	function widget($args, $instance)
	{
		if (!isset($instance['wpage']) || (int) $instance['wpage'] <= 0)
			return;
		$page = get_post($instance['wpage']);
		echo $args['before_widget'];
		echo '<div class="u3a-link-box">';
		if (isset($instance['title']))
		{
			$title = apply_filters('widget_title', $instance['title']);
			if ($title)
			{
				echo $args['before_title'] . $title . $args['after_title'];
			}
		}
		echo '<a href="' . get_permalink($page) . '">' . $page->post_title . '</a>';
		echo '</div>';
		echo $args['after_widget'];
	}

	function update($new_instance, $old_instance)
	{
		$instance = $old_instance;
		$instance['title'] = strip_tags($new_instance['title']);
		if (isset($new_instance['wpage']) && (int) $new_instance['wpage'] > 0)
		{
			$instance['wpage'] = $new_instance['wpage'];
		}
		return $instance;
	}

	function form($instance)
	{
		$default = array('wpage' => '-1', 'title' => '');
		$instance = wp_parse_args((array) $instance, $default);
		$args = array(
			'name'					 => $this->get_field_name('wpage'),
			'show_option_none'	 => 'None',
			'option_none_value'	 => '-1',
			'selected'				 => $instance['wpage']
		);
		echo '<p><label>Title:</label>';
		echo '<input class="widefat" name="' . $this->get_field_name('title') . '" type="text" value="' . $instance['title'] . '" /></p>';
		wp_dropdown_pages($args);
	}

}
