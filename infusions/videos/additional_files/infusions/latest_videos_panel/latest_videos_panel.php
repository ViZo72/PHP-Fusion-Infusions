<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: latest_videos_panel.php
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

if (defined('VIDEOS_EXIST')) {
    $side_panel = FALSE;
    require_once INFUSIONS.'videos/functions.php';

    $result = dbquery("SELECT v.*, vc.video_cat_id, vc.video_cat_name, u.user_id, u.user_name, u.user_status, u.user_avatar, u.user_level, u.user_joined
        FROM ".DB_VIDEOS." v
        INNER JOIN ".DB_VIDEO_CATS." vc on v.video_cat = vc.video_cat_id
        LEFT JOIN ".DB_USERS." u ON v.video_user=u.user_id
        ORDER BY v.video_datestamp DESC
        LIMIT 6
    ");

    $locale = fusion_get_locale('', VID_LOCALE);

    if (dbrows($result)) {
        openside($locale['VID_latest']);
        echo '<div class="'.($side_panel == TRUE ? 'list-group' : 'row equal-height').'">';

        while ($data = dbarray($result)) {
            echo '<div class="'.($side_panel == TRUE ? 'list-group-item' : 'col-xs-12 col-sm-4 col-md-3').'">';
                echo '<div'.($side_panel == TRUE ? ' class="pull-left m-r-15"' : '').'>';
                    echo '<a href="'.VIDEOS.'videos.php?video_id='.$data['video_id'].'" class="display-inline-block image-wrap thumb text-center overflow-hide m-2">';
                        echo '<img style="object-fit: contain;height: 100px; width: 100px;" class="img-responsive" src="'.GetVideoThumb($data).'" alt="'.$data['video_title'].'"/>';
                     echo '</a>';
                echo '</div>';

                echo '<div class="overflow-hide">';
                    echo '<a href="'.VIDEOS.'videos.php?video_id='.$data['video_id'].'"><span class="strong text-dark">'.$data['video_title'].'</span></a><br/>';
                    echo '<div>';
                        echo '<span><i class="fa fa-fw fa-folder"></i> '.$locale['VID_009'].' <a class="badge" href="'.VIDEOS.'videos.php?cat_id='.$data['video_cat_id'].'">'.$data['video_cat_name'].'</a></span>';
                        echo '<br/><span><i class="fa fa-fw fa-user"></i> '.profile_link($data['user_id'], $data['user_name'], $data['user_status']).'</span>';
                        echo '<br/><span><i class="fa fa-fw fa-clock-o"></i> '.$data['video_length'].'</span>';
                        echo '<br/><span><i class="fa fa-fw fa-calendar"></i> '.showdate('shortdate', $data['video_datestamp']).'</span>';
                    echo '</div>';
                echo '</div>';
            echo '</div>';
        }

        echo '</div>';
        closeside();
    }
}
