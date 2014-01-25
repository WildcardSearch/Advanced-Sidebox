<?php
/*
 * Plug-in Name: Advanced Sidebox for MyBB 1.6.x
 * Copyright 2013 WildcardSearch
 * http://www.rantcentralforums.com
 *
 * contains language used on both forum and ACP side by add-on modules
 */

$l['asb_addon'] = 'Add-on';

// default settings
$l['asb_xmlhttp_on_title'] = 'AJAX Update?';
$l['asb_xmlhttp_on_description'] = 'time (in seconds) between updates (0 to disable AJAX)';

$l['asb_forum_show_list_title'] = 'Forum Show List';
$l['asb_forum_show_list_desc'] = '(optional) add a forum id or a comma-separated list of fids to be draws threads from [can be amended by Thread Show List and Thread Hide List]';

$l['asb_forum_hide_list_title'] = 'Forum Hide List';
$l['asb_forum_hide_list_desc'] = '(optional) add a forum id or a comma-separated list of fids to be <strong>excluded/hidden</strong> [can be amended by Thread Show List and Thread Hide List]';

$l['asb_thread_show_list_title'] = 'Thread Show List';
$l['asb_thread_show_list_desc'] = '(optional) add a thread id or a comma-separated list of tids to draw posts from [If Forum Show List or Forum Hide List are in use, this settings will not override, but can amend]';

$l['asb_thread_hide_list_title'] = 'Thread Hide List';
$l['asb_thread_hide_list_desc'] = '(optional) add a thread id or a comma-separated list of tids to be <strong>excluded/hidden</strong> [If Forum Show List or Forum Hide List are in use, this settings <strong>will override and amend</strong>]';

// examples
$l['asb_example1_title'] = '[Example 1] Simplest Box';
$l['asb_example1_desc'] = 'As simple as it gets. This box illustrates the easiest way to create an add-on module';

$l['asb_example2_title'] = '[Example 2] Using Templates';
$l['asb_example2_desc'] = 'A simple box to illustrate using templates to produce content';

$l['asb_example3_title'] = '[Example 3] Using Settings';
$l['asb_example3_desc'] = 'A simple box to illustrate using settings to control content';
$l['asb_example3_setting_anouncement_text'] = 'Announcement Text';
$l['asb_example3_setting_anouncement_text_desc'] = 'this text will be styled and displayed';
$l['asb_example3_info'] = 'Custom text can be used by entering a value for the Announcement Text.';

// welcome
$l['asb_welcome'] = 'Welcome';
$l['asb_welcome_desc'] = 'Login for guest, info for member';

$l['asb_welcome_member_welcome_lastvisit'] = 'Last visit';
$l['asb_welcome_new_announcement'] = '1 new announcement';
$l['asb_welcome_new_announcements'] = '{1} new announcements';
$l['asb_welcome_new_thread'] = '1 new thread';
$l['asb_welcome_new_threads'] = '{1} new threads';
$l['asb_welcome_new_post'] = '1 new post';
$l['asb_welcome_new_posts'] = '{1} new posts';
$l['asb_welcome_view_new'] = 'View New Posts';
$l['asb_welcome_view_todays'] = "View Today's Posts";
$l['asb_welcome_guest_welcome_registration'] = 'You have to <a href="{1}">register</a> before you can post on our site.';
$l['asb_welcome_password'] = 'Password';
$l['asb_welcome_remember_me'] = 'Remember Me';

// search
$l['asb_search'] = 'Search';
$l['asb_search_desc'] = 'Simple options, a keyword text box and a go button';

$l['asb_search_in'] = 'Search In';
$l['asb_search_messages'] = 'Messages';
$l['asb_search_results_as'] = 'Results As';
$l['asb_search_posts'] = 'Posts';
$l['asb_search_threads'] = 'Threads';
$l['asb_search_keywords'] = 'Keywords';
$l['asb_search_advanced_search'] = 'Advanced Search';

// statistics
$l['asb_stats'] = 'Statistics';
$l['asb_stats_desc'] = 'Forum statistics and figures';

$l['asb_stats_box'] = 'Board Statistics';
$l['asb_stats_format_usernames_title'] = 'Format user names?';
$l['asb_stats_format_usernames_desc'] = '(may use another query)';

$l['asb_stats_num_members'] = 'Members';
$l['asb_stats_latest_member'] = 'Latest member';
$l['asb_stats_num_threads'] = 'Forum threads';
$l['asb_stats_num_posts'] = 'Forum posts';
$l['asb_stats_full_stats'] = 'Full Statistics';
$l['asb_stats_no_one'] = 'no-one';

// wol
$l['asb_wol'] = "Who's Online";
$l['asb_wol_desc'] = "Currently online members' avatars";

$l['asb_show_avatars_title'] = 'Show Avatars';
$l['asb_show_avatars_desc'] = 'YES to show an avatar list, NO to show a list of formatted user names';

$l['asb_wol_num_avatars_per_row_title'] = 'Number of Avatars Per Row';
$l['asb_wol_num_avatars_per_row_desc'] = '(enter number of columns in which avatars will be shown within the module, or 0 to disable avatars)';

$l['asb_wol_avatar_max_rows_title'] = 'Maximum Number of Avatar Rows';
$l['asb_wol_avatar_max_rows_desc'] = '(enter the maximum amount of rows that avatars may occupy, or 0 to disable avatars)';

$l['asb_wol_avatar_maintain_aspect_title'] = 'Maintain Avatar Aspect Ratio?';
$l['asb_wol_avatar_maintain_aspect_desc'] = 'YES to alter only the width of the avatar, maintaing the aspect ratio or NO (default) to force the avatars to be square';

$l['asb_wol_online_users'] = 'There are currently <b>{1}</b> online users.';
$l['asb_wol_online_user'] = 'There is currently 1 user online';
$l['asb_wol_online_counts'] = '<b>{1}</b> Member(s) | <b>{2}</b> Guest(s)';

$l['asb_wol_no_one_online'] = 'There are currently no members online.';
$l['asb_wol_avatar'] = 'Avatar';
$l['asb_wol_avatar_lc'] = 'avatar';
$l['asb_wol_profile'] = 'profile';
$l['asb_wol_see_all_alt'] = 'See all...';
$l['asb_wol_see_all_title'] = 'Click to see all online members.';

// latest threads
$l['asb_latest_threads'] = 'Latest Threads';
$l['asb_latest_threads_desc'] = 'Lists the latest forum threads';

$l['asb_latest_threads_lastpost'] = 'Last Post';
$l['asb_latest_threads_replies'] = 'Replies:';
$l['asb_latest_threads_views'] = 'Views:';
$l['asb_latest_threads_no_threads'] = 'There are no threads to display.';
$l['asb_latest_threads'] = 'Latest Threads';
$l['asb_gotounread'] = 'Go to first unread post';

// * settings
$l['asb_max_threads_title'] = 'Thread Limit';
$l['asb_max_threads_desc'] = 'maximal number of threads to display';

$l['asb_last_poster_avatar_title'] = 'Last Poster Avatar?';
$l['asb_last_poster_avatar_desc'] = 'YES to show the avatar of the last poster, NO to show formatted user name';

$l['asb_avatar_width_title'] = 'Avatar Width';
$l['asb_avatar_width_desc'] = 'width in pixels IF showing avatars';

//  recent posts
$l['asb_recent_posts'] = 'Recent Posts';
$l['asb_recent_posts_desc'] = 'Lists the latest posts';

$l['asb_recent_posts_max_title'] = 'Post Limit';
$l['asb_recent_posts_max_description'] = 'maximal number of posts to display';

$l['asb_recent_posts_max_length_title'] = 'Maximum Post Length';
$l['asb_recent_posts_max_length_description'] = 'maximum length of excerpt to show in characters';

$l['asb_recent_posts_no_posts'] = 'There are no posts to display.';

// pm's
$l['asb_private_messages'] = 'Private Messages';
$l['asb_private_messages_desc'] = "Lists the user's PM info";

$l['asb_pms_received_new'] = '{1}, you have <b>{2}</b> unread message(s).';
$l['asb_pms_received_new'] = '{1}, you have <b>{2}</b> unread message(s).';

$l['asb_pms_no_messages'] = 'Please {1} or {2} to use this functionality.';
$l['asb_pms_login'] = 'login';
$l['asb_pms_register'] = 'register';
$l['asb_pms_user_disabled_pms'] = 'You have disabled this functionality in {1}.';
$l['asb_pms_usercp'] = 'User CP';
$l['asb_pms_disabled_by_admin'] = "You don't have privileges to access this functionality, or it has been disabled by administrator. You may contact administrator for assistance.";
$l['asb_pms_unread'] = 'Unread Messages';
$l['asb_pms_total'] = 'Total Messages';

// random quotes
$l['asb_random_quotes'] = 'Random Quotes';
$l['asb_random_quotes_desc'] = 'Displays random quotes with a link and avatar';
$l['asb_random_quotes_no_posts'] = 'There are no posts to display.';

$l['asb_random_quotes_read_more'] = 'Read More';
$l['asb_random_quotes_read_more_title'] = 'Click to see the entire post';
$l['asb_random_quotes_read_more_threadlink_title'] = 'Click to see the entire thread';

$l['asb_random_quotes_forums_title'] = 'Forum List';
$l['asb_random_quotes_forums_desc'] = 'single fid or comma-separated fid list of forums to pull random posts from';

$l['asb_thread_id_title'] = 'Thread List/ID';
$l['asb_thread_id_desc'] = 'single tid or comma-separated tid list of thread(s) to pull random posts from (this setting will override forum id/list above (if set)';

$l['asb_random_quotes_max_quote_length_title'] = 'Maximal Post Length';
$l['asb_random_quotes_max_quote_length'] = 'in characters';

$l['asb_random_quotes_min_quote_length_title'] = 'Minimal Post Length';
$l['asb_random_quotes_min_quote_length_desc'] = 'in characters';

$l['asb_random_quotes_fade_out_title'] = 'Gradually Fade Out Text Over Maximum?';
$l['asb_random_quotes_fade_out_desc'] = 'YES to fade NO to clip (. . .)';

$l['asb_random_quotes_default_text_title'] = 'Default Text';
$l['asb_random_quotes_default_text_desc'] = 'display this if the message is too short';

// staff online
$l['asb_staff_online'] = 'Online Staff';
$l['asb_staff_online_desc'] = 'Display online staff members list';

$l['asb_staff_online_max_staff_title'] = 'Staff Limit';
$l['asb_staff_online_max_staff_desc'] = 'maximal number of staff members to display';

$l['asb_staff_online_no_staff_online'] = 'There are no staff members currently online.';

?>
