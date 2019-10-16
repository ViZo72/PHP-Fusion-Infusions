<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: team/team.php
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
require_once dirname(__FILE__).'/../../maincore.php';

if (!defined('TEAM_EXIST')) {
    redirect(BASEDIR.'error.php?code=404');
}

require_once THEMES.'templates/header.php';
require_once TEAM.'templates/team.php';

$locale = fusion_get_locale('', TM_LOCALE);

add_to_title($locale['tm_title']);
add_breadcrumb(['link' => INFUSIONS.'team/team.php', 'title' => $locale['tm_title']]);

$info = [];

$result = dbquery("SELECT t.*, u.user_id, u.user_name, u.user_status, u.user_avatar, u.user_level, u.user_joined
    FROM ".DB_TEAM." t
    LEFT JOIN ".DB_USERS." u ON t.userid=u.user_id
    ".(multilang_table('TM') ? " WHERE ".in_group('t.language', LANGUAGE) : '')
);

if (dbrows($result)) {
    while ($data = dbarray($result)) {
        $info[] = $data;
    }
}

render_team($info);

require_once THEMES.'templates/footer.php';
