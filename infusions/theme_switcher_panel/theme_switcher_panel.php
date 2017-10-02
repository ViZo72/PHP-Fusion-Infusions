<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: theme_switcher_panel/theme_switcher_panel.php
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

if (file_exists(INFUSIONS.'theme_switcher_panel/locale'.LANGUAGE.'php')) {
    $locale = fusion_get_locale('', INFUSIONS.'theme_switcher_panel/locale/'.LANGUAGE.'.php');
} else {
    $locale = fusion_get_locale('', INFUSIONS.'theme_switcher_panel/locale/English.php');
}

$theme = isset($_COOKIE[COOKIE_PREFIX.'theme']) ? $_COOKIE[COOKIE_PREFIX.'theme'] : fusion_get_settings('theme');

if (isset($_POST['change'])) {
    $theme = form_sanitizer($_POST['theme'], $theme, 'theme');

    if (\defender::safe()) {
        /*$data = [
            'settings_name'  => 'theme',
            'settings_value' => $theme
        ];

        dbquery_insert(DB_SETTINGS, $data, 'update', ['primary_key' => 'settings_name']);*/

        setcookie(COOKIE_PREFIX.'theme', $theme);

        addNotice('success', $locale['TS_02']);
        redirect(FUSION_REQUEST);
    }
}

$themes = makefilelist(THEMES, '.|..|templates|admin_themes', TRUE, 'folders');

openside($locale['TS_01']);

add_to_jquery('
    $("#theme").bind("change", function () {
        $("#theme_preview").attr("src", "'.INFUSIONS.'theme_switcher_panel/preview/" + $(this).val() + ".png");
    });
');

if (file_exists(INFUSIONS.'theme_switcher_panel/preview/'.$theme.'.png')) {
    echo '<img id="theme_preview" class="img-responsive m-b-15" src="'.INFUSIONS.'theme_switcher_panel/preview/'.$theme.'.png" alt="'.$theme.'">';
} else {
    echo '<img id="theme_preview" class="img-responsive m-b-15" src="'.get_image('imagenotfound').'" alt="'.$theme.'">';
}

echo openform('themeswitcher', 'post', FUSION_REQUEST);
$opts = [];
foreach ($themes as $file) {
    $opts[$file] = $file;
}

echo form_select('theme', '', $theme, [
    'options'        => $opts,
    'callback_check' => 'theme_exists',
    'width'          => '100%',
    'inline'         => TRUE
]);
echo form_button('change', $locale['TS_03'], 'change');
echo closeform();
closeside();
