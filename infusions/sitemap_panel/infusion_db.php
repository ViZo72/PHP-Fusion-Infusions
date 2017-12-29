<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: sitemap_panel/infusion_db.php
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

if (!defined('SMG_LOCALE')) {
    if (file_exists(INFUSIONS.'sitemap_panel/locale/'.LANGUAGE.'.php')) {
        define('SMG_LOCALE', INFUSIONS.'sitemap_panel/locale/'.LANGUAGE.'.php');
    } else {
        define('SMG_LOCALE', INFUSIONS.'sitemap_panel/locale/English.php');
    }
}

define('DB_SITEMAP', DB_PREFIX.'sitemap');
define('DB_SITEMAP_LINKS', DB_PREFIX.'sitemap_links');

\PHPFusion\Admins::getInstance()->setAdminPageIcons('SMG', '<i class="admin-ico fa fa-fw fa-sitemap"></i>');
