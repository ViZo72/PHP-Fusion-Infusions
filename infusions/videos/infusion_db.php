<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: videos/infusion_db.php
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
defined('IN_FUSION') || exit;

if (!defined('VID_LOCALE')) {
    if (file_exists(INFUSIONS.'videos/locale/'.LOCALESET.'videos.php')) {
        define('VID_LOCALE', INFUSIONS.'videos/locale/'.LOCALESET.'videos.php');
    } else {
        define('VID_LOCALE', INFUSIONS.'videos/locale/English/videos.php');
    }
}

if (!defined('VIDEOS')) {
    define('VIDEOS', INFUSIONS.'videos/');
}

if (!defined('DB_VIDEOS')) {
    define('DB_VIDEOS', DB_PREFIX.'videos');
}

if (!defined('DB_VIDEO_LIKES')) {
    define('DB_VIDEO_LIKES', DB_PREFIX.'video_likes');
}

if (!defined('DB_VIDEO_CATS')) {
    define('DB_VIDEO_CATS', DB_PREFIX.'video_cats');
}

// Admin Settings
\PHPFusion\Admins::getInstance()->setAdminPageIcons('VID', '<i class="admin-ico fa fa-fw fa-play"></i>');
\PHPFusion\Admins::getInstance()->setCommentType('VID', fusion_get_locale('vid_title', VID_LOCALE));
\PHPFusion\Admins::getInstance()->setLinkType('VID', fusion_get_settings('siteurl').'infusions/videos/videos.php?video_id=%s');

$inf_settings = get_settings('videos');
if (!empty($inf_settings['video_allow_submission']) && $inf_settings['video_allow_submission']) {
    if (method_exists(\PHPFusion\Admins::getInstance(), 'setSubmitData')) {
        \PHPFusion\Admins::getInstance()->setSubmitData('v', [
            'infusion_name' => 'videos',
            'link'          => INFUSIONS.'videos/video_submit.php',
            'submit_link'   => 'submit.php?stype=v',
            'submit_locale' => fusion_get_locale('vid_title', VID_LOCALE),
            'title'         => fusion_get_locale('video_submit', VID_LOCALE),
            'admin_link'    => INFUSIONS.'videos/admin.php'.fusion_get_aidlink().'&section=submissions&submit_id=%s'
        ]);
    } else {
        // 9.0
        \PHPFusion\Admins::getInstance()->setSubmitType('v', fusion_get_locale('vid_title', VID_LOCALE));
        \PHPFusion\Admins::getInstance()->setSubmitLink('v', INFUSIONS.'videos/admin.php'.fusion_get_aidlink().'&section=submissions&submit_id=%s');
    }
}

if (method_exists(\PHPFusion\Admins::getInstance(), 'setFolderPermissions')) {
    \PHPFusion\Admins::getInstance()->setFolderPermissions('videos', [
        'infusions/videos/videos/'             => TRUE,
        'infusions/videos/images/'             => TRUE,
        'infusions/videos/submissions/'        => TRUE,
        'infusions/videos/submissions/images/' => TRUE,
        'infusions/videos/cache/'              => TRUE
    ]);
}
