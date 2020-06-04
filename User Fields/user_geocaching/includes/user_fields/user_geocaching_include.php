<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: user_geocaching_include.php
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

// Display user field input
if ($profile_method == 'input') {
    $options = [
        'inline'      => TRUE,
        'max_length'  => 40,
        'error_text'  => $locale['uf_geocaching_error'],
        'placeholder' => $locale['uf_geocaching_id']
    ] + $options;
    $user_fields = form_text('user_geocaching', $locale['uf_geocaching'], $field_value, $options);
    // Display in profile
} else if ($profile_method == 'display') {
    if ($field_value) {
        $field_value = '<a href="https://www.geocaching.com/profile/?guid='.$field_value.'" target="_blank"><img src="https://img.geocaching.com/stats/img.aspx?txt=Let\'s Go Geocaching!&uid='.$field_value.'&bg=1" alt="Profile" /></a>';
    }
    $user_fields = [
        'title' => $locale['uf_geocaching'],
        'value' => $field_value ?: ''
    ];
}
