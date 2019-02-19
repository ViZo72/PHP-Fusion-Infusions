<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: sitemap_panel/infusion.php
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

$locale = fusion_get_locale('', SMG_LOCALE);

// Infusion general information
$inf_title       = $locale['SMG_title'];
$inf_description = $locale['SMG_desc'];
$inf_version     = '1.2.0';
$inf_developer   = 'RobiNN';
$inf_email       = 'kelcakrobo@gmail.com';
$inf_weburl      = 'https://github.com/RobiNN1';
$inf_folder      = 'sitemap_panel';
$inf_image       = 'sitemap.svg';

// Create tables
$inf_newtable[] = DB_SITEMAP." (
    module_id MEDIUMINT(8) UNSIGNED NOT NULL AUTO_INCREMENT,
    name VARCHAR(20) NOT NULL DEFAULT '',
    enabled VARCHAR(10) NOT NULL DEFAULT '',
    frequency VARCHAR(10) NOT NULL DEFAULT '',
    priority VARCHAR(4) NOT NULL DEFAULT '',
    PRIMARY KEY (module_id)
) ENGINE=MyISAM DEFAULT CHARSET=UTF8 COLLATE=utf8_unicode_ci";

$inf_newtable[] = DB_SITEMAP_LINKS." (
    link_id MEDIUMINT(8) UNSIGNED NOT NULL AUTO_INCREMENT,
    url VARCHAR(200) NOT NULL DEFAULT '',
    PRIMARY KEY (link_id)
) ENGINE=MyISAM DEFAULT CHARSET=UTF8 COLLATE=utf8_unicode_ci";

$modules = [
    'customlinks'    => ['enabled' => 1, 'frequency' => 'monthly', 'priority' => '0.6'],
    'profiles'       => ['enabled' => 1, 'frequency' => 'weekly',  'priority' => '0.1'],
    'articles'       => ['enabled' => 1, 'frequency' => 'always',  'priority' => '0.5'],
    'article_cats'   => ['enabled' => 1, 'frequency' => 'monthly', 'priority' => '0.3'],
    'blogs'          => ['enabled' => 1, 'frequency' => 'always',  'priority' => '0.6'],
    'blog_cats'      => ['enabled' => 1, 'frequency' => 'monthly', 'priority' => '0.3'],
    'custompages'    => ['enabled' => 1, 'frequency' => 'weekly',  'priority' => '0.4'],
    'downloads'      => ['enabled' => 1, 'frequency' => 'weekly',  'priority' => '0.5'],
    'download_cats'  => ['enabled' => 1, 'frequency' => 'monthly', 'priority' => '0.3'],
    'faq_cats'       => ['enabled' => 1, 'frequency' => 'monthly', 'priority' => '0.3'],
    'forum'          => ['enabled' => 1, 'frequency' => 'daily',   'priority' => '0.6'],
    'gallery'        => ['enabled' => 1, 'frequency' => 'weekly',  'priority' => '0.3'],
    'gallery_albums' => ['enabled' => 1, 'frequency' => 'weekly',  'priority' => '0.4'],
    'news'           => ['enabled' => 1, 'frequency' => 'always',  'priority' => '0.6'],
    'news_cats'      => ['enabled' => 1, 'frequency' => 'monthly', 'priority' => '0.3'],
    'videos'         => ['enabled' => 1, 'frequency' => 'weekly',  'priority' => '0.5'],
    'video_cats'     => ['enabled' => 1, 'frequency' => 'monthly', 'priority' => '0.3'],
    'weblinks'       => ['enabled' => 1, 'frequency' => 'weekly',  'priority' => '0.5'],
    'weblink_cats'   => ['enabled' => 1, 'frequency' => 'monthly', 'priority' => '0.3']
];

foreach ($modules as $name => $data) {
    $inf_insertdbrow[] = DB_SITEMAP." (name, enabled, frequency, priority) VALUES ('".$name."', '".$data['enabled']."', '".$data['frequency']."', '".$data['priority']."')";
}

// Insert panel
$inf_insertdbrow[] = DB_PANELS." (panel_name, panel_filename, panel_content, panel_side, panel_order, panel_type, panel_access, panel_display, panel_status, panel_url_list, panel_restriction, panel_languages) VALUES ('".$inf_title."', '".$inf_folder."', '', '3', '1', 'file', '0', '1', '1', '', '3', '".fusion_get_settings('enabled_languages')."')";

// Insert settings
$settings = [
    'auto_update'     => 1,
    'update_interval' => 43200,
    'sitemap_index'   => 0
];

foreach ($settings as $name => $value) {
    $inf_insertdbrow[] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES('".$name."', '".$value."', '".$inf_folder."')";
}

// Multilanguage links
$enabled_languages = makefilelist(LOCALE, ".|..", TRUE, "folders");
if (!empty($enabled_languages)) {
    foreach ($enabled_languages as $language) {
        include INFUSIONS.'sitemap_panel/locale/'.$language.'.php';

        $mlt_adminpanel[$language][] = [
            'rights'   => 'SMG',
            'image'    => $inf_image,
            'title'    => $locale['SMG_title_admin'],
            'panel'    => 'admin.php',
            'page'     => 5,
            'language' => $language
        ];

        // Delete
        $mlt_deldbrow[$language][] = DB_ADMIN." WHERE admin_rights='SMG' AND admin_language='".$language."'";
    }
} else {
    $inf_adminpanel[] = [
        'rights'   => 'SMG',
        'image'    => $inf_image,
        'title'    => $locale['SMG_title_admin'],
        'panel'    => 'admin.php',
        'page'     => 5,
        'language' => LANGUAGE
    ];
}

// Uninstallation
$inf_droptable[] = DB_SITEMAP;
$inf_droptable[] = DB_SITEMAP_LINKS;
$inf_deldbrow[] = DB_ADMIN." WHERE admin_rights='SMG'";
$inf_deldbrow[] = DB_PANELS." WHERE panel_filename='".$inf_folder."'";
$inf_deldbrow[] = DB_SETTINGS_INF." WHERE settings_inf='".$inf_folder."'";
