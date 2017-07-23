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

if (file_exists(INFUSIONS.'content_creator/locale/'.LANGUAGE.'.php')) {
    include INFUSIONS.'content_creator/locale/'.LANGUAGE.'.php';
} else {
    include INFUSIONS.'content_creator/locale/English.php';
}

$inf_title       = $locale['CC_title'];
$inf_description = $locale['CC_descr'];
$inf_version     = '1.0.1';
$inf_developer   = 'RobiNN';
$inf_email       = 'kelcakrobo@gmail.com';
$inf_weburl      = 'https://github.com/RobiNN1';
$inf_folder      = 'content_creator';
$inf_image       = 'content_creator.svg';

$inf_adminpanel[] = [
    'image'  => $inf_image,
    'page'   => 5,
    'rights' => 'CC',
    'title'  => $inf_title,
    'panel'  => 'content_creator.php',
];
