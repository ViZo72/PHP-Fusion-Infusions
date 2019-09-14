<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: most_read_blogs_panel.php
| Author: RobiNN
| Version: 1.0.1
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

if (file_exists(INFUSIONS.'most_read_blogs_panel/locale/'.LANGUAGE.'.php')) {
    $locale = fusion_get_locale('', INFUSIONS.'most_read_blogs_panel/locale/'.LANGUAGE.'.php');
} else {
    $locale = fusion_get_locale('', INFUSIONS.'most_read_blogs_panel/locale/English.php');
}

add_to_css('
    .most-read-blogs .nav-pills>li {float: none;display: inline-block;}
    .most-read-blogs .nav-pills>li>a {padding: 0 5px;}
    .most-read-blogs .nav-pills>li>a:hover,.most-read-blogs .nav-pills>li>a:focus {background-color: transparent;color: inherit;}
    .most-read-blogs .nav-pills>li.active>a,.most-read-blogs .nav-pills>li.active>a:hover,.most-read-blogs .nav-pills>li.active>a:focus {background-color: transparent;color: inherit;font-weight: bold;}
    .most-read-blogs hr {margin-top: 5px;margin-bottom: 5px;}
');

openside($locale['mrb_01']);
echo '<div class="most-read-blogs">';
    echo '<ul class="nav nav-pills text-center" role="tablist">';
        echo '<li role="presentation" class="active"><a href="#blogs-7days" aria-controls="blogs-7days" role="tab" data-toggle="tab">'.$locale['mrb_02'].'</a></li>';
        echo '<li role="presentation"><a href="#blogs-14days" aria-controls="blogs-14days" role="tab" data-toggle="tab">'.$locale['mrb_03'].'</a></li>';
        echo '<li role="presentation"><a href="#blogs-30days" aria-controls="blogs-30days" role="tab" data-toggle="tab">'.$locale['mrb_04'].'</a></li>';
    echo '</ul>';

    echo '<div class="tab-content m-t-5">';
        echo '<div role="tabpanel" class="tab-pane active" id="blogs-7days">';
            $result_7days = dbquery("SELECT blog_id, blog_subject, blog_reads, blog_datestamp, blog_draft
                FROM ".DB_BLOG."
                WHERE blog_draft = 0 AND blog_datestamp > '".strtotime('-7 day')."'
                ".(multilang_table('BL') ? " AND blog_language='".LANGUAGE."' AND " : '').groupaccess('blog_visibility')."
                ORDER BY blog_reads DESC
                LIMIT 15
            ");

            if (dbrows($result_7days) > 0) {
                echo '<ul class="blogs-list list-style-none">';
                $i = 0;
                while ($data = dbarray($result_7days)) {
                    echo $i > 0 ? '<hr/>' : '';
                    echo '<li><a href="'.INFUSIONS.'blog/blog.php?readmore='.$data['blog_id'].'"><span class="badge pull-right">'.$data['blog_reads'].' <i class="fa fa-eye"></i></span>'.trim_text($data['blog_subject'], 25).'</a></li>';
                    $i++;
                }
                echo '</ul>';
            } else {
                echo '<div class="text-center">'.$locale['mrb_05'].'</div>';
            }

        echo '</div>';

        echo '<div role="tabpanel" class="tab-pane" id="blogs-14days">';
            $result_14days = dbquery("SELECT blog_id, blog_subject, blog_reads, blog_datestamp, blog_draft
                FROM ".DB_BLOG."
                WHERE blog_draft = 0 AND blog_datestamp > '".strtotime('-14 day')."'
                ".(multilang_table('BL') ? " AND blog_language='".LANGUAGE."' AND " : '').groupaccess('blog_visibility')."
                ORDER BY blog_reads DESC
                LIMIT 15
            ");

            if (dbrows($result_14days) > 0) {
                echo '<ul class="blogs-list list-style-none">';
                $i = 0;
                while ($data = dbarray($result_14days)) {
                    echo $i > 0 ? '<hr/>' : '';
                    echo '<li><a href="'.INFUSIONS.'blog/blog.php?readmore='.$data['blog_id'].'"><span class="badge pull-right">'.$data['blog_reads'].' <i class="fa fa-eye"></i></span>'.trim_text($data['blog_subject'], 25).'</a></li>';
                    $i++;
                }
                echo '</ul>';
            } else {
                echo '<div class="text-center">'.$locale['mrb_05'].'</div>';
            }
        echo '</div>';

        echo '<div role="tabpanel" class="tab-pane" id="blogs-30days">';
            $result_30days = dbquery("SELECT blog_id, blog_subject, blog_reads, blog_datestamp, blog_draft
                FROM ".DB_BLOG."
                WHERE blog_draft = 0 AND blog_datestamp > '".strtotime('-30 day')."'
                ".(multilang_table('BL') ? " AND blog_language='".LANGUAGE."' AND " : '').groupaccess('blog_visibility')."
                ORDER BY blog_reads DESC
                LIMIT 15
            ");

            if (dbrows($result_30days) > 0) {
                echo '<ul class="blogs-list list-style-none">';
                $i = 0;
                while ($data = dbarray($result_30days)) {
                    echo $i > 0 ? '<hr/>' : '';
                    echo '<li><a href="'.INFUSIONS.'blog/blog.php?readmore='.$data['blog_id'].'"><span class="badge pull-right">'.$data['blog_reads'].' <i class="fa fa-eye"></i></span>'.trim_text($data['blog_subject'], 25).'</a></li>';
                    $i++;
                }
                echo '</ul>';
            } else {
                echo '<div class="text-center">'.$locale['mrb_05'].'</div>';
            }
        echo '</div>';
    echo '</div>';
echo '</div>';
closeside();
