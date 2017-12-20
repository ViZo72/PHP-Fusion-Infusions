<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: actual_version_panel/infusion_db.php
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

if (!defined('AV_LOCALE')) {
    if (file_exists(INFUSIONS.'actual_version_panel/locale/'.LANGUAGE.'.php')) {
        define('AV_LOCALE', INFUSIONS.'actual_version_panel/locale/'.LANGUAGE.'.php');
    } else {
        define('AV_LOCALE', INFUSIONS.'actual_version_panel/locale/English.php');
    }
}

\PHPFusion\Admins::getInstance()->setAdminPageIcons('AV', '<i class="fa fa-code-fork fa-lg"></i>');
