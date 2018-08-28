<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: videos_rewrite_include.php
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

$regex = [
    '%video_id%'       => '([0-9]+)',
    '%cat_id%'         => '([0-9]+)',
    '%author_id%'      => '([0-9]+)',
    '%video_title%'    => '([0-9a-zA-Z._\W]+)',
    '%video_cat_id%'   => '([0-9]+)',
    '%author_name%'    => '([0-9a-zA-Z._\W]+)',
    '%video_cat_name%' => '([0-9a-zA-Z._\W]+)',
    '%rowstart%'       => '([0-9]+)',
    '%filter_type%'    => '([0-9a-zA-Z]+)',
    '%stype%'          => '(v)'
];

$pattern = [
    'submit-%stype%/video'                                    => 'submit.php?stype=%stype%',
    'submit-%stype%/video/submitted-and-thank-you'            => 'submit.php?stype=%stype%&amp;submitted=v',
    'videos/author/%author_id%/%author_name%'                 => 'infusions/videos/videos.php?author=%author_id%',
    'videos/filter/%filter_type%'                             => 'infusions/videos/videos.php?type=%filter_type%',
    'videos/filter/%filter_type%/rowstart/%rowstart%'         => 'infusions/videos/videos.php?type=%filter_type%&amp;rowstart=%rowstart%',
    'videos/filter/%filter_type%/category/%video_cat_id%'     => 'infusions/videos/videos.php?cat_id=%video_cat_id%&amp;type=%filter_type%',
    'videos/category/%video_cat_id%/%video_cat_name%'         => 'infusions/videos/videos.php?cat_id=%video_cat_id%',
    'videos/category/%video_cat_id%/%video_id%/%video_title%' => 'infusions/videos/videos.php?cat_id=%video_cat_id%&amp;video_id=%video_id%',
    'videos/%video_id%/%video_title%'                         => 'infusions/videos/videos.php?video_id=%video_id%',
    'videos/rowstart/%rowstart%'                              => 'infusions/videos/videos.php?rowstart=%rowstart%',
    'videos'                                                  => 'infusions/videos/videos.php'
];

$pattern_tables['%video_id%'] = [
    'table'       => DB_VIDEOS,
    'primary_key' => 'video_id',
    'id'          => ['%video_id%' => 'video_id'],
    'columns'     => [
        '%video_title%' => 'video_title'
    ]
];

$pattern_tables['%video_cat_id%'] = [
    'table'       => DB_VIDEO_CATS,
    'primary_key' => 'video_cat_id',
    'id'          => ['%video_cat_id%' => 'video_cat_id'],
    'columns'     => [
        '%video_cat_name%' => 'video_cat_name'
    ]
];

$pattern_tables['%author_id%'] = [
    'table'       => DB_USERS,
    'primary_key' => 'user_id',
    'id'          => ['%author_id%' => 'user_id'],
    'columns'     => [
        '%author_name%' => 'user_name'
    ]
];
