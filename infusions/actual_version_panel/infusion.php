<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: actual_version_panel/infusion.php
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

$locale = fusion_get_locale('', AV_LOCALE);

// Infusion general information
$inf_title       = $locale['AV_title'];
$inf_description = $locale['AV_desc'];
$inf_version     = '1.0.1';
$inf_developer   = 'RobiNN';
$inf_email       = 'kelcakrobo@gmail.com';
$inf_weburl      = 'https://github.com/RobiNN1';
$inf_folder      = 'actual_version_panel';
$inf_image       = 'version.svg';

// Insert panel
$inf_insertdbrow[] = DB_PANELS." (panel_name, panel_filename, panel_content, panel_side, panel_order, panel_type, panel_access, panel_display, panel_status, panel_url_list, panel_restriction, panel_languages) VALUES ('".$inf_title."', '".$inf_folder."', '', '2', '1', 'file', '0', '1', '1', '', '3', '".fusion_get_settings('enabled_languages')."')";

// Insert settings
$settings = [
    'actual_version'    => '9.0 - 27.4. 2017',
    'phpfusion_dl_link' => 'https://sourceforge.net/projects/php-fusion/files/PHP-Fusion%20Archives/9.x/PHP-Fusion%209.0.zip/download',
    'translate_dl_link' => 'https://github.com/php-fusion/PHP-Fusion-9-Locale'
];

foreach ($settings as $name => $value) {
    $inf_insertdbrow[] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('".$name."', '".$value."', '".$inf_folder."')";
}

// Multilanguage links
$enabled_languages = makefilelist(LOCALE, ".|..", TRUE, "folders");
if (!empty($enabled_languages)) {
    foreach ($enabled_languages as $language) {
        INFUSIONS.'actual_version_panel/locale/'.$language.'.php';

        $mlt_adminpanel[$language][] = [
            'rights'   => 'AV',
            'image'    => $inf_image,
            'title'    => $inf_title,
            'panel'    => 'admin.php',
            'page'     => 5,
            'language' => $language
        ];

        // Delete
        $mlt_deldbrow[$language][] = DB_ADMIN." WHERE admin_rights='AV' AND admin_language='".$language."'";
    }
} else {
    $inf_adminpanel[] = [
        'rights'   => 'AV',
        'image'    => $inf_image,
        'title'    => $inf_title,
        'panel'    => 'admin.php',
        'page'     => 5,
        'language' => LANGUAGE
    ];
}

// Uninstallation
$inf_deldbrow[] = DB_ADMIN." WHERE admin_rights='AV'";
$inf_deldbrow[] = DB_PANELS." WHERE panel_filename='".$inf_folder."'";
$inf_deldbrow[] = DB_SETTINGS_INF." WHERE settings_inf='".$inf_folder."'";
