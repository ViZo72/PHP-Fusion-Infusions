<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: videos/infusion.php
| Author: RobiNN
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
if (!defined('IN_FUSION')) {
    die('Access Denied');
}

$locale = fusion_get_locale('', VID_LOCALE);

// Infusion general information
$inf_title       = $locale['VID_title'];
$inf_description = $locale['VID_desc'];
$inf_version     = '1.1.2';
$inf_developer   = 'RobiNN';
$inf_email       = 'kelcakrobo@gmail.com';
$inf_weburl      = 'https://github.com/RobiNN1';
$inf_folder      = 'videos';
$inf_image       = 'videos.svg';

// Create tables
$inf_newtable[] = DB_VIDEOS." (
    video_id MEDIUMINT(8) UNSIGNED NOT NULL AUTO_INCREMENT,
    video_cat MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
    video_user MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '1',
    video_title VARCHAR(200) NOT NULL DEFAULT '',
    video_description VARCHAR(250) NOT NULL DEFAULT '',
    video_keywords VARCHAR(250) NOT NULL DEFAULT '',
    video_length VARCHAR(10) NOT NULL DEFAULT '',
    video_datestamp INT(10) UNSIGNED NOT NULL DEFAULT '0',
    video_visibility CHAR(4) NOT NULL DEFAULT '0',
    video_type VARCHAR(7) NOT NULL DEFAULT '',
    video_file VARCHAR(200) NOT NULL DEFAULT '',
    video_url VARCHAR(150) NOT NULL DEFAULT '',
    video_embed VARCHAR(500) NOT NULL DEFAULT '',
    video_image VARCHAR(120) NOT NULL,
    video_views MEDIUMINT(7) NOT NULL DEFAULT '0',
    video_allow_comments TINYINT(1) UNSIGNED NOT NULL DEFAULT '1',
    video_allow_ratings TINYINT(1) UNSIGNED NOT NULL DEFAULT '1',
    video_allow_likes TINYINT(1) UNSIGNED NOT NULL DEFAULT '1',
    PRIMARY KEY (video_id),
    KEY video_cat (video_cat),
    KEY video_datestamp (video_datestamp),
    KEY video_views (video_views)
) ENGINE=MyISAM DEFAULT CHARSET=UTF8 COLLATE=utf8_unicode_ci";

$inf_newtable[] = DB_VIDEO_LIKES." (
    like_id MEDIUMINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    video_id MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
    like_user MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
    like_type VARCHAR(10) NOT NULL DEFAULT 'like',
    PRIMARY KEY (like_id),
    KEY video_id (video_id),
    KEY like_user (like_user)
) ENGINE=MyISAM DEFAULT CHARSET=UTF8 COLLATE=utf8_unicode_ci";

$inf_newtable[] = DB_VIDEO_CATS." (
    video_cat_id MEDIUMINT(8) UNSIGNED NOT NULL AUTO_INCREMENT,
    video_cat_parent MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
    video_cat_name VARCHAR(200) NOT NULL DEFAULT '',
    video_cat_description VARCHAR(250) NOT NULL DEFAULT '',
    video_cat_sorting VARCHAR(50) NOT NULL DEFAULT 'video_title ASC',
    video_cat_language VARCHAR(50) NOT NULL DEFAULT '".LANGUAGE."',
    PRIMARY KEY(video_cat_id),
    KEY video_cat_parent (video_cat_parent)
) ENGINE=MyISAM DEFAULT CHARSET=UTF8 COLLATE=utf8_unicode_ci";

// Insert panel
$inf_insertdbrow[] = DB_PANELS." (panel_name, panel_filename, panel_content, panel_side, panel_order, panel_type, panel_access, panel_display, panel_status, panel_url_list, panel_restriction, panel_languages) VALUES('".$locale['VID_latest']."', 'latest_videos_panel', '', '3', '5', 'file', '0', '1', '1', '', '3', '".fusion_get_settings('enabled_languages')."')";

// Insert settings
$settings = [
    'video_max_b'            => 52428800,
    'video_types'            => '.flv,.mp4,.mov,.f4v,.3gp,.3g2,.mp3,.flac',
    'video_screen_max_b'     => 153600,
    'video_screen_max_w'     => 1024,
    'video_screen_max_h'     => 768,
    'video_pagination'       => 15,
    'video_allow_submission' => 1,
    'video_allow_likes'      => 1
];

foreach ($settings as $name => $value) {
    $inf_insertdbrow[] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('".$name."', '".$value."', '".$inf_folder."')";
}

// Multilanguage table
$inf_mlt[] = [
    'title'  => $locale['VID_title'],
    'rights' => 'VL',
];

// Multilanguage links
$enabled_languages = makefilelist(VIDEOS.'locale', ".|..", TRUE, 'folders');
if (!empty($enabled_languages)) {
    foreach ($enabled_languages as $language) {
        include VIDEOS.'locale/'.$language.'/videos.php';

        $mlt_adminpanel[$language][] = [
            'rights'   => 'VID',
            'image'    => $inf_image,
            'title'    => $locale['VID_title'],
            'panel'    => 'admin.php',
            'page'     => 1,
            'language' => $language
        ];

        // Add
        $mlt_insertdbrow[$language][] = DB_SITE_LINKS." (link_name, link_url, link_visibility, link_position, link_window, link_order, link_status, link_language) VALUES('".$locale['VID_title']."', 'infusions/videos/videos.php', '0', '2', '0', '2', '1', '".$language."')";
        $mlt_insertdbrow[$language][] = DB_SITE_LINKS." (link_name, link_url, link_visibility, link_position, link_window, link_order, link_status, link_language) VALUES ('".$locale['VID_submit']."', 'submit.php?stype=v', ".USER_LEVEL_MEMBER.", '1', '0', '27', '1', '".$language."')";

        // Delete
        $mlt_deldbrow[$language][] = DB_SITE_LINKS." WHERE link_url='infusions/videos/videos.php' AND link_language='".$language."'";
        $mlt_deldbrow[$language][] = DB_SITE_LINKS." WHERE link_url='submit.php?stype=v' AND link_language='".$language."'";
        $mlt_deldbrow[$language][] = DB_VIDEO_CATS." WHERE video_cat_language='".$language."'";
        $mlt_deldbrow[$language][] = DB_ADMIN." WHERE admin_rights='VID' AND admin_language='".$language."'";
    }
} else {
    $inf_adminpanel[] = [
        'rights'   => 'VID',
        'image'    => $inf_image,
        'title'    => $locale['VID_title'],
        'panel'    => 'admin.php',
        'page'     => 1,
        'language' => LANGUAGE
    ];

    $inf_insertdbrow[] = DB_SITE_LINKS." (link_name, link_url, link_visibility, link_position, link_window, link_order, link_status, link_language) VALUES('".$locale['VID_title']."', 'infusions/videos/videos.php', '0', '2', '0', '2', '1', '".LANGUAGE."')";
    $inf_insertdbrow[] = DB_SITE_LINKS." (link_name, link_url, link_visibility, link_position, link_window, link_order, link_status, link_language) VALUES ('".$locale['VID_submit']."', 'submit.php?stype=v', ".USER_LEVEL_MEMBER.", '1', '0', '27', '1', '".LANGUAGE."')";
}

// Uninstallation
$inf_droptable[] = DB_VIDEOS;
$inf_droptable[] = DB_VIDEO_LIKES;
$inf_droptable[] = DB_VIDEO_CATS;
$inf_deldbrow[] = DB_ADMIN." WHERE admin_rights='VID'";
$inf_deldbrow[] = DB_COMMENTS." WHERE comment_type='VID'";
$inf_deldbrow[] = DB_RATINGS." WHERE rating_type='VID'";
$inf_deldbrow[] = DB_PANELS." WHERE panel_filename='latest_videos_panel'";
$inf_deldbrow[] = DB_SETTINGS_INF." WHERE settings_inf='".$inf_folder."'";
$inf_deldbrow[] = DB_SITE_LINKS." WHERE link_url='infusions/videos/videos.php'";
$inf_deldbrow[] = DB_SITE_LINKS." WHERE link_url='submit.php?stype=v'";
$inf_deldbrow[] = DB_SUBMISSIONS." WHERE submit_type='v'";
$inf_deldbrow[] = DB_LANGUAGE_TABLES." WHERE mlt_rights='VL'";
$inf_delfiles[] = VIDEOS.'videos/';
$inf_delfiles[] = VIDEOS.'cache/';
