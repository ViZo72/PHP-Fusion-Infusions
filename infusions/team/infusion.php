<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: team/infusion.php
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

$locale = fusion_get_locale('', TEAM_LOCALE);

$inf_title       = $locale['TEAM_title'];
$inf_description = $locale['TEAM_desc'];
$inf_version     = '1.00';
$inf_developer   = 'RobiNN';
$inf_email       = 'kelcakrobo@gmail.com';
$inf_weburl      = 'https://github.com/RobiNN1';
$inf_folder      = 'team';
$inf_image       = 'team.svg';

$inf_adminpanel[] = [
    'title'  => $locale['TEAM_title_admin'],
    'image'  => $inf_image,
    'panel'  => 'admin.php',
    'rights' => 'TEAM',
    'page'   => 5
];

$inf_newtable[] = DB_TEAM." (
    team_id MEDIUMINT(8) UNSIGNED NOT NULL AUTO_INCREMENT,
    userid MEDIUMINT(8) NOT NULL DEFAULT 0,
    position VARCHAR(50) NOT NULL DEFAULT '',
    profession VARCHAR(50) NOT NULL DEFAULT '',
    PRIMARY KEY (team_id)
) ENGINE=MyISAM DEFAULT CHARSET=UTF8 COLLATE=utf8_unicode_ci";

$inf_droptable[] = DB_TEAM;
