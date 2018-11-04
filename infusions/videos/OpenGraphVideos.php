<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: videos/OpenGraphVideos.php
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

class OpenGraphVideos extends \PHPFusion\OpenGraph {
    public static function ogVideo($video_id = 0) {
        $settings = fusion_get_settings();
        $info = [];

        $result = dbquery("SELECT * FROM ".DB_VIDEOS." WHERE video_id = :video_id", [':video_id' => $video_id]);

        if (dbrows($result)) {
            $data = dbarray($result);
            $info['url'] = $settings['siteurl'].'infusions/videos/videos.php?video_id='.$video_id;
            $info['keywords'] = $data['video_keywords'] ? $data['video_keywords'] : $settings['keywords'];
            $info['title'] = $data['video_title'].' - '.$settings['sitename'];
            $info['description'] = $data['video_description'] ? fusion_first_words(strip_tags(html_entity_decode($data['video_description'])), 50) : $settings['description'];
            $info['type'] = 'video.movie';

            require_once VIDEOS.'functions.php';

            $info['image'] = GetVideoThumb($data);
        }

        self::setValues($info);
    }

    public static function ogVideoCat($cat_id = 0) {
        $settings = fusion_get_settings();
        $info = [];
        $result = dbquery("SELECT video_cat_name, video_cat_description FROM ".DB_VIDEO_CATS." WHERE video_cat_id=:cat_id", [':cat_id' => $cat_id]);

        if (dbrows($result)) {
            $data = dbarray($result);
            $info['url'] = $settings['siteurl'].'infusions/videos/videos.php?cat_id='.$cat_id;
            $info['keywords'] = $settings['keywords'];
            $info['title'] = $data['video_cat_name'].' - '.$settings['sitename'];
            $info['description'] = $data['video_cat_description'] ? fusion_first_words(strip_tags(html_entity_decode($data['video_cat_description'])), 50) : $settings['description'];
            $info['type'] = 'website';
            $info['image'] = $settings['siteurl'].'images/favicons/mstile-150x150.png';
        }

        self::setValues($info);
    }
}
