<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: search_videos_include.php
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
namespace PHPFusion\Search;

use PHPFusion\ImageRepo;
use PHPFusion\Search;

if (!defined('IN_FUSION')) {
    die('Access Denied');
}

if (db_exists(DB_VIDEOS)) {
    $formatted_result = '';
    $settings = fusion_get_settings();
    $locale = fusion_get_locale('', INFUSIONS.'videos/locale/'.LOCALESET.'search/videos.php');
    $item_count = '0 '.$locale['v400'].' '.$locale['522'].'<br/>';
    $date_search = (Search_Engine::get_param('datelimit') != 0 ? ' AND video_datestamp>='.(TIME - Search_Engine::get_param('datelimit')) : '');

    if (Search_Engine::get_param('stype') == 'videos' || Search_Engine::get_param('stype') == 'all') {
        $sort_by = [
            'datestamp' => 'video_datestamp',
            'subject'   => 'video_title',
            'author'    => 'video_user',
        ];

        $order_by = [
            '0' => ' DESC',
            '1' => ' ASC',
        ];

        $sortby = !empty(Search_Engine::get_param('sort')) ? "ORDER BY ".$sort_by[Search_Engine::get_param('sort')].$order_by[Search_Engine::get_param('order')] : '';
        $limit = (Search_Engine::get_param('stype') != 'all' ? " LIMIT ".Search_Engine::get_param('rowstart').',10' : '');

        switch (Search_Engine::get_param('fields')) {
            case 2:
                Search_Engine::search_column('video_title', 'videos');
                Search_Engine::search_column('video_description', 'videos');
                Search_Engine::search_column('video_user', 'videos');
                break;
            case 1:
                Search_Engine::search_column('video_description', 'videos');
                Search_Engine::search_column('video_title', 'videos');
                break;
            default:
                Search_Engine::search_column('video_title', 'videos');
        }

        if (!empty(Search_Engine::get_param('search_param'))) {
            $query = "SELECT v.*, vc.*
                FROM ".DB_VIDEOS." v
                INNER JOIN ".DB_VIDEO_CATS." vc ON v.video_cat=vc.video_cat_id
                ".(multilang_table('VL') ? "WHERE vc.video_cat_language='".LANGUAGE."' AND " : "WHERE ")
                .groupaccess('video_visibility')." AND ".Search_Engine::search_conditions('videos').$date_search;

            $result = dbquery($query, Search_Engine::get_param('search_param'));

            $rows = dbrows($result);
        } else {
            $rows = 0;
        }

        if ($rows != 0) {
            $item_count = '<a href="'.BASEDIR.'search.php?stype=videos&amp;stext='.Search_Engine::get_param('stext').'&amp;'.Search_Engine::get_param('composevars').'">'.$rows.' '.($rows == 1 ? $locale['v401'] : $locale['v402']).' '.$locale['522'].'</a><br/>';

            $result = dbquery("SELECT v.*, vc.*, u.user_id, u.user_name, u.user_status, u.user_avatar, u.user_joined, u.user_level
                FROM ".DB_VIDEOS." v
                INNER JOIN ".DB_VIDEO_CATS." vc ON v.video_cat=vc.video_cat_id
                LEFT JOIN ".DB_USERS." u ON v.video_user=u.user_id
                ".(multilang_table('VL') ? "WHERE vc.video_cat_language='".LANGUAGE."' AND " : "WHERE ")."
                ".Search_Engine::search_conditions('videos').$date_search.$sortby.$limit, Search_Engine::get_param('search_param'));

            $search_result = '';

            while ($data = dbarray($result)) {
                $text_all = $data['video_description'];
                $text_all = Search_Engine::search_striphtmlbbcodes($text_all);
                $text_frag = Search_Engine::search_textfrag($text_all);
                $subj_c = Search_Engine::search_stringscount($data['video_title']);
                $text_c = Search_Engine::search_stringscount($data['video_description']);

                $context = '';
                if ($text_frag != '') {
                    $context .= '<div class="quote" style="width: auto;height: auto;overflow: auto;">'.$text_frag.'</div><br/>';
                }

                $meta = '<span class="small2">'.$locale['global_070'].profile_link($data['user_id'], $data['user_name'], $data['user_status']);
                $meta .= ' | <span class="alt">'.$locale['v403'].'</span> '.showdate('%d.%m.%y', $data['video_datestamp']).' | ';
                $meta .= '<span class="alt">'.$locale['v404'].'</span> '.$data['video_views'].'</span>';

                $search_result .= strtr(Search::render_search_item(), [
                        '{%item_url%}'             => VIDEOS.'videos.php?cat_id='.$data['video_cat'].'&amp;video_id='.$data['video_id'].'&sref=search',
                        '{%item_image%}'           => '<i class="fa fa-play fa-lg"></i>',
                        '{%item_title%}'           => $data['video_title'],
                        '{%item_description%}'     => $meta,
                        '{%item_search_criteria%}' => '',
                        '{%item_search_context%}'  => $context
                    ]
                );
            }

            // Pass strings for theme developers
            $formatted_result = strtr(Search::render_search_item_wrapper(), [
                '{%image%}'          => '<img src="'.ImageRepo::getimage('ac_VID').'" alt="'.$locale['v400'].'" style="width:32px;"/>',
                '{%icon_class%}'     => 'fa fa-cloud-video fa-lg fa-fw',
                '{%search_title%}'   => $locale['v400'],
                '{%search_result%}'  => $item_count,
                '{%search_content%}' => $search_result
            ]);
        }

        Search_Engine::search_navigation($rows);
        Search_Engine::search_globalarray($formatted_result);
        Search_Engine::append_item_count($item_count);
    }
}
