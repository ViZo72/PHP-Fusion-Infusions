<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: most_read_blogs_panel.php
| Author: RobiNN
| Version: 1.0.0
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

if (file_exists(INFUSIONS.'most_read_blogs_panel/locale/'.LANGUAGE.'php')) {
    $locale = fusion_get_locale('', INFUSIONS.'most_read_blogs_panel/locale/'.LANGUAGE.'.php');
} else {
    $locale = fusion_get_locale('', INFUSIONS.'most_read_blogs_panel/locale/English.php');
}

add_to_head('<style type="text/css">
.most-read-blogs .nav-pills>li {float: none;display: inline-block;}
.most-read-blogs .nav-pills>li>a {padding: 0 5px;}
.most-read-blogs .nav-pills>li>a:hover,.most-read-blogs .nav-pills>li>a:focus {background-color: transparent;color: inherit;}
.most-read-blogs .nav-pills>li.active>a,.most-read-blogs .nav-pills>li.active>a:hover,.most-read-blogs .nav-pills>li.active>a:focus {background-color: transparent;color: inherit;font-weight: bold;}
</style>');

openside($locale['MRB_01']);
echo '<div class="most-read-blogs">';
    echo '<ul class="nav nav-pills text-center" role="tablist">';
        echo '<li role="presentation" class="active"><a href="#days7" aria-controls="days7" role="tab" data-toggle="tab">'.$locale['MRB_02'].'</a></li>';
        echo '<li role="presentation"><a href="#days14" aria-controls="days14" role="tab" data-toggle="tab">'.$locale['MRB_03'].'</a></li>';
        echo '<li role="presentation"><a href="#days30" aria-controls="days30" role="tab" data-toggle="tab">'.$locale['MRB_04'].'</a></li>';
    echo '</ul>';

    echo '<div class="tab-content m-t-5">';
        echo '<div role="tabpanel" class="tab-pane active" id="days7">';
            $result_7days = dbquery("SELECT blog_id, blog_subject, blog_reads, blog_datestamp, blog_start, blog_end, blog_draft
                FROM ".DB_BLOG."
                WHERE (".time()." > blog_start OR blog_start = 0) AND blog_draft = 0 AND (".time()." < blog_end OR blog_end = 0) AND blog_draft = 0 AND blog_datestamp > '".strtotime('-7 day')."'
                ".(multilang_table('BL') ? " AND blog_language='".LANGUAGE."' AND " : '').groupaccess('blog_visibility')."
                ORDER BY blog_reads DESC
                LIMIT 15
            ");

            if (dbrows($result_7days) > 0) {
                echo '<ul>';
                while ($data = dbarray($result_7days)) {
                    echo '<li><a href="'.INFUSIONS.'blog/blog.php?readmore='.$data['blog_id'].'">'.$data['blog_subject'].'</a> <span class="badge">'.$data['blog_reads'].' <i class="fa fa-eye"></i></span></li>';
                }
                echo '</ul>';
            } else {
                echo '<div class="text-center">'.$locale['MRB_05'].'</div>';
            }

        echo '</div>';

        echo '<div role="tabpanel" class="tab-pane" id="days14">';
            $result_14days = dbquery("SELECT blog_id, blog_subject, blog_reads, blog_datestamp, blog_start, blog_end, blog_draft
                FROM ".DB_BLOG."
                WHERE (".time()." > blog_start OR blog_start = 0) AND blog_draft = 0 AND (".time()." < blog_end OR blog_end = 0) AND blog_draft = 0 AND blog_datestamp > '".strtotime('-14 day')."'
                ".(multilang_table('BL') ? " AND blog_language='".LANGUAGE."' AND " : '').groupaccess('blog_visibility')."
                ORDER BY blog_reads DESC
                LIMIT 15
            ");

            if (dbrows($result_14days) > 0) {
                echo '<ul>';
                while ($data = dbarray($result_14days)) {
                    echo '<li><a href="'.INFUSIONS.'blog/blog.php?readmore='.$data['blog_id'].'">'.$data['blog_subject'].'</a> <span class="badge">'.$data['blog_reads'].' <i class="fa fa-eye"></i></span></li>';
                }
                echo '</ul>';
            } else {
                echo '<div class="text-center">'.$locale['MRB_05'].'</div>';
            }
        echo '</div>';

        echo '<div role="tabpanel" class="tab-pane" id="days30">';
            $result_30days = dbquery("SELECT blog_id, blog_subject, blog_reads, blog_datestamp, blog_start, blog_end, blog_draft
                FROM ".DB_BLOG."
                WHERE (".time()." > blog_start OR blog_start = 0) AND blog_draft = 0 AND (".time()." < blog_end OR blog_end = 0) AND blog_draft = 0 AND blog_datestamp > '".strtotime('-30 day')."'
                ".(multilang_table('BL') ? " AND blog_language='".LANGUAGE."' AND " : '').groupaccess('blog_visibility')."
                ORDER BY blog_reads DESC
                LIMIT 15
            ");

            if (dbrows($result_30days) > 0) {
                echo '<ul>';
                while ($data = dbarray($result_30days)) {
                    echo '<li><a href="'.INFUSIONS.'blog/blog.php?readmore='.$data['blog_id'].'">'.$data['blog_subject'].'</a> <span class="badge">'.$data['blog_reads'].' <i class="fa fa-eye"></i></span></li>';
                }
                echo '</ul>';
            } else {
                echo '<div class="text-center">'.$locale['MRB_05'].'</div>';
            }
        echo '</div>';
    echo '</div>';
echo '</div>';
closeside();
