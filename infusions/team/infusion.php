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
defined('IN_FUSION') || exit;

$locale = fusion_get_locale('', TM_LOCALE);

// Infusion general information
$inf_title       = $locale['tm_title'];
$inf_description = $locale['tm_desc'];
$inf_version     = '1.0.2';
$inf_developer   = 'RobiNN';
$inf_email       = 'kelcakrobo@gmail.com';
$inf_weburl      = 'https://github.com/RobiNN1';
$inf_folder      = 'team';
$inf_image       = 'team.svg';

// Create tables
$inf_newtable[] = DB_TEAM." (
    team_id MEDIUMINT(8) UNSIGNED NOT NULL AUTO_INCREMENT,
    userid MEDIUMINT(8) NOT NULL DEFAULT 0,
    position VARCHAR(50) NOT NULL DEFAULT '',
    profession VARCHAR(50) NOT NULL DEFAULT '',
    language VARCHAR(50) NOT NULL DEFAULT '".LANGUAGE."',
    PRIMARY KEY (team_id)
) ENGINE=MyISAM DEFAULT CHARSET=UTF8 COLLATE=utf8_unicode_ci";

// Multilanguage table
$inf_mlt[] = [
    'title'  => $locale['tm_title'],
    'rights' => 'TM',
];

// Multilanguage links
$enabled_languages = makefilelist(LOCALE, '.|..', TRUE, 'folders');
if (!empty($enabled_languages)) {
    foreach ($enabled_languages as $language) {
        if (file_exists(INFUSIONS.'team/locale/'.$language.'/team.php')) {
            include INFUSIONS.'team/locale/'.$language.'/team.php';
        } else {
            include INFUSIONS.'team/locale/English/team.php';
        }

        $mlt_adminpanel[$language][] = [
            'rights'   => 'TM',
            'image'    => $inf_image,
            'title'    => $locale['tm_title_admin'],
            'panel'    => 'admin.php',
            'page'     => 5,
            'language' => $language
        ];

        // Add
        $mlt_insertdbrow[$language][] = DB_SITE_LINKS." (link_name, link_url, link_visibility, link_position, link_window, link_order, link_status, link_language) VALUES('".$locale['tm_title']."', 'infusions/team/team.php', '0', '2', '0', '10', '1', '".$language."')";

        // Delete
        $mlt_deldbrow[$language][] = DB_SITE_LINKS." WHERE link_url='infusions/team/team.php' AND link_language='".$language."'";
        $mlt_deldbrow[$language][] = DB_ADMIN." WHERE admin_rights='TM' AND admin_language='".$language."'";
    }
} else {
    $inf_adminpanel[] = [
        'rights'   => 'TM',
        'image'    => $inf_image,
        'title'    => $locale['tm_title_admin'],
        'panel'    => 'admin.php',
        'page'     => 5,
        'language' => LANGUAGE
    ];

    $inf_insertdbrow[] = DB_SITE_LINKS." (link_name, link_url, link_visibility, link_position, link_window, link_order, link_status, link_language) VALUES('".$locale['tm_title']."', 'infusions/team/team.php', '0', '2', '0', '10', '1', '".LANGUAGE."')";
}

// Uninstallation
$inf_droptable[] = DB_TEAM;
$inf_deldbrow[] = DB_ADMIN." WHERE admin_rights='TM'";
$inf_deldbrow[] = DB_LANGUAGE_TABLES." WHERE mlt_rights='TM'";
$inf_deldbrow[] = DB_SITE_LINKS." WHERE link_url='infusions/team/team.php'";
