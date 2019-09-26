<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: team/infusion_db.php
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

if (!defined('TM_LOCALE')) {
    if (file_exists(INFUSIONS.'team/locale/'.LOCALESET.'team.php')) {
        define('TM_LOCALE', INFUSIONS.'team/locale/'.LOCALESET.'team.php');
    } else {
        define('TM_LOCALE', INFUSIONS.'team/locale/English/team.php');
    }
}

if (!defined('TEAM')) {
    define('TEAM', INFUSIONS.'team/');
}

if (!defined('DB_TEAM')) {
    define('DB_TEAM', DB_PREFIX.'team');
}

\PHPFusion\Admins::getInstance()->setAdminPageIcons('TM', '<i class="admin-ico fa fa-fw fa-users"></i>');
