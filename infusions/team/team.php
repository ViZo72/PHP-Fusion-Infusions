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
require_once THEMES.'templates/header.php';

$locale = fusion_get_locale('', TM_LOCALE);

add_to_title($locale['tm_title']);
add_breadcrumb(['link' => INFUSIONS.'team/team.php', 'title' => $locale['tm_title']]);

opentable($locale['tm_title']);

$result = dbquery("SELECT t.*, u.user_id, u.user_name, u.user_status, u.user_avatar, u.user_level, u.user_joined
    FROM ".DB_TEAM." t
    LEFT JOIN ".DB_USERS." u ON t.userid=u.user_id
    ".(multilang_table('TM') ? " WHERE language='".LANGUAGE."'" : '')
);

echo '<div class="table-responsive"><table class="table table-striped table-bordered">';
    echo '<thead><tr>';
        echo '<td>'.$locale['tm_001'].'</td>';
        echo '<td>'.$locale['tm_002'].'</td>';
        echo '<td>'.$locale['tm_003'].'</td>';
        echo '<td>'.$locale['tm_004'].'</td>';
        if (iMEMBER) echo '<td>'.$locale['tm_005'].'</td>';
    echo '</tr></thead>';

    if (dbrows($result)) {
        while ($data = dbarray($result)) {
            echo '<tr>';
                echo '<td>';
                    echo display_avatar($data, '35px', '', false, 'img-circle m-r-5');
                    echo profile_link($data['user_id'], $data['user_name'], $data['user_status']);
                echo '</td>';
                echo '<td>'.$data['position'].'</td>';
                echo '<td>'.$data['profession'].'</td>';
                echo '<td>'.showdate('shortdate', $data['user_joined']).'</td>';
                if (iMEMBER) echo '<td><a href="'.BASEDIR.'messages.php?msg_send='.$data['user_id'].'"><i class="fa fa-envelope fa-fw fa-2x"></i></a></td>';
            echo '</tr>';
        }
    } else {
        echo '<tr><td colspan="5" class="text-center">'.$locale['tm_007'].'</td></tr>';
    }
echo '</table></div>';

closetable();

require_once THEMES.'templates/footer.php';
