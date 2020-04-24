<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: docs/admin.php
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

pageAccess('DOCS');

class docsAdmin {
    private $locale;

    public function __construct() {
        $this->locale = fusion_get_locale('', DOCS_LOCALE);

        if (isset($_GET['section']) && $_GET['section'] == 'back') {
            redirect(FUSION_SELF.fusion_get_aidlink());
        }
    }

    public function displayAdmin() {
        add_to_title($this->locale['docs_title']);

        add_breadcrumb(['link' => DOCS.'admin.php'.fusion_get_aidlink(), 'title' => $this->locale['docs_title']]);

        opentable($this->locale['docs_title']);

        $edit = (isset($_GET['action']) && $_GET['action'] == 'edit') && isset($_GET['docs_id']);
        $allowed_section = ['list', 'form', 'categories'];
        $_GET['section'] = isset($_GET['section']) && in_array($_GET['section'], $allowed_section) ? $_GET['section'] : 'list';

        if (isset($_GET['section']) && $_GET['section'] == 'form' || isset($_GET['ref'])) {
            $tab['title'][] = $this->locale['back'];
            $tab['id'][]    = 'back';
            $tab['icon'][]  = 'fa fa-fw fa-arrow-left';
        }

        if (!isset($_GET['section']) && isset($_GET['ref']) && $_GET['ref'] == 'form') {
            $title = $edit ? $this->locale['edit'] : $this->locale['add'];
            $icon = 'fa fa-'.($edit ? 'pencil' : 'plus');
        } else {
            $title = $this->locale['docs_title'];
            $icon = 'fa fa-fw fa-file-alt';
        }

        $tab['title'][] = $title;
        $tab['id'][]    = 'list';
        $tab['icon'][]  = $icon;
        $tab['title'][] = $this->locale['docs_001'];
        $tab['id'][]    = 'categories';
        $tab['icon'][]  = 'fa fa-folder';

        echo opentab($tab, $_GET['section'], 'docsadmin', TRUE, 'nav-tabs m-b-20');
        switch ($_GET['section']) {
            case 'categories':
                require_once 'admin/categories.php';
                add_breadcrumb(['link' => FUSION_REQUEST, 'title' => $this->locale['docs_001']]);
                break;
            default:
                if (dbcount("(docs_cat_id)", DB_DOCS_CATS)) {
                    require_once 'admin/docs.php';
                } else {
                    echo '<div class="well text-center">'.$this->locale['docs_002'].'</div>';
                }

                if (isset($_GET['ref']) && $_GET['ref'] == 'form') {
                    add_breadcrumb(['link' => FUSION_REQUEST, 'title' => $edit ? $this->locale['edit'] : $this->locale['add']]);
                }
                break;
        }
        echo closetab();

        closetable();
    }
}

$docs = new docsAdmin();
$docs->displayAdmin();

require_once THEMES.'templates/footer.php';
