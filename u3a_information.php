<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
require_once 'U3ADatabase.php';
require_once 'u3a_config.php';

class U3A_Information
{

	public static $u3a_dbname = DB_NAME;
//	public static $gallery = '[upg-attach type="image"]';
//	public static $docs = '[mdocs cat="grpdocs"]' . "\n" . "[mdocs_upload_btn]\n";
	public static $group_page_titles = [
		"Information"	 => 1,
		"Members"		 => 2,
		"Documents"		 => 3,
		"Gallery"		 => 4,
		"Manage"			 => 5,
		"Forum"			 => 6
	];
	public static $group_page_manage_titles = [
		"Manage Categories",
		"Manage Images",
		"Manage Documents",
		"Manage Permissions",
		"Manage Members",
		"Edit Group Details"
	];
	public static $application_form_required_fields = [ "join"		 => [
			'member-surname',
			'member-forename',
			'member-email',
			'member-house',
			'member-address1',
			'member-postcode'
		],
		"add"			 => [
			'member-surname',
			'member-forename',
			'member-house',
			'member-address1',
			'member-postcode'
		],
		"edit"		 => [
			'member-surname',
			'member-forename',
			'member-house',
			'member-address1',
			'member-postcode'
		],
		"selfedit"	 => [
			'member-surname',
			'member-forename',
			'member-email',
			'member-house',
			'member-address1',
			'member-postcode'
		]
	];

	public static function u3a_name_lc()
	{
		return strtolower(substr(self::$u3a_dbname, 0, -4));
	}

	public static function u3a_name_uc()
	{
		return strtoupper(self::u3a_name_lc());
	}

	public static function u3a_name()
	{
		return ucfirst(self::u3a_name_lc());
	}

	public static function u3a_blogname()
	{
		return get_option("blogname", "U3A");
	}

	public static function get_temp_dir()
	{
		$tmpdir = trailingslashit(trailingslashit(dirname(__FILE__)) . "tmp");
		if (!file_exists($tmpdir))
		{
			mkdir($tmpdir);
		}
		return $tmpdir;
	}

	public static function get_slideshow_dir()
	{
		$ssdir = trailingslashit(trailingslashit(dirname(__FILE__)) . "slideshows");
		if (!file_exists($ssdir))
		{
			mkdir($ssdir);
		}
		return $ssdir;
	}

	public static function get_temp_url()
	{
		$tmpdir = plugin_dir_url(__FILE__) . "tmp/";
		return $tmpdir;
	}

	public static function get_slideshow_url()
	{
		$tmpdir = plugin_dir_url(__FILE__) . "slideshows/";
		return $tmpdir;
	}

	public static function get_group_page_active_tab($title)
	{
		$ret = 1;
		if ($title && array_key_exists($title, self::$group_page_titles))
		{
			$ret = self::$group_page_titles[$title];
		}
		return $ret;
	}

	public static function get_manage_open_spoiler($title, $open1)
	{
		$ret = "no";
		if ($title === $open1)
		{
			$ret = "yes";
		}
		return '[su_spoiler title="' . $title . '" style="fabric" icon="arrow-circle-1" open="' . $ret . '"]';
	}

	public static function u3a_help_page()
	{
		$groups_page = get_page_by_title("help documents");
		if (!$groups_page)
		{
			$pageguid = site_url() . "helpdocs";
			$newgrp = [
				"post_type"			 => "page",
				"post_title"		 => "help documents",
				"post_status"		 => 'publish',
				"post_content"		 => '[u3a_help_page]',
				'post_name'			 => "helpdocs",
				'comment_status'	 => 'closed',
				'ping_status'		 => 'closed',
				'post_author'		 => 1,
				'menu_order'		 => 0,
				'guid'				 => $pageguid
			];
			$pgid = wp_insert_post($newgrp);
			$groups_page = get_page_by_title("help documents");
		}
		return $groups_page->ID;
	}

	public static function u3a_help_videos_page()
	{
		$groups_page = get_page_by_title("help videos");
		if (!$groups_page)
		{
			$pageguid = site_url() . "helpvids";
			$newgrp = [
				"post_type"			 => "page",
				"post_title"		 => "help videos",
				"post_status"		 => 'publish',
				"post_content"		 => '[u3a_help_videos_page]',
				'post_name'			 => "helpvids",
				'comment_status'	 => 'closed',
				'ping_status'		 => 'closed',
				'post_author'		 => 1,
				'menu_order'		 => 0,
				'guid'				 => $pageguid
			];
			$pgid = wp_insert_post($newgrp);
			$groups_page = get_page_by_title("help videos");
		}
		return $groups_page->ID;
	}

	public static function u3a_contact_page()
	{
		$groups_page = get_page_by_title("contact");
		if (!$groups_page)
		{
			$pageguid = site_url() . "contact";
			$newgrp = [
				"post_type"			 => "page",
				"post_title"		 => "contact",
				"post_status"		 => 'publish',
				"post_content"		 => '[u3a_contact_page]',
				'post_name'			 => "contact",
				'comment_status'	 => 'closed',
				'ping_status'		 => 'closed',
				'post_author'		 => 1,
				'menu_order'		 => 0,
				'guid'				 => $pageguid
			];
			$pgid = wp_insert_post($newgrp);
			$groups_page = get_page_by_title("contact");
		}
		return $groups_page->ID;
	}

	public static function u3a_groups_page()
	{
		$groups_page = get_page_by_title("groups");
		if (!$groups_page)
		{
			$pageguid = site_url() . "groups";
			$newgrp = [
				"post_type"			 => "page",
				"post_title"		 => "groups",
				"post_status"		 => 'publish',
				"post_content"		 => '[u3a_groups_page]',
				"post_category"	 => [U3A_Information::u3a_group_category()],
				'post_name'			 => "groups",
				'comment_status'	 => 'closed',
				'ping_status'		 => 'closed',
				'post_author'		 => 1,
				'menu_order'		 => 0,
				'guid'				 => $pageguid
			];
			$pgid = wp_insert_post($newgrp);
			$groups_page = get_page_by_title("groups");
		}
		return $groups_page->ID;
	}

	public static function u3a_newsletter_page()
	{
		$page = get_page_by_title("newsletter");
		if (!$page)
		{
			$pageguid = site_url() . "newsletter";
			$newgrp = [
				"post_type"			 => "page",
				"post_title"		 => "newsletter",
				"post_status"		 => 'publish',
				"post_content"		 => '[u3a_newsletter_page]',
				'post_name'			 => "newsletter",
				'comment_status'	 => 'closed',
				'ping_status'		 => 'closed',
				'post_author'		 => 1,
				'menu_order'		 => 0,
				'guid'				 => $pageguid
			];
			$pgid = wp_insert_post($newgrp);
			$page = get_page_by_title("newsletter");
		}
		return $page->ID;
	}

//	public static function u3a_contact_page()
//	{
//		$page = get_page_by_title("contact");
//		if (!$page)
//		{
//			$pageguid = site_url() . "contact";
//			$newgrp = [
//				"post_type"			 => "page",
//				"post_title"		 => "newsletter",
//				"post_status"		 => 'publish',
//				"post_content"		 => '[u3a_contact_page]',
//				'post_name'			 => "contact",
//				'comment_status'	 => 'closed',
//				'ping_status'		 => 'closed',
//				'post_author'		 => 1,
//				'menu_order'		 => 0,
//				'guid'				 => $pageguid
//			];
//			$pgid = wp_insert_post($newgrp);
//			$page = get_page_by_title("contact");
//		}
//		return $page->ID;
//	}

	public static function u3a_meetings_page()
	{
		$page = get_page_by_title("meetings");
		if (!$page)
		{
			$pageguid = site_url() . "meetings";
			$newgrp = [
				"post_type"			 => "page",
				"post_title"		 => "meetings",
				"post_status"		 => 'publish',
				"post_content"		 => '[u3a_meetings]',
				'post_name'			 => "meetings",
				'comment_status'	 => 'closed',
				'ping_status'		 => 'closed',
				'post_author'		 => 1,
				'menu_order'		 => 0,
				'guid'				 => $pageguid
			];
			$pgid = wp_insert_post($newgrp);
			$page = get_page_by_title("meetings");
		}
		return $page->ID;
	}

	public static function u3a_join_page()
	{
		$page = get_page_by_title("membership");
		if (!$page)
		{
			$pageguid = site_url() . "membership";
			$newgrp = [
				"post_type"			 => "page",
				"post_title"		 => "membership",
				"post_status"		 => 'publish',
				"post_content"		 => '[u3a_join]',
				'post_name'			 => "membership",
				'comment_status'	 => 'closed',
				'ping_status'		 => 'closed',
				'post_author'		 => 1,
				'menu_order'		 => 0,
				'guid'				 => $pageguid
			];
			$pgid = wp_insert_post($newgrp);
			$page = get_page_by_title("membership");
		}
		return $page->ID;
	}

	public static function u3a_committee_page()
	{
		$page = get_page_by_title("committee");
		if (!$page)
		{
			$pageguid = site_url() . "committee";
			$newgrp = [
				"post_type"			 => "page",
				"post_title"		 => "committee",
				"post_status"		 => 'publish',
				"post_content"		 => '[u3a_committee_members email="yes"]',
				'post_name'			 => "committee",
				'comment_status'	 => 'closed',
				'ping_status'		 => 'closed',
				'post_author'		 => 1,
				'menu_order'		 => 0,
				'guid'				 => $pageguid
			];
			$pgid = wp_insert_post($newgrp);
			$page = get_page_by_title("committee");
		}
		return $page->ID;
	}

	public static function u3a_coordinators_page()
	{
		$page = get_page_by_title("coordinators");
		if (!$page)
		{
			$pageguid = site_url() . "coordinators";
			$newgrp = [
				"post_type"			 => "page",
				"post_title"		 => "coordinators",
				"post_status"		 => 'publish',
				"post_content"		 => '[u3a_coordinators]',
				'post_name'			 => "coordinators",
				'comment_status'	 => 'closed',
				'ping_status'		 => 'closed',
				'post_author'		 => 1,
				'menu_order'		 => 0,
				'guid'				 => $pageguid
			];
			$pgid = wp_insert_post($newgrp);
			$page = get_page_by_title("coordinators");
		}
		return $page->ID;
	}

	public static function u3a_allmembers_page()
	{
		$page = get_page_by_title("membership");
		if (!$page)
		{
			$pageguid = site_url() . "allmembers";
			$newgrp = [
				"post_type"			 => "page",
				"post_title"		 => "membership",
				"post_status"		 => 'publish',
				"post_content"		 => '[u3a_allmembers]',
				'post_name'			 => "allmembers",
				'comment_status'	 => 'closed',
				'ping_status'		 => 'closed',
				'post_author'		 => 1,
				'menu_order'		 => 0,
				'guid'				 => $pageguid
			];
			$pgid = wp_insert_post($newgrp);
			$page = get_page_by_title("membership");
		}
		return $page->ID;
	}

	public static function u3a_application_page()
	{
		$page = get_page_by_title("Application Form");
		if (!$page)
		{
			$pageguid = site_url() . "application";
			$newgrp = [
				"post_type"			 => "page",
				"post_title"		 => "Application Form",
				"post_status"		 => 'publish',
				"post_content"		 => '[u3a_application]',
				'post_name'			 => "application",
				'comment_status'	 => 'closed',
				'ping_status'		 => 'closed',
				'post_author'		 => 1,
				'menu_order'		 => 0,
				'guid'				 => $pageguid
			];
			$pgid = wp_insert_post($newgrp);
			$page = get_page_by_title("Application Form");
		}
		return $page->ID;
	}

	public static function u3a_manage_groups_page()
	{
		$page = get_page_by_title("manage groups");
		if (!$page)
		{
			$pageguid = site_url() . "manage_groups";
			$newgrp = [
				"post_type"			 => "page",
				"post_title"		 => "manage groups",
				"post_status"		 => 'publish',
				"post_content"		 => '[u3a_manage_groups]',
				'post_name'			 => "manage_groups",
				'comment_status'	 => 'closed',
				'ping_status'		 => 'closed',
				'post_author'		 => 1,
				'menu_order'		 => 0,
				'guid'				 => $pageguid
			];
			$pgid = wp_insert_post($newgrp);
			$page = get_page_by_title("manage groups");
		}
		return $page->ID;
	}

	public static function u3a_manage_members_page()
	{
		$page = get_page_by_title("manage members");
		if (!$page)
		{
			$pageguid = site_url() . "manage_members";
			$memmng = [
				"post_type"			 => "page",
				"post_title"		 => "manage members",
				"post_status"		 => 'publish',
				"post_content"		 => '[u3a_manage_members]',
				'post_name'			 => "manage_members",
				'comment_status'	 => 'closed',
				'ping_status'		 => 'closed',
				'post_author'		 => 1,
				'menu_order'		 => 0,
				'guid'				 => $pageguid
			];
			$pgid = wp_insert_post($memmng);
			$page = get_page_by_title("manage members");
		}
		return $page->ID;
	}

	public static function u3a_manage_committee_documents_page()
	{
		$page = get_page_by_title("manage committee documents");
		if (!$page)
		{
			$committee_page_id = self::u3a_committee_page();
			$pageguid = site_url() . "manage_committee_documents";
			$cdmng = [
				"post_type"			 => "page",
				"post_title"		 => "manage committee documents",
				"post_status"		 => 'publish',
				"post_content"		 => '[u3a_manage_committee_documents committee="' . $committee_page_id . '"]',
				'post_name'			 => "manage_committee_documents",
				'comment_status'	 => 'closed',
				'ping_status'		 => 'closed',
				'post_author'		 => 1,
				'menu_order'		 => 0,
				'guid'				 => $pageguid
			];
			$pgid = wp_insert_post($cdmng);
			$page = get_page_by_title("manage committee documents");
		}
		return $page->ID;
	}

	public static function u3a_manage_committee_permissions_page()
	{
		$page = get_page_by_title("manage committee permissions");
		if (!$page)
		{
			$committe_page_id = self::u3a_committee_page();
			$pageguid = site_url() . "manage_committee_permissions";
			$cdmng = [
				"post_type"			 => "page",
				"post_title"		 => "manage committee permissions",
				"post_status"		 => 'publish',
				"post_content"		 => '[u3a_manage_permissions group="0" committee="1"]',
				'post_name'			 => "manage_committee_permissions",
				'comment_status'	 => 'closed',
				'ping_status'		 => 'closed',
				'post_author'		 => 1,
				'menu_order'		 => 0,
				'guid'				 => $pageguid
			];
			$pgid = wp_insert_post($cdmng);
			$page = get_page_by_title("manage committee permissions");
		}
		return $page->ID;
	}

	public static function u3a_videos_page()
	{
		$page = get_page_by_title("videos");
		if (!$page)
		{
			$pageguid = site_url() . "videos";
			$newgrp = [
				"post_type"			 => "page",
				"post_title"		 => "videos",
				"post_status"		 => 'publish',
				"post_content"		 => '[u3a_videos]',
				'post_name'			 => "videos",
				'comment_status'	 => 'closed',
				'ping_status'		 => 'closed',
				'post_author'		 => 1,
				'menu_order'		 => 0,
				'guid'				 => $pageguid
			];
			$pgid = wp_insert_post($newgrp);
			$page = get_page_by_title("videos");
		}
		return $page->ID;
	}

	public static function u3a_administration_page()
	{
		$page = get_page_by_title("administration");
		if (!$page)
		{
			$pageguid = site_url() . "administration";
			$newgrp = [
				"post_type"			 => "page",
				"post_title"		 => "administration",
				"post_status"		 => 'publish',
				"post_content"		 => '[u3a_administration]',
				'post_name'			 => "administration",
				'comment_status'	 => 'closed',
				'ping_status'		 => 'closed',
				'post_author'		 => 1,
				'menu_order'		 => 0,
				'guid'				 => $pageguid
			];
			$pgid = wp_insert_post($newgrp);
			$page = get_page_by_title("administration");
		}
		return $page->ID;
	}

	public static function u3a_group_category()
	{
		return get_cat_ID("group");
	}

	public static function u3a_group_page_category()
	{
		return get_cat_ID("group page");
	}

	public static function u3a_group_page($grp, $create = false)
	{
//		write_log("group page for " . $grp->name);
// create it
		$slug = sanitize_title($grp->name);
		$pageguid = site_url() . $slug;
// check if page exists
		$pg = get_page_by_title($grp->name);
		$pgid = 0;
		if ($pg)
		{
			$pgid = $pg->ID;
			$grppg["ID"] = $pgid;
			if ($create)
			{
				$grppg = [
					'ID'				 => $pgid,
					'post_content'	 => '[u3a_group_page group="' . $grp->id . '"]'
				];
				wp_update_post($grppg);
			}
		}
		else
		{
			$grppg = [
				'post_title'		 => $grp->name,
				'post_type'			 => 'page',
				'post_name'			 => $slug,
				'post_content'		 => '[u3a_group_page group="' . $grp->id . '"]',
				'post_status'		 => 'publish',
				'comment_status'	 => 'closed',
				'ping_status'		 => 'closed',
				'post_author'		 => 1,
				'menu_order'		 => 0,
				'guid'				 => $pageguid
			];
			$pgid = wp_insert_post($grppg, FALSE);
//			write_log("creating page for " . $grp->name);
		}
		return $pgid;
	}

	public static function u3a_group_pages($grps, $create = false)
	{
		$group_page_ids = [];
		foreach ($grps as $grp)
		{
			$group_page_ids[$grp->name] = self::u3a_group_page($grp, $create);
		}
		return $group_page_ids;
	}

	public static function u3a_payment_return_page()
	{
		$page = get_page_by_title("Payment Return");
		if (!$page)
		{
			$pageguid = site_url() . "payment-return";
			$newpg = [
				"post_type"			 => "page",
				"post_title"		 => "Payment Return",
				"post_status"		 => 'publish',
				"post_content"		 => '[u3a_payment_return]',
				'post_name'			 => "payment-return",
				'comment_status'	 => 'closed',
				'ping_status'		 => 'closed',
				'post_author'		 => 1,
				'menu_order'		 => 0,
				'guid'				 => $pageguid
			];
			$pgid = wp_insert_post($newpg);
			$page = get_page_by_title("Payment Return");
		}
		return $page->ID;
	}

	public static function u3a_payment_void_page()
	{
		$page = get_page_by_title("Payment Void");
		if (!$page)
		{
			$pageguid = site_url() . "payment-voided";
			$newpg = [
				"post_type"			 => "page",
				"post_title"		 => "Payment Void",
				"post_status"		 => 'publish',
				"post_content"		 => '[u3a_payment_void]',
				'post_name'			 => "payment-voided",
				'comment_status'	 => 'closed',
				'ping_status'		 => 'closed',
				'post_author'		 => 1,
				'menu_order'		 => 0,
				'guid'				 => $pageguid
			];
			$pgid = wp_insert_post($newpg);
			$page = get_page_by_title("Payment Void");
		}
		return $page->ID;
	}

	public static function u3a_payment_complete_page()
	{
		$page = get_page_by_title("Payment Complete");
		if (!$page)
		{
			$pageguid = site_url() . "payment-completed";
			$newpg = [
				"post_type"			 => "page",
				"post_title"		 => "Payment Complete",
				"post_status"		 => 'publish',
				"post_content"		 => '[u3a_payment_complete]',
				'post_name'			 => "payment-completed",
				'comment_status'	 => 'closed',
				'ping_status'		 => 'closed',
				'post_author'		 => 1,
				'menu_order'		 => 0,
				'guid'				 => $pageguid
			];
			$pgid = wp_insert_post($newpg);
			$page = get_page_by_title("Payment Complete");
		}
		return $page->ID;
	}

	public static function u3a_links_page()
	{
		$page = get_page_by_title("Links");
		if (!$page)
		{
			$pageguid = site_url() . "links";
			$div = new U3A_DIV('[u3a_links group="0" member="0"]', null, "u3a-links-page u3a-links-div");
			$newpg = [
				"post_type"			 => "page",
				"post_title"		 => "Links",
				"post_status"		 => 'publish',
				"post_content"		 => $div->to_html(),
				'post_name'			 => "links",
				'comment_status'	 => 'closed',
				'ping_status'		 => 'closed',
				'post_author'		 => 1,
				'menu_order'		 => 0,
				'guid'				 => $pageguid
			];
			$pgid = wp_insert_post($newpg);
			$page = get_page_by_title("Links");
		}
		return $page->ID;
	}

	public static function u3a_members_display_page()
	{
		$page = get_page_by_title("Members Display");
		if (!$page)
		{
			$pageguid = site_url() . "members-display";
			$newpg = [
				"post_type"			 => "page",
				"post_title"		 => "Members Display",
				"post_status"		 => 'publish',
				"post_content"		 => '[u3a_display_members_table]',
				'post_name'			 => "members-display",
				'comment_status'	 => 'closed',
				'ping_status'		 => 'closed',
				'post_author'		 => 1,
				'menu_order'		 => 0,
				'guid'				 => $pageguid
			];
			$pgid = wp_insert_post($newpg);
			$page = get_page_by_title("Members Display");
		}
		return $page->ID;
	}

	public static function u3a_members_personal_page()
	{
		$title = "Personal Page";
		$name = "members-personal";
		$page = get_page_by_title($title);
		if (!$page)
		{
			$pageguid = site_url() . $name;
			$newpg = [
				"post_type"			 => "page",
				"post_title"		 => "Personal Page",
				"post_status"		 => 'publish',
				"post_content"		 => '[u3a_members_personal]',
				'post_name'			 => $name,
				'comment_status'	 => 'closed',
				'ping_status'		 => 'closed',
				'post_author'		 => 1,
				'menu_order'		 => 0,
				'guid'				 => $pageguid
			];
			$pgid = wp_insert_post($newpg);
			$page = get_page_by_title($title);
		}
		return $page->ID;
	}

	public static function get_page_url_from_title($title)
	{
		$ret = null;
		$pg = get_page_by_title($title);
		if ($pg)
		{
			$ret = $pg->guid;
		}
		return $ret;
	}

	public static function u3a_user_from_id($user_id)
	{
		$current_wp_user = new WP_User($user_id);
		$current_user = null;
		if ($current_wp_user && $current_wp_user->ID)
		{
			$lg = $current_wp_user->user_login;
			if (is_numeric($lg))
			{
				$current_user = U3A_Row::load_single_object("U3A_Members", ["membership_number" => $lg, "status" => "Current"]);
			}
			else
			{
				$cttee = U3A_Row::load_single_object("U3A_Committee", ["login" => $lg]);
				if ($cttee)
				{
					$current_user = U3A_Row::load_single_object("U3A_Members", ["id" => $cttee->members_id]);
				}
			}
		}
//		write_log("current user ");
//		write_log($current_user);
		return $current_user;
	}

	public static function u3a_logged_in_user()
	{
		return U3A_Database_Row::u3a_logged_in_member();
	}

	public static function u3a_real_user()
	{
		return U3A_Database_Row::u3a_real_member();
	}

	public static function u3a_image_upload_button_parameters()
	{
		return [
			"by"		 => "photographer",
			"accept"	 => ".png,.jpg,.jpeg,image/png,image/jpeg"
		];
	}

	public static function u3a_document_upload_button_parameters()
	{
		return [
			"by"		 => "author",
			"accept"	 => ".pdf,.doc,.docx,.epub,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,application/epub+zip,application/pdf,application/vnd.oasis.opendocument.text"
		];
	}

	public static function not_implemented($qualifier)
	{
		$h = new U3A_H(3, "not " . $qualifier . " implemented");
		return $h->to_html();
	}

	public static function not_available($functionality)
	{
		return new U3A_DIV($functionality . " not yet available");
	}

	public static function u3a_management_enabled()
	{
		$usr = self::u3a_logged_in_user();
		$me = get_option("enable_management");
		return ((U3A_Committee::is_committee_member($usr) && ($me === "cm")) || (U3A_Committee::is_webmanager($usr) && ($me === "wm")) || $me === "yes") ? 1 : 0;
	}

	public static function u3a_has_permission($member, $permit, $grp = 0)
	{
		// webmanager can do anything
		if (U3A_Committee::is_webmanager($member))
		{
			$ret = true;
		}
		else
		{
			$ret = false;
			$groups_id = $grp ? U3A_Groups::get_group_id($grp) : 0;
			if ($groups_id)
			{
				// it is a group permission
				$type = U3A_Permission_Types::GROUP_TYPE;
				$members_id = U3A_Members::get_member_id($member);
				// coordinators automatically have permission
				if (U3A_Group_Members::is_coordinator($members_id, $groups_id) || U3A_Committee::is_groups_administrator($member))
				{
					$ret = true;
				}
				else
				{
					// check there is such a permission
					$permissions_types_id = U3A_Permission_Types::get_permission_types_id($permit, U3A_Permission_Types::GROUP_TYPE, self::u3a_management_enabled());
					if ($permissions_types_id)
					{
//						write_log("check as member");
						// check permission as member
						$ret = U3A_Row::has_rows("U3A_Permissions", ["groups_id" => $groups_id, "members_id" => $members_id, "permission_types_id" => $permissions_types_id]);
						if (!$ret)
						{
							// check for everyone permission
							$ret = U3A_Row::has_rows("U3A_Permissions", ["groups_id" => $groups_id, "members_id" => 0, "permission_types_id" => $permissions_types_id]);
						}
						if (!$ret)
						{
//							write_log("check as committee");
//							write_log(U3A_Committee::get_committee_id($members_id));
							// check permission as committee member
							$committee_id = U3A_Committee::get_committee_ids_for_member($members_id);
//							write_log($committee_id);
							if ($committee_id)
							{
								$ret = U3A_Row::has_rows("U3A_Permissions", ["groups_id" => $groups_id, "committee_id" => $committee_id, "permission_types_id" => $permissions_types_id]);
							}
						}
					}
				}
			}
			else
			{
				// check there is such a permission
				$permissions_types_id = U3A_Permission_Types::get_permission_types_id($permit, U3A_Permission_Types::COMMITTEE_TYPE, self::u3a_management_enabled());
				if ($permissions_types_id)
				{
					$members_id = U3A_Members::get_member_id($member);
					// check permission as committee member
					$committee_id = U3A_Committee::get_committee_ids_for_member($members_id);
//					write_log($committee_id);
//					$committee_id = U3A_Committee::get_committee_id($member);
					if ($committee_id)
					{
						$ret = U3A_Row::has_rows("U3A_Permissions", ["groups_id" => $groups_id, "committee_id" => $committee_id, "permission_types_id" => $permissions_types_id]);
					}
					{
						$roles_id = U3A_Roles::get_roles_ids_for_member($members_id);
						if ($roles_id)
						{
							$ret = U3A_Row::has_rows("U3A_Permissions", ["groups_id" => $groups_id, "roles_id" => $roles_id, "permission_types_id" => $permissions_types_id]);
						}
					}
				}
			}
		}
		return $ret;
	}

	public static function u3a_application_form_is_required($name, $op)
	{
		if (array_key_exists($op, self::$application_form_required_fields))
		{
			$ret = array_search($name, self::$application_form_required_fields[$op]) !== FALSE;
		}
		else
		{
			$ret = false;
		}
		return $ret;
	}

	//"F jS Y"
	public static function u3a_get_subscriptions_due_this_year($date_format = null)
	{
//		$cfg = U3A_CONFIG::get_the_config();
//		$this_year = U3A_Timestamp_Utilities::year();
//		return $this_year . '-' . $cfg->SUBSCRIPTIONS_DUE;
		return U3A_CONFIG::u3a_get_formatted_date("SUBSCRIPTIONS_DUE", $date_format, 0);
	}

	public static function u3a_get_subscriptions_due_next_year($date_format = null)
	{
//		$cfg = U3A_CONFIG::get_the_config();
//		$this_year = U3A_Timestamp_Utilities::year();
//		$next_year = $this_year + 1;
//		return $next_year . '-' . $cfg->SUBSCRIPTIONS_DUE;
		return U3A_CONFIG::u3a_get_formatted_date("SUBSCRIPTIONS_DUE", $date_format, 1);
	}

	public static function u3a_get_membership_lapses_this_year($date_format = null)
	{
//		$cfg = U3A_CONFIG::get_the_config();
//		$this_year = U3A_Timestamp_Utilities::year();
//		return $this_year . '-' . $cfg->MEMBERSHIP_LAPSES;
		return U3A_CONFIG::u3a_get_formatted_date("MEMBERSHIP_LAPSES", $date_format, 0);
	}

	public static function u3a_get_membership_lapses_next_year($date_format = null)
	{
//		$cfg = U3A_CONFIG::get_the_config();
//		$this_year = U3A_Timestamp_Utilities::year();
//		$next_year = $this_year + 1;
//		return $next_year . '-' . $cfg->MEMBERSHIP_LAPSES;
		return U3A_CONFIG::u3a_get_formatted_date("MEMBERSHIP_LAPSES", $date_format, 1);
	}

	public static function u3a_get_membership_lapses($renew)
	{
		$yr = U3A_Timestamp_Utilities::year($renew);
		$ml = U3A_CONFIG::u3a_get_as_timestamp("MEMBERSHIP_LAPSES", $yr);
		if ($ml < $renew)
		{
			$ml = U3A_CONFIG::u3a_get_as_timestamp($config_value, $yr + 1);
		}
		return $ml;
	}

	public static function u3a_get_reduced_rate_starts_this_year($date_format = null)
	{
//		$cfg = U3A_CONFIG::get_the_config();
//		$this_year = U3A_Timestamp_Utilities::year();
//		return $this_year . '-' . $cfg->REDUCED_FROM;
		return U3A_CONFIG::u3a_get_formatted_date("REDUCED_FROM", $date_format, 0);
	}

	public static function u3a_get_reduced_rate_starts_next_year($date_format = null)
	{
		return U3A_CONFIG::u3a_get_formatted_date("REDUCED_FROM", $date_format, 1);
	}

	public static function u3a_get_renewals_from_this_year($date_format = null)
	{
		return U3A_CONFIG::u3a_get_formatted_date("RENEWALS_FROM", $date_format, 0);
	}

	public static function u3a_get_renewals_from_next_year($date_format = null)
	{
		return U3A_CONFIG::u3a_get_formatted_date("RENEWALS_FROM", $date_format, 1);
	}

	public static function u3a_get_renewals_from_year_after_next($date_format = null)
	{
		return U3A_CONFIG::u3a_get_formatted_date("RENEWALS_FROM", $date_format, 2);
	}

	public static function u3a_get_renewal_rate($associate = false)
	{
		$cfg = U3A_CONFIG::get_the_config();
		return $associate ? $cfg->ASSOCIATE_MEMBERSHIP_RATE : $cfg->MEMBERSHIP_RATE;
	}

	public static function u3a_get_reduced_rate($associate = false)
	{
		$cfg = U3A_CONFIG::get_the_config();
		return $associate ? $cfg->ASSOCIATE_REDUCED_RATE : $cfg->REDUCED_RATE;
	}

	public static function u3a_is_reduced_rate()
	{
		$now = time();
		$rrs = strtotime(self::u3a_get_reduced_rate_starts_this_year());
		return $now > $rrs;
	}

	public static function u3a_get_current_join_rate($associate = false)
	{
		return self::u3a_is_reduced_rate() ? self::u3a_get_reduced_rate($associate) : self::u3a_get_renewal_rate($associate);
	}

	public static function u3a_has_lapsed()
	{
		$now = time();
		$ml = strtotime(self::u3a_get_membership_lapses_this_year());
		return $now > $mm;
	}

	public static function u3a_get_u3a_name()
	{
		$cfg = U3A_CONFIG::get_the_config();
		return $cfg->U3ANAME;
	}

	public static function u3a_get_mailing_list_domain()
	{
		$cfg = U3A_CONFIG::get_the_config();
		return $cfg->MAILING_LIST_DOMAIN;
	}

	public static function u3a_get_domain_name()
	{
		$cfg = U3A_CONFIG::get_the_config();
		return $cfg->DOMAIN_NAME;
	}

	public static function u3a_get_paypal()
	{
		$cfg = U3A_CONFIG::get_the_config();
		return $cfg->PAYPAL;
	}

	public static function u3a_is_live_server()
	{
		return self::u3a_get_domain_name() === $_SERVER["SERVER_NAME"];
	}

}
