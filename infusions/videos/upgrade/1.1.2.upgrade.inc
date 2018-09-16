<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: videos/upgrade/1.1.2.upgrade.inc
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
$inf_version = '1.1.2';

$dbvideos = [
    'video_allow_likes' => " ADD video_allow_likes TINYINT(1) UNSIGNED NOT NULL DEFAULT '1' AFTER video_allow_ratings"
];

foreach ($dbvideos as $key => $value) {
    if (!column_exists(DB_VIDEOS, $key)) {
        $inf_altertable[] = DB_VIDEOS.$value;
    }
}

$db_vid_cats = [
    'video_cat_parent' => " ADD INDEX video_cat_parent (video_cat_parent)"
];

foreach ($db_vid_cats as $key => $value) {
    if (!column_exists(DB_VIDEO_CATS, $key)) {
        $inf_altertable[] = DB_VIDEO_CATS.$value;
    }
}

$db_vid_settings = [
    'video_allow_likes' => 1
];

$settings = get_settings('videos');

foreach ($db_vid_settings as $key => $value) {
    if (!isset($settings[$key])) {
        $inf_insertdbrow[] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('$key', '$value', 'videos')";
    }
}

if (!db_exists(DB_VIDEO_LIKES)) {
    $inf_newtable[] = $db_prefix."video_likes (
        like_id MEDIUMINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        video_id MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
        like_user MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
        like_type TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
        PRIMARY KEY (like_id),
        KEY video_id (video_id),
        KEY like_user (like_user)
    ) ENGINE=MyISAM DEFAULT CHARSET=UTF8 COLLATE=utf8_unicode_ci";
}
