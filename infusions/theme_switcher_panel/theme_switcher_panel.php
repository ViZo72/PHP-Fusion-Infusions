<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: theme_switcher_panel/theme_switcher_panel.php
| Author: RobiNN
| Version: 1.0.3
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

if (file_exists(INFUSIONS.'theme_switcher_panel/locale/'.LANGUAGE.'php')) {
    $locale = fusion_get_locale('', INFUSIONS.'theme_switcher_panel/locale/'.LANGUAGE.'.php');
} else {
    $locale = fusion_get_locale('', INFUSIONS.'theme_switcher_panel/locale/English.php');
}

$themes = makefilelist(THEMES, '.|..|templates|admin_themes', TRUE, 'folders');

if (isset($_GET['theme'])) {
    $theme_file = '';

    foreach ($themes as $file) {
        if ($_GET['theme'] == $file) {
            setcookie(COOKIE_PREFIX.'theme', $_GET['theme']);
            redirect(FUSION_SELF);
            $theme_file = $file;
        }
    }

    $theme = $_GET['theme'] == $theme_file ? $_GET['theme'] : fusion_get_settings('theme');
} else if (isset($_COOKIE[COOKIE_PREFIX.'theme'])) {
    $theme = $_COOKIE[COOKIE_PREFIX.'theme'];
} else if (!empty(fusion_get_userdata('user_theme'))) {
    $theme = fusion_get_userdata('user_theme');
} else {
    $theme = fusion_get_settings('theme');
}

if ($theme == 'Default') {
    $theme = fusion_get_settings('theme');
}

if (isset($_POST['change'])) {
    $theme = form_sanitizer($_POST['theme'], $theme, 'theme');

    if (\defender::safe()) {
        setcookie(COOKIE_PREFIX.'theme', $theme);

        addNotice('success', $locale['TS_02']);
        redirect(FUSION_REQUEST);
    }
}

openside($locale['TS_01']);

add_to_jquery('
    $("#theme").bind("change", function () {
        $("#theme_preview").error(function() {
            $("#theme_preview").attr("src", "'.get_image('imagenotfound').'");
        });

        $("#theme_preview").attr("src", "'.INFUSIONS.'theme_switcher_panel/preview/" + $(this).val() + ".png");
    });
');

if (file_exists(INFUSIONS.'theme_switcher_panel/preview/'.$theme.'.png')) {
    echo '<img id="theme_preview" class="img-responsive m-b-15" src="'.INFUSIONS.'theme_switcher_panel/preview/'.$theme.'.png" alt="'.$theme.'">';
} else {
    echo '<img id="theme_preview" class="img-responsive m-b-15" src="'.get_image('imagenotfound').'" alt="'.$theme.'">';
}

echo openform('themeswitcher', 'post', FUSION_SELF);
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
