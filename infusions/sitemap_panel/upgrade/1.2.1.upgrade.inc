<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: sitemap_panel/upgrade/1.2.1.upgrade.inc
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
$inf_version = '1.2.1';

$insert_settings = [
    'sitemap_index' => 0
];

$settings = get_settings('sitemap_panel');

foreach ($insert_settings as $key => $value) {
    if (!isset($settings[$key])) {
        $inf_insertdbrow[] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('$key', '$value', 'sitemap_panel')";
    }
}

$modules = [
    'videos'     => ['enabled' => 1, 'frequency' => 'weekly', 'priority' => '0.6'],
    'video_cats' => ['enabled' => 1, 'frequency' => 'monthly', 'priority' => '0.3']
];

foreach ($modules as $name => $data) {
    $inf_insertdbrow[] = DB_SITEMAP." (name, enabled, frequency, priority) VALUES ('".$name."', '".$data['enabled']."', '".$data['frequency']."', '".$data['priority']."')";
}
