<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: sitemap/infusion_db.php
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

\PHPFusion\Admins::getInstance()->setAdminPageIcons('SM', '<i class="fa fa-sitemap fa-lg"></i>');

if (!defined('SM_LOCALE')) {
    if (file_exists(INFUSIONS.'sitemap/locale/'.LANGUAGE.'.php')) {
        define('SM_LOCALE', INFUSIONS.'sitemap/locale/'.LANGUAGE.'.php');
    } else {
        define('SM_LOCALE', INFUSIONS.'sitemap/locale/English.php');
    }
}

define('DB_SM_LINKS', DB_PREFIX.'sitemap_links');
