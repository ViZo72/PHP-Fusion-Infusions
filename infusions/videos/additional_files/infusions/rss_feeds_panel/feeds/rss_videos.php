<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: rss_videos.php
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
require_once dirname(__FILE__).'../../../../maincore.php';

if (db_exists(DB_VIDEOS) && db_exists(DB_VIDEO_CATS)) {
    if (file_exists(INFUSIONS.'rss_feeds_panel/locale/'.LANGUAGE.'.php')) {
        $locale = fusion_get_locale('', INFUSIONS.'rss_feeds_panel/locale/'.LANGUAGE.'.php');
    } else {
        $locale = fusion_get_locale('', INFUSIONS.'rss_feeds_panel/locale/English.php');
    }

    $settings = fusion_get_settings();

    $result = dbquery("SELECT vc.*, v.*
        FROM ".DB_VIDEO_CATS." vc
        RIGHT JOIN ".DB_VIDEOS." v ON vc.video_cat_id=v.video_cat
        WHERE ".groupaccess('video_visibility').(multilang_table('VL') ? " AND video_cat_language='".LANGUAGE."'" : '')."
        ORDER BY v.video_views DESC LIMIT 0, 10
    ");

    header('Content-Type: application/rss+xml; charset='.$locale['charset']);

    $title = $settings['sitename'].' - '.fusion_get_locale('VID_title', VID_LOCALE).(multilang_table('VL') ? $locale['rss_in'].LANGUAGE : '');

    if (file_exists(INFUSIONS.'rss_feeds_panel/RSS.php')) {
        require_once INFUSIONS.'rss_feeds_panel/RSS.php';

        $rss = new RSS('videos', $title);

        if (dbrows($result) != 0) {
            while ($data = dbarray($result)) {
                $rss->AddItem($data['video_title'], $settings['siteurl'].'infusions/videos/videos.php?video_id='.$data['video_id'], $data['video_description']);
            }
        } else {
            $rss->AddItem($settings['sitename'].' - '.fusion_get_locale('VID_title', VID_LOCALE), $settings['siteurl'], $locale['rss_nodata']);
        }

        $rss->Write();
    } else {
        echo '<?xml version="1.0" encoding="UTF-8"?>'.PHP_EOL;
        echo '<rss version="2.0" xmlns:content="http://purl.org/rss/1.0/modules/content/" xmlns:atom="http://www.w3.org/2005/Atom">'.PHP_EOL;
            echo '<channel>'.PHP_EOL;
                echo '<title>'.$title.'</title>'.PHP_EOL;
                echo '<atom:link href='.$settings['siteurl'].'infusions/rss_feeds_panel/feeds/rss_videos.php" rel="self" type="application/rss+xml"/>'.PHP_EOL;
                echo '<link>'.$settings['siteurl'].'</link>'.PHP_EOL;
                echo '<description>'.$settings['description'].'</description>'.PHP_EOL;

                if (dbrows($result) != 0) {
                    while ($data = dbarray($result)) {
                        echo '<item>'.PHP_EOL;
                            echo '<title><![CDATA['.html_entity_decode(htmlentities(stripslashes($data['video_title']))).']]></title>'.PHP_EOL;
                            echo '<link>'.$settings['siteurl'].'infusions/videos/videos.php?video_id='.$data['video_id'].'</link>'.PHP_EOL;

                            if (!empty($data['video_description'])) {
                                echo '<description><![CDATA['.html_entity_decode(htmlentities(stripslashes($data['video_description']))).']]></description>'.PHP_EOL;
                            }
                        echo '</item>'.PHP_EOL;
                    }
                } else {
                    echo '<item>'.PHP_EOL;
                        echo '<title><![CDATA['.$settings['sitename'].' - '.fusion_get_locale('VID_title', VID_LOCALE).']]></title>'.PHP_EOL;
                        echo '<link>'.$settings['siteurl'].'</link>'.PHP_EOL;
                        echo '<description><![CDATA['.$locale['rss_nodata'].']]></description>'.PHP_EOL;
                    echo '</item>'.PHP_EOL;
                }

            echo '</channel>'.PHP_EOL;
        echo '</rss>'.PHP_EOL;
    }
}
