<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: videos/templates/videos.php
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

if (!function_exists('render_videos')) {
    function render_videos($info) {
        opentable(fusion_get_locale('VID_title'));

        echo render_breadcrumbs();

        if (isset($_GET['video_id']) && !empty($info['video_item'])) {
            display_video_item($info);
        } else {
            display_video_index($info);
        }

        closetable();

        \PHPFusion\Panels::addPanel('video_menu_panel', display_video_menu($info), \PHPFusion\Panels::PANEL_RIGHT, iGUEST, 9);
    }
}

if (!function_exists('display_video_item')) {
    function display_video_item($info) {
        $locale = fusion_get_locale();
        $data = $info['video_item'];

        echo '<div class="clearfix">';
            echo '<div class="btn-group pull-right m-t-15">';
                if ($data['admin_link']) {
                    $admin_actions = $data['admin_link'];
                    echo '<a class="btn btn-default btn-sm" href="'.$admin_actions['edit'].'"><i class="fa fa-pencil"></i> '.$locale['edit'].'</a>';
                    echo '<a class="btn btn-danger btn-sm" href="'.$admin_actions['delete'].'"><i class="fa fa-trash"></i> '.$locale['delete'].'</a>';
                }
            echo '</div>';
            echo '<h3>'.$data['video_title'].'</h3>';
        echo '</div>';

        echo $data['video_video'];

        echo '<div class="m-t-20">';
            echo '<i class="m-l-5 fa fa-eye"></i> '.$data['video_views'];
            echo '<i class="m-l-5 fa fa-folder-o"></i> '.$data['video_post_cat'];
            echo '<i class="m-l-5 fa fa-clock-o"></i> '.$data['video_post_time'];
        echo '</div>';

        echo '<hr/>';

        echo '<div class="clearfix">';
            echo '<div class="pull-left m-r-5">'.$data['video_post_author_avatar'].'</div>';
            echo '<div style="font-size: 20px;">'.$data['video_post_author'].'</div>';
            echo '<span><a href="'.BASEDIR.'messages.php?folder=inbox&amp;msg_send='.$data['user_id'].'">'.$locale['send_message'].'</a></span>';
        echo '</div>';

        echo '<p class="m-t-20">'.$data['video_description'].'</p>';

        echo '<hr/>';

        echo $data['video_show_comments'];
        echo $data['video_show_ratings'];
    }
}

if (!function_exists('display_video_index')) {
    function display_video_index($info) {
        $locale = fusion_get_locale();

        if (!empty($info['video_item'])) {
            echo '<div class="row equal-height">';

            foreach ($info['video_item'] as $video_id => $data) {
                echo '<div class="col-xs-6 col-sm-4 col-md-3 col-lg-3">';
                    echo '<article class="item">';
                        echo '<figure class="thumb">';
                            echo '<a style="max-height: 150px; max-width: 267px;" href="'.INFUSIONS.'videos/videos.php?video_id='.$data['video_id'].'">';
                                echo '<div class="video-thumbnail">';
                                    echo '<img class="img-responsive" style="height: 150px;" src="'.$data['video_image'].'" alt="'.$data['video_title'].'"/>';
                                    echo '<span class="label label-default" style="position: absolute;margin-top: -20px;z-index: 1;right: 20px;background: rgba(0, 0, 0, 0.7);">'.$data['video_length'].'</span>';
                                echo '</div>';
                            echo '</a>';
                        echo '</figure>';

                        echo '<div class="post clearfix">';
                            echo '<h4 class="post-title"><a href="'.INFUSIONS.'videos/videos.php?cat_id='.$data['video_cat'].'&amp;video_id='.$data['video_id'].'">'.$data['video_title'].'</a></h4>';
                            echo '<div class="meta">';
                                echo '<span>'.$data['video_user_link'].'</span>';
                                echo '<span> &middot; '.$data['video_views'].' &middot; </span>';
                                echo '<span>'.$data['video_post_time'].'</span>';
                            echo '</div>';
                        echo '</div>';
                    echo '</article>';
                echo '</div>';
            }
            echo '</div>';

            if (!empty($info['video_nav'])) {
                echo '<div class="text-center m-t-10 m-b-10">'.$info['video_nav'].'</div>';
            }
        } else {
            echo '<div class="well text-center">'.$locale['VID_075'].'</div>';
        }
    }
}

if (!function_exists('display_video_menu')) {
    function display_video_menu($info) {
        $locale = fusion_get_locale();

        function display_video_cats($info, $cat_id = 0, $level = 0) {
            $html = '';
            if (!empty($info[$cat_id])) {
                foreach ($info[$cat_id] as $video_cat_id => $cdata) {
                    $active = (!empty($_GET['cat_id']) && $_GET['cat_id'] == $video_cat_id) ? TRUE : FALSE;

                    $html .= '<li'.($active ? ' class="active strong"' : '').'>'.str_repeat('&nbsp;', $level).' '.$cdata['video_cat_link'];
                    if (!empty($info[$video_cat_id])) {
                        $html .= '<ul class="list-style-none">';
                        $html .= display_video_cats($info, $video_cat_id, $level + 1);
                        $html .= '</ul>';
                    }
                    $html .= '</li>';
                }
            }

            return $html;
        }

        ob_start();
        echo '<ul class="block">';
            echo '<li><a href="'.VIDEOS.'videos.php">'.$locale['VID_067'].'</a></li>';
            foreach ($info['video_filter'] as $filter_key => $filter) {
                echo '<li'.(isset($_GET['type']) && $_GET['type'] == $filter_key ? ' class="active strong"' : '').'><a href="'.$filter['link'].'">'.$filter['title'].'</a></li>';
            }
        echo '</ul>';

        openside($locale['VID_001']);
        echo '<ul class="block">';
            $video_cat_menu = display_video_cats($info['video_categories']);
            if (!empty($video_cat_menu)) {
                echo $video_cat_menu;
            } else {
                echo '<li>'.$locale['VID_072'].'</li>';
            }
        echo '</ul>';
        closeside();

        openside($locale['VID_073']);
            echo '<ul class="block">';
            if (!empty($info['video_author'])) {
                foreach ($info['video_author'] as $author_id => $author_info) {
                    echo '<li'.($author_info['active'] ? ' class="active strong"' : '').'>';
                        echo '<a href="'.$author_info['link'].'">'.$author_info['title'].'</a> <span class="badge m-l-10">'.$author_info['count'].'</span>';
                    echo '</li>';
                }
            } else {
                echo '<li>'.$locale['VID_074'].'</li>';
            }
        echo '</ul>';
        closeside();

        return ob_get_clean();
    }
}
