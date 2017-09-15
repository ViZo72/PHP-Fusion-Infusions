<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: team/admin.php
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

pageAccess('TEAM');

use \PHPFusion\BreadCrumbs;

class Team {
    private $locale = [];
    private $data = [
        'team_id'    => 0,
        'userid'     => 0,
        'position'   => '',
        'profession' => ''
    ];

    public function __construct() {
        $this->locale = fusion_get_locale('', TEAM_LOCALE);
        $result = dbquery("SELECT * FROM ".DB_TEAM." WHERE team_id='".(isset($_GET['userid']) ? $_GET['userid'] : '')."'");

        if (isset($_GET['section']) && $_GET['section'] == 'back') redirect(FUSION_SELF.fusion_get_aidlink());

        if ((isset($_GET['action']) && $_GET['action'] == 'edit') && (isset($_GET['userid']) && isnum($_GET['userid']))) {
            if (dbrows($result)) {
                $this->data = dbarray($result);
            } else {
                redirect(FUSION_SELF.fusion_get_aidlink());
            }
        }

        if ((isset($_GET['action']) && $_GET['action'] == 'delete') && (isset($_GET['userid']) && isnum($_GET['userid']))) {
            if (dbrows($result)) dbquery("DELETE FROM ".DB_TEAM." WHERE team_id='".intval($_GET['userid'])."'");
            addNotice('success', $this->locale['TEAM_011']);
            redirect(FUSION_SELF.fusion_get_aidlink());
        }

        if (isset($_POST['save'])) {
            $this->data = [
                'team_id'    => form_sanitizer(!empty($_GET['userid']) ? $_GET['userid'] : $_POST['team_id'], 0, 'team_id'),
                'userid'     => form_sanitizer(!empty($_GET['userid']) ? $_GET['userid'] : $_POST['user'], '', 'user'),
                'position'   => form_sanitizer($_POST['position'], '', 'position'),
                'profession' => form_sanitizer($_POST['profession'], '', 'profession')
            ];

            if (dbcount('(team_id)', DB_TEAM, "team_id='".$this->data['userid']."'")) {
                dbquery_insert(DB_TEAM, $this->data, 'update');

                if (\defender::safe()) {
                    addNotice('success', $this->locale['TEAM_010']);
                    redirect(FUSION_SELF.fusion_get_aidlink());
                }
            } else {
                dbquery_insert(DB_TEAM, $this->data, 'save');

                if (\defender::safe()) {
                    addNotice('success', $this->locale['TEAM_009']);
                    redirect(FUSION_SELF.fusion_get_aidlink());
                }
            }
        }
    }

    public function Display() {
        add_to_title($this->locale['TEAM_title_admin']);
        BreadCrumbs::getInstance()->addBreadCrumb([
            'link'  => INFUSIONS.'team/admin.php'.fusion_get_aidlink(),
            'title' => $this->locale['TEAM_title_admin']
        ]);

        opentable($this->locale['TEAM_title_admin']);

        $edit = (isset($_GET['action']) && $_GET['action'] == 'edit') && isset($_GET['userid']) ? TRUE : FALSE;
        $allowed_section = ['list', 'form'];
        $_GET['section'] = isset($_GET['section']) && in_array($_GET['section'], $allowed_section) ? $_GET['section'] : 'list';

        if (isset($_GET['section']) && $_GET['section'] == 'form') {
            $tab['title'][] = $this->locale['back'];
            $tab['id'][]    = 'back';
            $tab['icon'][]  = 'fa fa-fw fa-arrow-left';
        }

        $tab['title'][] = $this->locale['TEAM_title'];
        $tab['id'][]    = 'list';
        $tab['icon'][]  = 'fa fa-fw fa-users';

        $tab['title'][] = $edit ? $this->locale['edit'] : $this->locale['add'];
        $tab['id'][]    = 'form';
        $tab['icon'][]  = 'fa fa-'.($edit ? 'pencil' : 'plus');

        echo opentab($tab, $_GET['section'], 'teamadmin', TRUE, 'nav-tabs m-b-20');
            switch ($_GET['section']) {
                case 'form':
                    $this->Form();
                    break;
                default:
                    $this->Listing();
                    break;
            }
        echo closetab();

        closetable();
    }

    private function Form() {
        echo openform('teamform', 'post', FUSION_REQUEST);
            echo form_hidden('team_id', '', $this->data['team_id']);
            echo form_user_select('user', $this->locale['TEAM_008'], $this->data['userid'], ['inline' => TRUE, 'allow_self' => TRUE]);
            echo form_text('position', $this->locale['TEAM_002'], $this->data['position'], ['inline' => TRUE]);
            echo form_text('profession', $this->locale['TEAM_003'], $this->data['profession'], ['inline' => TRUE]);
            echo form_button('save', $this->locale['save'], 'save', ['class' => 'btn-success']);
        echo closeform();
    }

    private function Listing() {
        $result = dbquery("SELECT t.*, u.user_id, u.user_name, u.user_status, u.user_avatar, u.user_level, u.user_joined
            FROM ".DB_TEAM." t
            LEFT JOIN ".DB_USERS." u ON t.userid=u.user_id
        ");

        echo '<div class="table-responsive"><table class="table table-striped table-bordered">';
        echo '<thead><tr>';
            echo '<td>'.$this->locale['TEAM_001'].'</td>';
            echo '<td>'.$this->locale['TEAM_002'].'</td>';
            echo '<td>'.$this->locale['TEAM_003'].'</td>';
            echo '<td>'.$this->locale['TEAM_004'].'</td>';
            echo '<td>'.$this->locale['TEAM_006'].'</td>';
        echo '</tr></thead>';

        if (dbrows($result)) {
            while ($data = dbarray($result)) {
                echo '<tr>';
                    echo '<td>';
                        echo display_avatar($data, '20px', '', false, 'img-circle m-r-5');
                        echo profile_link($data['user_id'], $data['user_name'], $data['user_status']);
                    echo '</td>';
                    echo '<td>'.$data['position'].'</td>';
                    echo '<td>'.$data['profession'].'</td>';
                    echo '<td>'.showdate('shortdate', $data['user_joined']).'</td>';
                    echo '<td>';
                        echo '<a href="'.FUSION_SELF.fusion_get_aidlink().'&amp;section=form&amp;action=edit&amp;userid='.$data['team_id'].'">'.$this->locale['edit'].'</a> | ';
                        echo '<a href="'.FUSION_SELF.fusion_get_aidlink().'&amp;action=delete&amp;userid='.$data['team_id'].'">'.$this->locale['delete'].'</a>';
                    echo '</td>';
                echo '</tr>';
            }
        } else {
            echo '<tr><td colspan="6" class="text-center">'.$this->locale['TEAM_007'].'</td></tr>';
        }
        echo '</table></div>';
    }
}

$team = new Team();
$team->Display();

require_once THEMES.'templates/footer.php';
