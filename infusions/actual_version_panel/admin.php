<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: actual_version_panel/admin.php
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
require_once '../../maincore.php';
require_once THEMES.'templates/admin_header.php';

pageAccess('AV');

$locale = fusion_get_locale('', AV_LOCALE);
$settings = get_settings('actual_version_panel');

\PHPFusion\BreadCrumbs::getInstance()->addBreadCrumb(['link' => INFUSIONS.'actual_version_panel/admin.php'.fusion_get_aidlink(), 'title' => $locale['AV_title']]);

if (isset($_POST['save_settings'])) {
    $settings = [
        'actual_version'    => form_sanitizer($_POST['actual_version'], '', 'actual_version'),
        'phpfusion_dl_link' => form_sanitizer($_POST['phpfusion_dl_link'], '', 'phpfusion_dl_link'),
        'translate_dl_link' => form_sanitizer($_POST['translate_dl_link'], '', 'translate_dl_link')
    ];

    if (\defender::safe()) {
        foreach ($settings as $settings_name => $settings_value) {
            $db = [
                'settings_name'  => $settings_name,
                'settings_value' => $settings_value,
                'settings_inf'   => 'actual_version_panel'
            ];

            dbquery_insert(DB_SETTINGS_INF, $db, 'update', ['primary_key' => 'settings_name']);
        }

        addNotice('success', $locale['AV_notice']);
        redirect(FUSION_REQUEST);
    }
}

opentable($locale['AV_title']);
echo openform('av_settings', 'post', FUSION_REQUEST);
    echo form_text('actual_version', $locale['AV_100'], $settings['actual_version'], ['inline' => TRUE]);
    echo form_text('phpfusion_dl_link', $locale['AV_101'], $settings['phpfusion_dl_link'], ['inline' => TRUE]);
    echo form_text('translate_dl_link', $locale['AV_102'], $settings['translate_dl_link'], ['inline' => TRUE]);
    echo form_button('save_settings', $locale['save'], $locale['save'], ['class' => 'btn-success', 'icon' => 'fa fa-hdd-o']);
echo closeform();
closetable();

require_once THEMES.'templates/footer.php';
