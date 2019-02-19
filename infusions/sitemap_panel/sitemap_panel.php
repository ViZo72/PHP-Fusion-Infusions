<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: sitemap_panel/sitemap_panel.php
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

require_once INFUSIONS.'sitemap_panel/autoloader.php';

$smg = new SitemapGenerator();

if (file_exists($smg->sitemap_file)) {
    if ($smg->sitemap_settings['auto_update'] == 1) {
        if ((TIME - filemtime($smg->sitemap_file)) > $smg->sitemap_settings['update_interval']) {
            $smg->GenerateXML();
        }
    }
}
