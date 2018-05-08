<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: most_read_news_panel.php
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

if (file_exists(INFUSIONS.'most_read_news_panel/locale/'.LANGUAGE.'.php')) {
    $locale = fusion_get_locale('', INFUSIONS.'most_read_news_panel/locale/'.LANGUAGE.'.php');
} else {
    $locale = fusion_get_locale('', INFUSIONS.'most_read_news_panel/locale/English.php');
}

add_to_head('<style type="text/css">
.most-read-news .nav-pills>li {float: none;display: inline-block;}
.most-read-news .nav-pills>li>a {padding: 0 5px;}
.most-read-news .nav-pills>li>a:hover,.most-read-news .nav-pills>li>a:focus {background-color: transparent;color: inherit;}
.most-read-news .nav-pills>li.active>a,.most-read-news .nav-pills>li.active>a:hover,.most-read-news .nav-pills>li.active>a:focus {background-color: transparent;color: inherit;font-weight: bold;}
.most-read-news hr {margin-top: 5px;margin-bottom: 5px;}
</style>');

openside($locale['MRN_01']);
echo '<div class="most-read-news">';
    echo '<ul class="nav nav-pills text-center" role="tablist">';
        echo '<li role="presentation" class="active"><a href="#news-7days" aria-controls="news-7days" role="tab" data-toggle="tab">'.$locale['MRN_02'].'</a></li>';
        echo '<li role="presentation"><a href="#news-14days" aria-controls="news-14days" role="tab" data-toggle="tab">'.$locale['MRN_03'].'</a></li>';
        echo '<li role="presentation"><a href="#news-30days" aria-controls="news-30days" role="tab" data-toggle="tab">'.$locale['MRN_04'].'</a></li>';
    echo '</ul>';

    echo '<div class="tab-content m-t-5">';
        echo '<div role="tabpanel" class="tab-pane active" id="news-7days">';
            $result_7days = dbquery("SELECT news_id, news_subject, news_reads, news_datestamp, news_draft
                FROM ".DB_NEWS."
                WHERE news_draft = 0 AND news_datestamp > '".strtotime('-7 day')."'
                ".(multilang_table('NS') ? " AND news_language='".LANGUAGE."' AND " : '').groupaccess('news_visibility')."
                ORDER BY news_reads DESC
                LIMIT 15
            ");

            if (dbrows($result_7days) > 0) {
                echo '<ul class="news-list list-style-none">';
                $i = 0;
                while ($data = dbarray($result_7days)) {
                    echo $i > 0 ? '<hr/>' : '';
                    echo '<li><a href="'.INFUSIONS.'news/news.php?readmore='.$data['news_id'].'"><span class="badge pull-right">'.$data['news_reads'].' <i class="fa fa-eye"></i></span>'.trim_text($data['news_subject'], 25).'</a></li>';
                    $i++;
                }
                echo '</ul>';
            } else {
                echo '<div class="text-center">'.$locale['MRN_05'].'</div>';
            }

        echo '</div>';

        echo '<div role="tabpanel" class="tab-pane" id="news-14days">';
            $result_14days = dbquery("SELECT news_id, news_subject, news_reads, news_datestamp, news_draft
                FROM ".DB_NEWS."
                WHERE news_draft = 0 AND news_datestamp > '".strtotime('-14 day')."'
                ".(multilang_table('NS') ? " AND news_language='".LANGUAGE."' AND " : '').groupaccess('news_visibility')."
                ORDER BY news_reads DESC
                LIMIT 15
            ");

            if (dbrows($result_14days) > 0) {
                echo '<ul class="news-list list-style-none">';
                $i = 0;
                while ($data = dbarray($result_14days)) {
                    echo $i > 0 ? '<hr/>' : '';
                    echo '<li><a href="'.INFUSIONS.'news/news.php?readmore='.$data['news_id'].'"><span class="badge pull-right">'.$data['news_reads'].' <i class="fa fa-eye"></i></span>'.trim_text($data['news_subject'], 25).'</a></li>';
                    $i++;
                }
                echo '</ul>';
            } else {
                echo '<div class="text-center">'.$locale['MRN_05'].'</div>';
            }
        echo '</div>';

        echo '<div role="tabpanel" class="tab-pane" id="news-30days">';
            $result_30days = dbquery("SELECT news_id, news_subject, news_reads, news_datestamp, news_draft
                FROM ".DB_NEWS."
                WHERE news_draft = 0 AND news_datestamp > '".strtotime('-30 day')."'
                ".(multilang_table('NS') ? " AND news_language='".LANGUAGE."' AND " : '').groupaccess('news_visibility')."
                ORDER BY news_reads DESC
                LIMIT 15
            ");

            if (dbrows($result_30days) > 0) {
                echo '<ul class="news-list list-style-none">';
                $i = 0;
                while ($data = dbarray($result_30days)) {
                    echo $i > 0 ? '<hr/>' : '';
                    echo '<li><a href="'.INFUSIONS.'news/news.php?readmore='.$data['news_id'].'"><span class="badge pull-right">'.$data['news_reads'].' <i class="fa fa-eye"></i></span>'.trim_text($data['news_subject'], 25).'</a></li>';
                    $i++;
                }
                echo '</ul>';
            } else {
                echo '<div class="text-center">'.$locale['MRN_05'].'</div>';
            }
        echo '</div>';
    echo '</div>';
echo '</div>';
closeside();
