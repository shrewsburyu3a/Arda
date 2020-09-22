<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

require_once(ABSPATH . 'wp-config.php');
require_once 'U3ADatabase.php';
require_once 'u3a_information.php';
require_once 'u3a_mail.php';

add_action('wp_enqueue_scripts', 'u3a_enqueued_scripts');

/**
 * Localization object
 *
 * @since 1.0.0
 */
function u3a_enqueued_scripts()
{
	wp_enqueue_style('u3a-dropdown-style', plugin_dir_url(__FILE__) . 'css/dropdown.min.css');
	wp_enqueue_style('u3a-chosen-style', plugin_dir_url(__FILE__) . 'chosen/chosen.min.css');
	wp_enqueue_style('u3a-timepicker-style', plugin_dir_url(__FILE__) . 'css/timepicker.min.css');
	wp_enqueue_style('u3a-style', plugin_dir_url(__FILE__) . 'css/u3a-website.css');
	wp_enqueue_style('dashicons');
	wp_enqueue_script('jquery-ui-core');
	wp_enqueue_script('jquery-ui-sortable');
	wp_enqueue_script('u3a-dropdown-script', plugin_dir_url(__FILE__) . 'js/jquery.dropdown.min.js', ['jquery'], null, true);
	wp_enqueue_script('u3a-chosen-script', plugin_dir_url(__FILE__) . 'chosen/chosen.jquery.min.js', ['jquery'], null, true);
	wp_enqueue_script('u3a-timepicker-script', plugin_dir_url(__FILE__) . 'js/timepicker.min.js', null, null, true);
	wp_enqueue_script('u3a-uploads', plugin_dir_url(__FILE__) . 'js/u3a-website.js', ['jquery', 'u3a-chosen-script'], null, true);
//	if (is_home())
//	{EBaCxw_3yOSbGTY0NtVfoqOMImS3jDlPmiGk7Gj6Cjf1h6t6KuXMWqcSNvV9dPnRLNf9WRgq5Xms3hRg
//	wp_enqueue_script('u3a-paypal', "https://www.paypal.com/sdk/js?currency=GBP&client-id=ATpHQmey4eyA0ZNC7HdvVNHQ6DM7nPNufyDmYO1kHniGjE3692Km1nHVuHEfzzbdJk4bsVIyGO3ygDb8", null, null, true);
//	wp_enqueue_script('u3a-paypal', "https://www.paypal.com/sdk/js?currency=GBP&client-id=AdhOTZv-wHs1S07WdSbHmXnD2w8Aip93Bq18amjIYrsKGG8V4oFd4kRj7uVRLUP0VcV9wT0ZWiKIc0wv&disable-funding=card", null, null, true);
//	wp_enqueue_script('u3a-paypal', "https://www.paypal.com/sdk/js?currency=GBP&client-id=AWswT9r10ULNtH8sOmXNRhlgWcN4S4TqeVn8RAAQn1H00IU9D-mqPg8Ua6E_vH2UppjwXJ05Jv4jrUYo", null, null, true);
	$pp = U3A_Information::u3a_get_paypal();
	wp_enqueue_script('u3a-paypal', "$pp", null, null, true);
//	}
	wp_enqueue_script("u3a-alerts", "https://cdn.jsdelivr.net/npm/sweetalert2@9");
//	wp_enqueue_script("u3a-alerts", "https://unpkg.com/sweetalert/dist/sweetalert.min.js");
	wp_localize_script('u3a-uploads', 'settings', ['ajaxurl' => admin_url('admin-ajax.php'), "slideshow" => plugin_dir_url(__FILE__) . 'u3a_slideshow.php']);
}

add_action("wp_head", "u3a_wp_head");

function u3a_wp_head()
{
	?>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-modal/0.9.1/jquery.modal.min.js"></script>
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jquery-modal/0.9.1/jquery.modal.min.css" />
	<?php
}

add_action("spacious_before_header", "u3a_option_values");

function u3a_option_values()
{
	$mbr = U3A_Information::u3a_logged_in_user();
	if ($mbr)
	{
		$opts = U3A_Options_Values::get_option_values(U3A_Options::OPTION_CATEGORY_MEMBER, $mbr->id, true);
		foreach ($opts as $oval)
		{
			echo "<input type='hidden' value='$oval' class='u3a-member-option u3a-option'/>";
		}
		$grps = U3A_Group_Members::get_groups_for_member($mbr);
		if ($grps)
		{
			foreach ($grps as $grp)
			{
				$opts = U3A_Options_Values::get_option_values(U3A_Options::OPTION_CATEGORY_GROUP, $grp->id, true);
				foreach ($opts as $oval)
				{
					echo "<input type='hidden' value='$oval' class='u3a-group-option u3a-option'/>";
				}
			}
		}
	}
	$is_live = U3A_Information::u3a_is_live_server() ? 1 : 0;
	echo "<input type='hidden' value='$is_live' id='u3a-is-live'/>";
//	$imagick = new Imagick();
//	$fonts = $imagick->queryFonts();
//	foreach ($fonts as $font)
//	{
//		write_log("font $font");
//	}
}

/* add new tab called "mytab" */

add_filter('um_account_page_default_tabs_hook', 'my_custom_tab_in_um', 100);

function my_custom_tab_in_um($tabs)
{
	$tabs[800]['memberform']['icon'] = 'um-faicon-keyboard-o';
	$tabs[800]['memberform']['title'] = 'Membership Details';
	$tabs[800]['memberform']['custom'] = true;
	$tabs[800]['memberpage']['icon'] = 'um-faicon-smile-o';
	$tabs[800]['memberpage']['title'] = 'My Page';
	$tabs[800]['memberpage']['custom'] = true;
	return $tabs;
}

/* make our new tab hookable */

add_action('um_account_tab__memberform', 'um_account_tab__memberform');

function um_account_tab__memberform($info)
{
	global $ultimatemember;
	extract($info);

	$output = $ultimatemember->account->get_tab_output('memberform');
	if ($output)
	{
		echo $output;
	}
}

add_action('um_account_tab__memberpage', 'um_account_tab__memberpage');

function um_account_tab__memberpage($info)
{
	global $ultimatemember;
	extract($info);

	$output = $ultimatemember->account->get_tab_output('memberpage');
	if ($output)
	{
		echo $output;
	}
}

/* Finally we add some content in the tab */

add_filter('um_account_content_hook_memberform', 'um_account_content_hook_memberform');

function um_account_content_hook_memberform($output)
{
	ob_start();
	?>

	<div class="um-field">
		<?php
		$mbr = U3A_Information::u3a_logged_in_user();
		echo (do_shortcode('[u3a_member_details_form member="' . $mbr->id . '" op="selfedit"]'))
		?>
		<!-- Here goes your custom content -->

	</div>

	<?php
	$output .= ob_get_contents();
	ob_end_clean();
	return $output;
}

add_action("u3a_group_member_added", "u3a_add_new_group_member_to_mailing_list", 10, 2);

function u3a_add_new_group_member_to_mailing_list($groups_id, $members_id)
{
	$grp = U3A_Groups::get_group($groups_id);
	if ($grp)
	{
		$ml = $grp->get_mailing_list();
		if ($ml)
		{
			$mbr = U3A_Members::get_member($members_id);
			if ($mbr)
			{
				$mailer = U3A_Mail::get_the_mailer();
				$mailer->add_member_to_list($mbr->get_mailing_list_member(), $ml);
			}
		}
	}
}

add_action("u3a_member_removed_from_group", "u3a_remove_group_member_from_mailing_list", 10, 2);

function u3a_remove_group_member_from_mailing_list($groups_id, $members_id)
{
	$grp = U3A_Groups::get_group($groups_id);
	if ($grp)
	{
		$ml = $grp->get_mailing_list();
		if ($ml)
		{
			$mbr = U3A_Members::get_member($members_id);
			if ($mbr)
			{
				$mailer = U3A_Mail::get_the_mailer();
				$mailer->remove_member_from_list($mbr->get_mailing_list_member(), $ml);
			}
		}
	}
}

add_action("u3a_member_edited", "u3a_member_edited_action", 10, 2);

function u3a_member_edited_action($mbr, $changed)
{
	$email_changed = array_key_exists("email", $changed) ? $changed["email"] : null;
	$forename_changed = array_key_exists("forename", $changed) ? $changed["forename"] : null;
	$surname_changed = array_key_exists("surname", $changed) ? $changed["surname"] : null;
	$gift_aid_changed = array_key_exists("gift_aid", $changed) ? $changed["gift_aid"] : null;
	$payment_type_changed = array_key_exists("payment_type", $changed) ? $changed["payment_type"] : null;
	if ($email_changed)
	{
		$user = get_user_by('login', $mbr->membership_number);
		if (!$user)
		{
			$user = get_user_by('login', '0' . $mbr->membership_number);
			if (!$user)
			{
				$user = get_user_by('login', '00' . $mbr->membership_number);
			}
		}
		if ($user)
		{
			$wpid = $user->ID;
			write_log("update wp user email " . $wpid);
			wp_update_user([
				'ID'			 => $wpid,
				'user_email' => $email_changed["newval"]
			]);
		}
		else
		{
			write_log("no wp user found for member " . $mbr->membership_number);
		}
		$groups = U3A_Group_Members::get_groups_for_member($mbr);
		if ($groups)
		{
			$member = $mbr->get_mailing_list_member();
			$mailer = U3A_Mail::get_the_mailer();
			foreach ($groups as $grp)
			{
				if ($grp->has_mailing_list())
				{
					$ml = $grp->get_mailing_list();
					$mailer->remove_member_from_list($email_changed["oldval"], $ml);
					$mailer->add_member_to_list($member, $ml);
//					$mailer->update_member_on_list($member, $email_changed["oldval"], $ml);
				}
			}
		}
	}
	if ($forename_changed || $surname_changed)
	{
		$user = get_user_by('login', $mbr->membership_number);
		if ($user)
		{
			$wpid = $user->ID;
			if ($forename_changed)
			{
				update_user_meta($wpid, "first_name", $mbr->forename);
			}
			if ($surname_changed)
			{
				update_user_meta($wpid, "last_name", $mbr->surname);
			}
		}
	}
	if ($gift_aid_changed || $payment_type_changed)
	{
		$wm = U3A_Committee::get_webmanager();
		$tr = U3A_Committee::get_treasurer();
		$mb = U3A_Committee::get_membership_secretary();
		$contents = "<p>" . $mbr->get_name() . " (" . $mbr->membership_number . ") has changed their payment details" . "</p>";
		if ($gift_aid_changed)
		{
			$contents .= "<p>Gift Aid has changed from " . $mbr->get_gift_aid_text($gift_aid_changed["oldval"]) . " to " . $mbr->get_gift_aid_text() . "</p>";
			$to = $tr->email;
			$cc = null;
		}
		if ($payment_type_changed)
		{
			$contents .= "<p>Payment Type has changed from " . $payment_type_changed["oldval"] . " to " . $payment_type_changed["newval"] . "</p>";
			$to = $mb->email;
			$cc = [$tr->email];
		}
		$to = $wm->email;
		$sent = U3A_Sent_Mail::send($wm->id, $to, "Member edit", $contents, $cc);
	}
}

function u3a_test_task_run()
{
	write_log("u3a_test_task_run");
}

//add_filter('um_browser_url_redirect_to__filter', 'u3a_browser_url_redirect_to', 10, 1);
//
//function u3a_browser_url_redirect_to($url)
//{
//	// your code here
//	$mbr = U3A_Information::u3a_logged_in_user();
//	write_log("old url", $url, ($mbr ? $mbr->id : "null"));
//	if ($mbr)
//	{
//		$url = U3A_Options_Values::get_option_value(U3A_Options::OPTION_CATEGORY_MEMBER, $mbr->id, "initial page", $url, true);
//		write_log("new url", $mbr->id, $url);
//	}
//	return $url;
//}


add_action('um_on_login_before_redirect', 'u3a_on_login_before_redirect', 10, 1);

function u3a_on_login_before_redirect($user_id)
{
	$mbr = U3A_Information::u3a_logged_in_user();
	write_log("on login", ($mbr ? $mbr->id : "null"));
	if ($mbr)
	{
		$url = U3A_Options_Values::get_option_value(U3A_Options::OPTION_CATEGORY_MEMBER, $mbr->id, "initial page", "/", true);
		write_log("new url", $mbr->id, $url);
		exit(wp_redirect($url));
	}
}
