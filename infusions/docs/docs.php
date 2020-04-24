<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: docs/docs.php
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
require_once __DIR__.'/../../maincore.php';

if (!defined('DOCS_EXIST')) {
    redirect(BASEDIR.'error.php?code=404');
}

require_once THEMES.'templates/header.php';
require_once DOCS.'functions.php';
require_once DOCS.'templates/docs.php';

$locale = fusion_get_locale('', DOCS_LOCALE);

set_title($locale['docs_title']);
add_breadcrumb(['link' => INFUSIONS.'docs/docs.php', 'title' => \PHPFusion\SiteLinks::get_current_SiteLinks('infusions/docs/docs.php', 'link_name')]);

$info = [];

if (isset($_GET['page_id'])) {
    if (validate_docs($_GET['page_id'])) {
        $data = dbarray(dbquery(get_docs_query(['condition' => 'd.docs_id=:docs_id']), [':docs_id' => intval($_GET['page_id'])]));

        $info['cat_name'] = $data['docs_cat_name'];
        $info['page_title'] = $data['docs_name'];
        $data['docs_article'] = parse_docs_text($data['docs_article']);

        $info += $data;

        add_to_title(': '.$data['docs_name']);
        add_breadcrumb(['link' => DOCS.'docs.php?cat_id='.$data['docs_cat_id'], 'title' => $data['docs_cat_name']]);
        add_breadcrumb(['link' => DOCS.'docs.php?page_id='.intval($_GET['page_id']), 'title' => $data['docs_name']]);
    } else {
        redirect(DOCS.'docs.php');
    }
} else {
    if (isset($_GET['cat_id'])) {
        if (validate_docs_cat($_GET['cat_id'])) {
            $data = dbarray(dbquery("SELECT docs_cat_id, docs_cat_name, docs_cat_description FROM ".DB_DOCS_CATS." WHERE ".(multilang_column('DOCS') ? in_group('docs_cat_language', LANGUAGE)." AND " : '')." docs_cat_id=:docs_cat_id", [':docs_cat_id' => intval($_GET['cat_id'])]));

            add_to_title(': '.$data['docs_cat_name']);
            add_breadcrumb(['link' => DOCS.'docs.php?cat_id='.intval($_GET['cat_id']), 'title' => $data['docs_cat_name']]);

            $info['cat_name'] = $data['docs_cat_name'];
            $info['description'] = $data['docs_cat_description'];

            $result_pages = dbquery("SELECT d.*, dc.*
                FROM ".DB_DOCS." d
                LEFT JOIN ".DB_DOCS_CATS." AS dc ON d.docs_cat=dc.docs_cat_id
                WHERE dc.docs_cat_id=".$_GET['cat_id']."
                GROUP BY d.docs_id
            ");

            while ($page = dbarray($result_pages)) {
                $page['page_link'] = DOCS.'docs.php?page_id='.$page['docs_id'];
                $info['pages'][] = $page;
            }
        } else {
            redirect(DOCS.'docs.php');
        }
    } else {
        $result_cats = dbquery("SELECT *
            FROM ".DB_DOCS_CATS."
            WHERE ".in_group('docs_cat_language', LANGUAGE)."
        ");

        if (dbrows($result_cats) > 0) {
            while ($categorie = dbarray($result_cats)) {
                $info['categories'][] = $categorie;
            }
        } else {
            $info['no_cats'] = $locale['docs_021'];
        }
    }
}

render_docs($info);

require_once THEMES.'templates/footer.php';
