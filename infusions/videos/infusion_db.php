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
if (!defined('IN_FUSION')) {
    die('Access Denied');
}

if (!defined('VID_LOCALE')) {
    if (file_exists(INFUSIONS.'videos/locale/'.LANGUAGE.'.php')) {
        define('VID_LOCALE', INFUSIONS.'videos/locale/'.LANGUAGE.'.php');
    } else {
        define('VID_LOCALE', INFUSIONS.'videos/locale/English.php');
    }
}

if (!defined('VIDEOS')) {
    define('VIDEOS', INFUSIONS.'videos/');
}

if (!defined('DB_VIDEOS')) {
    define('DB_VIDEOS', DB_PREFIX.'videos');
}

if (!defined('DB_VIDEO_CATS')) {
    define('DB_VIDEO_CATS', DB_PREFIX.'video_cats');
}

\PHPFusion\Admins::getInstance()->setAdminPageIcons('VID', '<i class="admin-ico fa fa-fw fa-play"></i>');
\PHPFusion\Admins::getInstance()->setCommentType('VID', fusion_get_locale('VID_title', VID_LOCALE));
\PHPFusion\Admins::getInstance()->setLinkType('VID', fusion_get_settings('siteurl').'infusions/videos/videos.php?video_id=%s');

$inf_settings = get_settings('videos');
if ($inf_settings['video_allow_submission']) {
    if (method_exists(\PHPFusion\Admins::getInstance(), 'setSubmitData')) {
        \PHPFusion\Admins::getInstance()->setSubmitData('v', [
            'infusion_name' => 'videos',
            'link'          => INFUSIONS.'videos/video_submit.php',
            'submit_link'   => 'submit.php?stype=v',
            'submit_locale' => fusion_get_locale('VID_title', VID_LOCALE),
            'title'         => fusion_get_locale('VID_submit_0007', VID_LOCALE),
            'admin_link'    => INFUSIONS.'videos/admin.php'.fusion_get_aidlink().'&amp;section=submissions&amp;submit_id=%s'
        ]);
    } else {
        // 9.0
        \PHPFusion\Admins::getInstance()->setSubmitType('v', fusion_get_locale('VID_title', VID_LOCALE));
        \PHPFusion\Admins::getInstance()->setSubmitLink('v', INFUSIONS.'videos/admin.php'.fusion_get_aidlink().'&amp;section=submissions&amp;submit_id=%s');
    }
}

if (method_exists(\PHPFusion\Admins::getInstance(), 'setFolderPermissions')) {
    \PHPFusion\Admins::getInstance()->setFolderPermissions('videos', [
        'infusions/videos/videos/'             => TRUE,
        'infusions/videos/images/'             => TRUE,
        'infusions/videos/submissions/'        => TRUE,
        'infusions/videos/submissions/images/' => TRUE
    ]);
}
