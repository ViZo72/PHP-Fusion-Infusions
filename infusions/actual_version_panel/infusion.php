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
if (!defined("IN_FUSION")) {
    die("Access Denied");
}

$locale = fusion_get_locale('', AV_LOCALE);

$inf_title       = $locale['AV_title'];
$inf_description = $locale['AV_desc'];
$inf_version     = '1.00';
$inf_developer   = 'RobiNN';
$inf_email       = 'kelcakrobo@gmail.com';
$inf_weburl      = 'https://github.com/RobiNN1';
$inf_folder      = 'actual_version_panel';
$inf_image       = 'version.svg';

$inf_adminpanel[] = [
    'title'  => $locale['AV_title'],
    'image'  => $inf_image,
    'panel'  => 'admin.php',
    'rights' => 'AV',
    'page'   => 5
];

$inf_insertdbrow[] = DB_PANELS." (panel_name, panel_filename, panel_content, panel_side, panel_order, panel_type, panel_access, panel_display, panel_status, panel_url_list, panel_restriction) VALUES ('".$locale['AV_title']."', '".$inf_folder."', '', '2', '1', 'file', '0', '1', '1', '', '3')";
$inf_insertdbrow[] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES
    ('actual_version', '9.0 - 27.4. 2017', '".$inf_folder."'),
    ('phpfusion_dl_link', 'https://sourceforge.net/projects/php-fusion/files/PHP-Fusion%20Archives/9.x/PHP-Fusion%209.0.zip/download', '".$inf_folder."'),
    ('translate_dl_link', 'https://github.com/php-fusion/PHP-Fusion-9-Locale/tree/master/Czech', '".$inf_folder."')
";

$inf_deldbrow[] = DB_ADMIN." WHERE admin_rights='AV'";
$inf_deldbrow[] = DB_PANELS." WHERE panel_filename='".$inf_folder."'";
$inf_deldbrow[] = DB_SETTINGS_INF." WHERE settings_inf='".$inf_folder."'";
