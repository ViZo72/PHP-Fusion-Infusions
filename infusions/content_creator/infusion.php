<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: content_creator/infusion.php
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

$locale = fusion_get_locale('', CC_LOCALE);

// Infusion general information
$inf_title       = $locale['CC_title'];
$inf_description = $locale['CC_desc'];
$inf_version     = '1.1.4';
$inf_developer   = 'RobiNN';
$inf_email       = 'kelcakrobo@gmail.com';
$inf_weburl      = 'https://github.com/RobiNN1';
$inf_folder      = 'content_creator';
$inf_image       = 'content_creator.svg';

// Multilanguage links
$enabled_languages = makefilelist(LOCALE, ".|..", TRUE, "folders");
if (!empty($enabled_languages)) {
    foreach ($enabled_languages as $language) {
        include INFUSIONS.'content_creator/locale/'.$language.'.php';

        $mlt_adminpanel[$language][] = [
            'rights'   => 'CC',
            'image'    => $inf_image,
            'title'    => $inf_title,
            'panel'    => 'content_creator.php',
            'page'     => 5,
            'language' => $language
        ];

        // Delete
        $mlt_deldbrow[$language][] = DB_ADMIN." WHERE admin_rights='CC' AND admin_language='".$language."'";
    }
} else {
    $inf_adminpanel[] = [
        'rights'   => 'CC',
        'image'    => $inf_image,
        'title'    => $inf_title,
        'panel'    => 'content_creator.php',
        'page'     => 5,
        'language' => LANGUAGE
    ];
}

// Uninstallation
$inf_deldbrow[] = DB_ADMIN." WHERE admin_rights='CC'";
