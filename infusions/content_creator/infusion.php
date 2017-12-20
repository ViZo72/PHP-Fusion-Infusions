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
if (!defined('IN_FUSION')) {
    die('Access Denied');
}

$locale = fusion_get_locale('', CC_LOCALE);

$inf_title       = $locale['CC_title'];
$inf_description = $locale['CC_desc'];
$inf_version     = '1.1.1';
$inf_developer   = 'RobiNN';
$inf_email       = 'kelcakrobo@gmail.com';
$inf_weburl      = 'https://github.com/RobiNN1';
$inf_folder      = 'content_creator';
$inf_image       = 'content_creator.svg';

$inf_adminpanel[] = [
    'title'  => $inf_title,
    'image'  => $inf_image,
    'panel'  => 'content_creator.php',
    'rights' => 'CC',
    'page'   => 5
];

$inf_deldbrow[] = DB_ADMIN." WHERE admin_rights='CC'";
