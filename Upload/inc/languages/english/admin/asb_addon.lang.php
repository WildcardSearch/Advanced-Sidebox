<?php
/*
 * Plug-in Name: Advanced Sidebox for MyBB 1.8.x
 * Copyright 2014 WildcardSearch
 * https://www.rantcentralforums.com
 *
 * contains language used on both forum and ACP side by add-on modules
 */

$l['asb_addon'] = 'Addon';

$l['asb_recent_posts_ellipsis'] = $l['asb_recent_posts_title_ellipsis'] = $l['asb_latest_threads_ellipsis'] = '...';

$l['asb_sidebox_no_content'] = 'no content';

// default settings
$l['asb_xmlhttp_refresh_rate_title'] = 'AJAX Refresh Rate';
$l['asb_xmlhttp_refresh_rate_description'] = 'time (in seconds) between updates (0 to disable AJAX)';

$l['asb_xmlhttp_refresh_decay_title'] = 'AJAX Decay Rate';
$l['asb_xmlhttp_refresh_decay_description'] = 'the factor by which the refresh rate will increase if there is no new content (1=no rate increase)';

$l['asb_forum_show_list_title'] = 'Forum Show List';
$l['asb_forum_show_list_desc'] = '(optional) add a forum id or a comma-separated list of fids to be draws threads from [can be amended by Thread Show List and Thread Hide List]';

$l['asb_forum_hide_list_title'] = 'Forum Hide List';
$l['asb_forum_hide_list_desc'] = '(optional) add a forum id or a comma-separated list of fids to be <strong>excluded/hidden</strong> [can be amended by Thread Show List and Thread Hide List]';

$l['asb_thread_show_list_title'] = 'Thread Show List';
$l['asb_thread_show_list_desc'] = '(optional) add a thread id or a comma-separated list of tids to draw posts from [If Forum Show List or Forum Hide List are in use, this setting will not override, but can amend]';

$l['asb_thread_hide_list_title'] = 'Thread Hide List';
$l['asb_thread_hide_list_desc'] = '(optional) add a thread id or a comma-separated list of tids to be <strong>excluded/hidden</strong> [If Forum Show List or Forum Hide List are in use, this setting <strong>will override and amend</strong>]';

$l['asb_group_show_list_title'] = 'Usergroup Show List';
$l['asb_group_show_list_desc'] = '(optional) add a usergroup id or a comma-separated list of gids to show exclusively [If Usergroup Hide List is in use, this setting will not override, but can amend]';

$l['asb_group_hide_list_title'] = 'Usergroup Hide List';
$l['asb_group_hide_list_desc'] = '(optional) add a usergroup id or a comma-separated list of gids to be <strong>excluded/hidden</strong> [If Usergroup Show List is in use, this setting <strong>will override and amend</strong>]';

$l['asb_important_threads_only_title'] = 'Important Threads Only?';
$l['asb_important_threads_only_desc'] = "YES to only use 'stickied' threads, NO (default) to use any threads/posts";

$l['asb_load_xthreads_data_title'] = 'Load xThreads Data?';
$l['asb_load_xthreads_data_desc'] = "YES to load xThread data available for the fetched threads, NO (default) to save unnecessary DB calls.";

$l['asb_showinportal_threads_only_title'] = 'Show In Portal Threads Only?';
$l['asb_showinportal_threads_only_desc'] = "YES to only use 'show in portal' threads marked by the OUGC Show In Portal plugin, NO (default) to use any threads/posts.";

$l['asb_max_thread_title_length_title'] = 'Thread Title Length Limit';
$l['asb_max_thread_title_length_desc'] = 'enter nothing or 0 to show the full title, or enter the maximal number of characters to display (default 40)';

// welcome
$l['asb_welcome'] = 'Welcome';
$l['asb_welcome_desc'] = 'Displays a login form for guests or user information for members.';

$l['asb_welcome_member_welcome_lastvisit'] = 'Last visit';
$l['asb_welcome_new_announcement'] = '1 new announcement';
$l['asb_welcome_new_announcements'] = '{1} new announcements';
$l['asb_welcome_new_thread'] = '1 new thread';
$l['asb_welcome_new_threads'] = '{1} new threads';
$l['asb_welcome_new_post'] = '1 new post';
$l['asb_welcome_new_posts'] = '{1} new posts';
$l['asb_welcome_view_todays'] = "View Today's Posts";
$l['asb_welcome_guest_welcome_registration'] = 'You have to <a href="{1}">register</a> before you can post on our site.';

// search
$l['asb_search'] = 'Search';
$l['asb_search_desc'] = 'Displays simple search options, a keyword text box, and a submit button.';

$l['asb_search_in'] = 'Search In';
$l['asb_search_messages'] = 'Messages';
$l['asb_search_results_as'] = 'Results As';
$l['asb_search_posts'] = 'Posts';
$l['asb_search_threads'] = 'Threads';
$l['asb_search_keywords'] = 'Keywords';
$l['asb_search_advanced_search'] = 'Advanced Search';

// statistics
$l['asb_stats'] = 'Statistics';
$l['asb_stats_desc'] = 'Displays various forum statistics and figures.';

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
$l['asb_wol_desc'] = "Lists members who are currently online as names or avatars.";

$l['asb_show_avatars_title'] = 'Show Avatars';
$l['asb_show_avatars_desc'] = 'YES to show an avatar list, NO to show a list of formatted user names';

$l['asb_wol_num_avatars_per_row_title'] = 'Number of Avatars Per Row';
$l['asb_wol_num_avatars_per_row_desc'] = '(enter number of columns in which avatars will be shown within the module, or 0 to disable avatars)';

$l['asb_wol_avatar_max_rows_title'] = 'Maximum Number of Avatar Rows';
$l['asb_wol_avatar_max_rows_desc'] = '(enter the maximum amount of rows that avatars may occupy, or 0 to disable avatars)';

$l['asb_wol_asb_avatar_margin_title'] = 'Avatar Margin';
$l['asb_wol_asb_avatar_margin_desc'] = 'percentage of the width of the container';

$l['asb_wol_online_users'] = 'There are currently <b>{1}</b> online users.';
$l['asb_wol_online_user'] = 'There is currently 1 user online';
$l['asb_wol_complete_list'] = 'Complete List';
$l['asb_wol_online_counts'] = '<b>{1}</b> Member(s) | <b>{2}</b> Guest(s)';

$l['asb_wol_no_one_online'] = 'There are currently no members online.';
$l['asb_wol_avatar'] = 'Avatar';
$l['asb_wol_avatar_lc'] = 'avatar';
$l['asb_wol_profile'] = 'profile';
$l['asb_wol_see_all_alt'] = 'See all...';
$l['asb_wol_see_all_title'] = 'Click to see all online members.';

// latest threads
$l['asb_latest_threads'] = 'Latest Threads';
$l['asb_latest_threads_desc'] = 'Lists the latest forum threads.';

$l['asb_latest_threads_lastpost'] = 'Last Post';
$l['asb_latest_threads_replies'] = 'Replies:';
$l['asb_latest_threads_views'] = 'Views:';
$l['asb_latest_threads_no_threads'] = 'There are no threads to display.';
$l['asb_gotounread'] = 'Go to first unread post';

// * settings
$l['asb_max_threads_title'] = 'Thread Limit';
$l['asb_max_threads_desc'] = 'maximal number of threads to display';

$l['asb_last_poster_avatar_title'] = 'Last Poster Avatar?';
$l['asb_last_poster_avatar_desc'] = 'YES to show the avatar of the last poster, NO to show formatted user name';

$l['asb_avatar_width_title'] = 'Avatar Width';
$l['asb_avatar_width_desc'] = 'enter digits (ie. "40") to specifiy width in pixels, or digits followed by "%" (ie. "40%") to specifiy width in percentage of the side box width &mdash; this settings has no effect unless Last Poster Avatar is set to YES';

$l['asb_new_threads_only_title'] = 'New Threads Only?';
$l['asb_new_threads_only_desc'] = 'enter nothing or 0 to show all threads with new posts (default), or enter the amount of days to show only threads created within that time';

//  recent posts
$l['asb_recent_posts'] = 'Recent Posts';
$l['asb_recent_posts_desc'] = 'Lists the latest posts.';

$l['asb_recent_posts_max_title'] = 'Post Limit';
$l['asb_recent_posts_max_description'] = 'maximal number of posts to display';

$l['asb_recent_posts_max_length_title'] = 'Maximum Post Length';
$l['asb_recent_posts_max_length_description'] = 'enter nothing or 0 to show the full post message, or enter the maximal number of characters to display (default 20)';

$l['asb_recent_posts_no_posts'] = 'There are no posts to display.';

// pms
$l['asb_private_messages'] = 'Private Messages';
$l['asb_private_messages_desc'] = "Lists the user's private message info.";

$l['asb_pms_received_new'] = '{1} of {2} messages unread';

$l['asb_pms_no_messages'] = 'Please {1} or {2} to use this functionality.';
$l['asb_pms_login'] = 'login';
$l['asb_pms_register'] = 'register';
$l['asb_pms_user_disabled_pms'] = 'You have disabled this functionality in {1}.';
$l['asb_pms_disabled_by_admin'] = "You don't have privileges to access this functionality, or it has been disabled by administrator. You may contact administrator for assistance.";
$l['asb_pms_unread'] = 'Unread Messages';
$l['asb_pms_total'] = 'Total Messages';

$l['asb_private_messages_no_content'] = 'Either you do not have permission to access private messages, or you have disabled them in User CP';

// random quotes
$l['asb_random_quotes'] = 'Random Quotes';
$l['asb_random_quotes_desc'] = 'Displays random quotes with a link and an avatar.';
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

$l['asb_random_quote_users_profile'] = "{1}'s profile";

// staff online
$l['asb_staff_online'] = 'Online Staff';
$l['asb_staff_online_desc'] = 'Lists online staff members with badges and info.';

$l['asb_staff_online_max_staff_title'] = 'Staff Limit';
$l['asb_staff_online_max_staff_desc'] = 'maximal number of staff members to display';

$l['asb_staff_online_no_staff_online'] = 'There are no staff members currently online.';

// slideshow
$l['asb_slideshow'] = 'Slideshow';
$l['asb_slideshow_desc'] = 'Displays images in series with configurable options.';

$l['asb_slideshow_folder_title'] = 'Image Path';
$l['asb_slideshow_folder_description'] = 'this is the folder that contains the images to display from the forum root. (Example: images OR images/english NOT http://myforum.com/images)';

$l['asb_slideshow_recursive_title'] = 'Recursive?';
$l['asb_slideshow_recursive_description'] = 'YES to include any images found in subfolders, NO (default) to only display images stored directly in the given folder';

$l['asb_slideshow_rate_title'] = 'Rate';
$l['asb_slideshow_rate_description'] = 'time in seconds to pause on each image';

$l['asb_slideshow_shuffle_title'] = 'Shuffle?';
$l['asb_slideshow_shuffle_description'] = 'YES (default) to randomize image order, NO to use default order';

$l['asb_slideshow_fade_rate_title'] = 'Transistion Time';
$l['asb_slideshow_fade_rate_description'] = 'time in seconds to fade from one image to the next';

$l['asb_slideshow_footer_text_title'] = 'Footer Link Text';
$l['asb_slideshow_footer_text_description'] = 'text for footer link, leave blank for no footer';

$l['asb_slideshow_footer_url_title'] = 'Footer Link URL';
$l['asb_slideshow_footer_url_description'] = 'URL for footer link, above setting must have a value before it will be effective';

$l['asb_slideshow_height_title'] = 'Height';
$l['asb_slideshow_height_description'] = 'enter the height in pixels';

$l['asb_slideshow_maintain_height_title'] = 'Maintain Height?';
$l['asb_slideshow_maintain_height_description'] = 'YES to keep the sidebox the same height throughout (default), or NO to resize the size box with each image';

$l['asb_slideshow_no_images'] = 'No images to show';

// top poster
$l['asb_top_poster_title'] = 'Top Poster';
$l['asb_top_poster_desc'] = 'Highlight the user of your forum that has made the most posts within a given time period, or for a particular forum or thread.';

$l['asb_top_poster_no_one'] = 'no one';
$l['asb_top_poster_no_posts'] = 'no posts';
$l['asb_top_poster_no_top_poster'] = 'There is no top poster to display.';
$l['asb_top_poster_no_avatar'] = 'no avatar';
$l['asb_top_poster_posts'] = 'posts';
$l['asb_top_poster_post'] = 'post';
$l['asb_top_poster_threads'] = 'threads';
$l['asb_top_poster_thread'] = 'thread';
$l['asb_top_poster_congrats'] = 'Congratulations to {1}, our current top poster for the last {2} with {3} {4}{5}!';
$l['asb_top_poster_congrats_threads'] = 'Congratulations to {1}, our current top thread starter for the last {2} with {3} {4}{5}!';
$l['asb_top_poster_congrats_all_time'] = 'Congratulations to {1}, our all time top poster with {2} {3}{4}!';
$l['asb_top_poster_congrats_all_time_threads'] = 'Congratulations to {1}, our all time thread starter with {2} {3}{4}!';
$l['asb_top_poster_specific_thread_congrats'] = ' in {1}';
$l['asb_top_poster_specific_forum_congrats'] = ' in {1}';
$l['asb_top_poster_description'] = 'Top Posters For {1}';
$l['asb_top_poster_description_threads'] = 'Top Thread Starters For {1}';

$l['asb_top_poster_time_frame_title'] = 'Time Frame';
$l['asb_top_poster_time_frame_desc'] = 'time to compare for top poster stats';

$l['asb_top_poster_threads_only_title'] = 'Threads Only?';
$l['asb_top_poster_threads_only_desc'] = 'YES to only count the creation of new threads, NO (default) to include any post in the count';

$l['asb_top_poster_fid_title'] = 'Forum ID';
$l['asb_top_poster_fid_desc'] = 'enter a forum id to show the top poster for that forum, leave this field blank to show the top poster from all forums (default)';

$l['asb_top_poster_tid_title'] = 'Thread ID';
$l['asb_top_poster_tid_desc'] = 'enter a thread id to show the top poster for that thread, leave this field blank to show the top poster from all threads (default)<p style="font-style: italic;">this setting will override the above forum setting</p><p style="font-style: italic;">this setting is ineffective if "Threads Only" is set to YES</p>';

$l['asb_top_poster_max_posters_title'] = 'Number Of Top Posters';
$l['asb_top_poster_max_posters_desc'] = '0 or invalid values default to 1';

$l['asb_top_poster_avatar_size_title'] = 'Avatar Size';
$l['asb_top_poster_avatar_size_desc'] = 'size in pixels for top poster avatar (leave blank to fill side box minus some padding)';

$l['asb_top_poster_all_time_title'] = 'All Time';
$l['asb_top_poster_one_day_title'] = 'One Day';
$l['asb_top_poster_one_week_title'] = 'One Week';
$l['asb_top_poster_two_weeks_title'] = 'Two Weeks';
$l['asb_top_poster_one_month_title'] = 'One Month';
$l['asb_top_poster_three_months_title'] = 'Three Months';
$l['asb_top_poster_six_months_title'] = 'Six Months';
$l['asb_top_poster_one_year_title'] = 'One Year';

$l['asb_top_poster_all_time'] = 'all time';
$l['asb_top_poster_one_day'] = 'day';
$l['asb_top_poster_one_week'] = 'week';
$l['asb_top_poster_two_weeks'] = 'two weeks';
$l['asb_top_poster_one_month'] = 'month';
$l['asb_top_poster_three_months'] = 'three months';
$l['asb_top_poster_six_months'] = 'six months';
$l['asb_top_poster_one_year'] = 'year';

$l['asb_top_poster_all_time_desc'] = 'All Time';
$l['asb_top_poster_one_day_desc'] = 'The Last Day';
$l['asb_top_poster_one_week_desc'] = 'The Last Week';
$l['asb_top_poster_two_weeks_desc'] = 'The Last Two Weeks';
$l['asb_top_poster_one_month_desc'] = 'The Last Month';
$l['asb_top_poster_three_months_desc'] = 'The Last Three Months';
$l['asb_top_poster_six_months_desc'] = 'The Last Six Months';
$l['asb_top_poster_one_year_desc'] = 'The Last Year';

// goals
$l['asb_goals_title'] = 'Forum Goals';
$l['asb_goals_description'] = 'Displays a progress meter for thread/post/user count goals, with a success message when the goal is met.';

	// setting
$l['asb_goals_goal_type_title'] = 'Goal Type';
$l['asb_goals_goal_type_description'] = 'the type of goal to be tracked';
$l['asb_goals_goal_type_optionscode_posts'] = 'Post Count';
$l['asb_goals_goal_type_optionscode_threads'] = 'Thread Count';
$l['asb_goals_goal_type_optionscode_users'] = 'User Count';

$l['asb_goals_goal_title'] = 'Goal';
$l['asb_goals_goal_description'] = 'the total number of items before the goal is reached';

$l['asb_goals_success_image_title'] = 'Success Image';
$l['asb_goals_success_image_description'] = 'enter an image URL here to display it when the goal has been met, leave it blank to show no image (default)';

	// messages
$l['asb_goals_goal_reached'] = "We've done it! We've made {1} {2}!";
$l['asb_goals_progress_message'] = "of the way to our goal of {1} {2}!";
$l['asb_goals_progress_bar_title'] = "{1} of {2}";

$l['asb_goals_no_content'] = 'nothing to show';

$l['asb_goals_posts'] = 'posts';
$l['asb_goals_threads'] = 'threads';
$l['asb_goals_users'] = 'users';

$l['asb_goals_footer_goal_reached'] = 'Goal Met!';
$l['asb_goals_footer_progress'] = 'Only {1} {2} left to go!';

// forum age
$l['asb_forum_age_title'] = 'Forum Age';
$l['asb_forum_age_description'] = 'Displays the date the forum was founded and/or the current age of the forum.';

$l['asb_forum_age_show_creation_date_title'] = 'Show Creation Date?';
$l['asb_forum_age_show_creation_date_description'] = 'YES (default) to include the creation date of the forum in the descriptive text, NO to show only the forum age.';

$l['asb_forum_age_forum_age_date_format_title'] = 'Forum Age Date Format';
$l['asb_forum_age_forum_age_date_format_description'] = 'Select an option to format to a specific time interval.';

$l['asb_forum_age_creation_date_format_title'] = 'Creation Date Format';
$l['asb_forum_age_creation_date_format_description'] = 'Select a format for the creation date using PHP date() format';

$l['asb_forum_age_optionscode_second'] = 'Second';
$l['asb_forum_age_optionscode_minute'] = 'Minute';
$l['asb_forum_age_optionscode_hour'] = 'Hour';
$l['asb_forum_age_optionscode_day'] = 'Day';
$l['asb_forum_age_optionscode_week'] = 'Week';
$l['asb_forum_age_optionscode_month'] = 'Month';
$l['asb_forum_age_optionscode_year'] = 'Year';

$l['asb_forum_age_years'] = '{1} years';
$l['asb_forum_age_year'] = '{1} year';

$l['asb_forum_age_months'] = '{1} months';
$l['asb_forum_age_month'] = '{1} month';

$l['asb_forum_age_weeks'] = '{1} weeks';
$l['asb_forum_age_week'] = '{1} week';

$l['asb_forum_age_days'] = '{1} days';
$l['asb_forum_age_day'] = '{1} day';

$l['asb_forum_age_hours'] = '{1} hours';
$l['asb_forum_age_hour'] = '{1} hour';

$l['asb_forum_age_minutes'] = '{1} minutes';
$l['asb_forum_age_minute'] = '{1} minute';

$l['asb_forum_age_seconds'] = '{1} seconds';
$l['asb_forum_age_second'] = '{1} second';

$l['asb_forum_age_less_than'] = 'less than a';

$l['asb_forum_age_text'] = '{1} has been active for {2}';

$l['asb_forum_age_and'] = 'and ';
$l['asb_forum_age_founded_message'] = 'This forum was founded on {1}';

$l['asb_forum_age_no_content'] = 'nothing to show';

// birthdays
$l['asb_birthdays_title'] = 'Birthdays';
$l['asb_birthdays_description'] = 'Displays today\'s birthdays and/or birthdays for the month.';

$l['asb_birthdays_time_frame_title'] = 'Timeframe';
$l['asb_birthdays_time_frame_description'] = 'Choose to show birthdays for the month, today';

$l['asb_birthdays_timeframe_optionscode_this_month'] = 'This Month';
$l['asb_birthdays_timeframe_optionscode_today'] = 'Today';

$l['asb_birthdays_upcoming_birthdays'] = 'Upcoming Birthdays';
$l['asb_birthdays_todays_birthdays'] = 'Today\'s Birthdays';

$l['asb_birthdays_user_info'] = '{1} on {2}';
$l['asb_birthdays_no_content'] = 'no birthdays to show';

$l['asb_birthdays_no_birthdays_today'] = 'No birthdays today.';
$l['asb_birthdays_no_upcoming_birthdays'] = 'No upcoming birthdays.';