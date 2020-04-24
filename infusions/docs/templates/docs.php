<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: docs/templates/docs.php
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

if (!function_exists('render_docs')) {
    function render_docs($info) {
        $locale = fusion_get_locale();

        opentable($locale['docs_title']);

        echo render_breadcrumbs();

        if (isset($_GET['page_id'])) {
            display_docs_item($info);
        } else if (isset($_GET['cat_id'])) {
            display_docs_cat_index($info);
        } else {
            display_docs_index($info);
        }

        closetable();
    }
}

if (!function_exists('display_docs_item')) {
    function display_docs_item($info) {
        echo '<div class="row">';
            echo '<div class="col-xs-12 col-sm-3 col-md-3">';
                echo '<h4>'.$info['docs_cat_name'].'</h4>';

                echo '<hr>';

                $result = dbquery("SELECT d.*, dc.*
                    FROM ".DB_DOCS." AS d
                    LEFT JOIN ".DB_DOCS_CATS." AS dc ON d.docs_cat=dc.docs_cat_id
                    ".(multilang_table('DOC') ? "WHERE ".in_group('d.docs_language', LANGUAGE)." AND ".in_group('dc.docs_cat_language', LANGUAGE)." AND " : 'WHERE ')."
                    docs_cat = :cat
                ", [':cat' => $info['docs_cat']]);

                if (dbrows($result) > 0) {
                    while ($page = dbarray($result)) {
                        $active = $page['docs_id'] == $_GET['page_id'];
                        echo '<div><a '.($active ? 'class="text-bold"' : '').' href="'.DOCS.'docs.php?page_id='.$page['docs_id'].'">'.$page['docs_name'].'</a></div>';
                    }
                }
            echo '</div>';

            echo '<div class="col-xs-12 col-sm-9 col-md-9">';
                echo '<h1>'.$info['docs_name'].'</h1>';
                echo '<p>'.$info['docs_article'].'</p>';
            echo '</div>';
        echo '</div>';
    }
}

if (!function_exists('display_docs_cat_index')) {
    function display_docs_cat_index($info) {
        if (!empty($info['pages'])) {
            echo '<h1>'.$info['cat_name'].'</h1>';
            echo '<p>'.$info['description'].'</p>';
            echo '<hr>';

            echo '<div class="row">';
            foreach ($info['pages'] as $page) {
                echo '<div class="col-xs-6 col-sm-6"><a href="'.$page['page_link'].'">'.$page['docs_name'].'</a></div>';
            }
            echo '</div>';
        } else {
            echo '<div class="well text-center">'.fusion_get_locale('docs_016').'</div>';
        }
    }
}

if (!function_exists('display_docs_index')) {
    function display_docs_index($info) {
        $locale = fusion_get_locale();

        if (!empty($info['categories'])) {
            echo '<div class="row">';

            foreach ($info['categories'] as $cdata) {
                echo '<div class="col-xs-12 col-sm-6">';
                    echo '<div class="panel panel-default">';
                        echo '<div class="panel-heading clearfix">';
                            echo '<h4 class="pull-left">'.$cdata['docs_cat_name'].'</h4>';
                        echo '</div>';

                        echo '<div class="panel-body">';
                            echo $cdata['docs_cat_description'];

                            echo '<a class="display-block m-t-5" href="'.DOCS.'docs.php?cat_id='.$cdata['docs_cat_id'].'">'.$locale['docs_025'].'</a>';
                        echo '</div>';
                    echo '</div>';
                echo '</div>';
            }

            echo '</div>';
        } else {
            echo '<div class="well text-center">'.$locale['docs_021'].'</div>';
        }
    }
}
