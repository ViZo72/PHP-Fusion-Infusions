<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: docs/infusion.php
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

$locale = fusion_get_locale('', DOCS_LOCALE);

// Infusion general information
$inf_title       = $locale['docs_title'];
$inf_description = $locale['docs_desc'];
$inf_version     = '1.0.0';
$inf_developer   = 'RobiNN';
$inf_email       = 'kelcakrobo@gmail.com';
$inf_weburl      = 'https://github.com/RobiNN1';
$inf_folder      = 'docs';
$inf_image       = 'docs.svg';

// Create tables
$inf_newtable[] = DB_DOCS." (
    docs_id MEDIUMINT(8) UNSIGNED NOT NULL AUTO_INCREMENT,
    docs_name VARCHAR(50) NOT NULL DEFAULT '',
    docs_cat MEDIUMINT(8) NOT NULL DEFAULT '0',
    docs_article TEXT NOT NULL,
    docs_language VARCHAR(50) NOT NULL DEFAULT '".LANGUAGE."',
    PRIMARY KEY (docs_id),
    KEY docs_cat (docs_cat)
) ENGINE=MyISAM DEFAULT CHARSET=UTF8 COLLATE=utf8_unicode_ci";

$inf_newtable[] = DB_DOCS_CATS." (
    docs_cat_id MEDIUMINT(8) UNSIGNED NOT NULL AUTO_INCREMENT,
    docs_cat_name VARCHAR(50) NOT NULL DEFAULT '',
    docs_cat_parent MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
    docs_cat_description TEXT NOT NULL,
    docs_cat_language VARCHAR(50) NOT NULL DEFAULT '".LANGUAGE."',
    PRIMARY KEY (docs_cat_id),
    KEY docs_cat_parent (docs_cat_parent)
) ENGINE=MyISAM DEFAULT CHARSET=UTF8 COLLATE=utf8_unicode_ci";

// Multilanguage table
$inf_mlt[] = [
    'title'  => $locale['docs_title'],
    'rights' => 'DOC'
];

// Multilanguage links
$enabled_languages = makefilelist(LOCALE, '.|..', TRUE, 'folders');
if (!empty($enabled_languages)) {
    foreach ($enabled_languages as $language) {
        if (file_exists(INFUSIONS.'docs/locale/'.$language.'.php')) {
            include INFUSIONS.'docs/locale/'.$language.'.php';
        } else {
            include INFUSIONS.'docs/locale/English.php';
        }

        $mlt_adminpanel[$language][] = [
            'rights'   => 'DOCS',
            'image'    => $inf_image,
            'title'    => $locale['docs_title'],
            'panel'    => 'admin.php',
            'page'     => 5,
            'language' => $language
        ];

        // Add
        $mlt_insertdbrow[$language][] = DB_SITE_LINKS." (link_name, link_url, link_visibility, link_position, link_window, link_order, link_status, link_language) VALUES ('".$locale['docs_title']."', 'infusions/docs/docs.php', '0', '2', '0', '2', '1', '".$language."')";

        // Delete
        $mlt_deldbrow[$language][] = DB_SITE_LINKS." WHERE link_url='infusions/docs/docs.php' AND link_language='".$language."'";
        $mlt_deldbrow[$language][] = DB_DOCS_CATS." WHERE docs_cat_language='".$language."'";
        $mlt_deldbrow[$language][] = DB_ADMIN." WHERE admin_rights='DOCS' AND admin_language='".$language."'";
    }
} else {
    $inf_adminpanel[] = [
        'rights'   => 'DOCS',
        'image'    => $inf_image,
        'title'    => $locale['docs_title'],
        'panel'    => 'admin.php',
        'page'     => 5,
        'language' => LANGUAGE
    ];

    $inf_insertdbrow[] = DB_SITE_LINKS." (link_name, link_url, link_visibility, link_position, link_window, link_order, link_status, link_language) VALUES ('".$locale['docs_title']."', 'infusions/docs/docs.php', '0', '2', '0', '2', '1', '".LANGUAGE."')";
}

// Uninstallation
$inf_droptable[] = DB_DOCS;
$inf_droptable[] = DB_DOCS_CATS;
$inf_deldbrow[] = DB_ADMIN." WHERE admin_rights='DOCS'";
$inf_deldbrow[] = DB_SITE_LINKS." WHERE link_url='infusions/docs/docs.php'";
$inf_deldbrow[] = DB_LANGUAGE_TABLES." WHERE mlt_rights='DOCS'";
$inf_delfiles[] = IMAGES_DOCS;
