<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: content_creator/ContentCreator.php
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
if (!defined('IN_FUSION')) {
    die('Access Denied');
}

use \PHPFusion\BreadCrumbs;

class ContentCreator {
    private $locale      = [];
    private $snippet     = '';
    private $body        = '';
    private $short_text  = '';
    private $shout_text  = [];
    private $users       = 0;

    public function __construct() {
        $this->locale = fusion_get_locale('', CONTENT_CREATOR_LOCALE);

        add_to_title($this->locale['CC_title']);
        BreadCrumbs::getInstance()->addBreadCrumb([
            'link'  => CONTENT_CREATOR.'/content_creator_admin.php'.fusion_get_aidlink(),
            'title' => $this->locale['CC_title']
        ]);

        $this->snippet    = '<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vestibulum aliquam felis nunc, in dignissim metus suscipit eget. Nunc scelerisque laoreet purus, in ullamcorper magna sagittis eget. Aliquam ac rhoncus orci, a lacinia ante. Integer sed erat ligula. Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia Curae; Fusce ullamcorper sapien mauris, et tempus mi tincidunt laoreet. Proin aliquam vulputate felis in viverra.</p>';
        $this->body       = $this->snippet."\n<p>Duis sed lorem vitae nibh sagittis tempus sed sed enim. Mauris egestas varius purus, a varius odio vehicula quis. Donec cursus interdum libero, et ornare tellus mattis vitae. Phasellus et ligula velit. Vivamus ac turpis dictum, congue metus facilisis, ultrices lorem. Cras imperdiet lacus in tincidunt pellentesque. Sed consectetur nunc vitae fringilla volutpat. Mauris nibh justo, luctus eu dapibus in, pellentesque non urna. Nulla ullamcorper varius lacus, ut finibus eros interdum id. Proin at pellentesque sapien. Integer imperdiet, sapien nec tristique laoreet, sapien lacus porta nunc, tincidunt cursus risus mauris id quam.</p>";
        $this->short_text = '<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vestibulum tempor aliquam nulla eu dapibus. Donec pulvinar porttitor urna, in ultrices dolor cursus et. Quisque vitae eros imperdiet, dictum orci lacinia, scelerisque est.</p>';
        $this->shout_text = [
            1 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. :D',
            2 => 'Aliquam ac rhoncus orci, a lacinia ante.',
            3 => 'Mauris nibh justo, luctus eu dapibus in, pellentesque non urna. Nulla ullamcorper varius lacus, ut finibus eros interdum id. :)',
            4 => 'Quisque vitae eros imperdiet, dictum orci lacinia, scelerisque est.',
            5 => 'Proin aliquam vulputate felis in viverra.'
        ];
        $this->users      = dbcount('(user_id)', DB_USERS, 'user_status = 0');
    }

    private function NumField($id) {
        $select = form_text('num_'.$id, $this->locale['CC_001'], 10, [
            'type'        => 'number',
            'number_min'  => 1,
            'number_max'  => 2000,
            'inline'      => TRUE,
            'class'       => 'm-b-0',
            'inner_class' => 'input-sm'
        ]);

        return $select;
    }

    private function Button($id, $delete = FALSE) {
        if ($delete == TRUE) {
            $button = form_button('delete_'.$id, $this->locale['delete'], $this->locale['delete'], ['class' => 'btn-sm btn-danger']);
        } else {
            $button = form_button('create_'.$id, $this->locale['CC_001'], $this->locale['CC_001'], ['class' => 'btn-sm btn-default']);
        }

        return $button;
    }

    private function RandromName() {
        $length     = 8;
        $name       = '';
        $characters = array_merge(range('A', 'Z'), range('a', 'z'), range('0', '9'));
        $max        = count($characters) - 1;

        for ($i = 0; $i < $length; $i++) {
            $rand = mt_rand(0, $max);
            $name .= $characters[$rand];
        }

        return $name;
    }

    private function MakeIP() {
        $num1 = mt_rand(0, 255);
        $num2 = mt_rand(0, 255);
        $num3 = mt_rand(0, 255);
        $num4 = mt_rand(0, 255);

        $ip = $num1.'.'.$num2.'.'.$num3.'.'.$num4;

        return $ip;
    }

    private function Notice($num, $delete = FALSE) {
        if ($delete == TRUE) {
            addNotice('success', $this->locale['CC_002']);
        } else {
            addNotice('success', $this->locale['CC_003'].' ('.$num.')');
        }

        redirect(FUSION_REQUEST);
    }

    private function Query($table, $insert, $values) {
        dbquery("INSERT INTO  ".$table." (".$insert.") VALUES ".$values);
    }

    private function Delete($table) {
        dbquery("TRUNCATE TABLE ".$table);
    }

    private function Users() {
        $admin          = !isset($_POST['create_admins']) ? TRUE : FALSE;
        $mailnames      = ['gmail.com', 'hotmail.com', 'yahoo.com', 'outlook.com', 'yandex.com'];
        $password       = '8a724b7684e0254527cf990012e93b6ec988e71a612419da0938a78e096c79be'; // test123456
        $salt           = '2038a428a612fef1930f9cbfc34ac617931d9ac5';
        $passworda      = '116c3754c28c691f4c7769487fd41a2f9e6b85a41034cc84533c9a2923267fd1'; // test123456789
        $admin_salt     = $admin ? '' : '0d406b98c9e42c0223754fce4d8150a5f70f4d17';
        $user_level     = $admin ? USER_LEVEL_MEMBER : USER_LEVEL_ADMIN;
        $admin_password = $admin ? '' : $passworda;
        $rights         = 'A.BLOG.D.FQ.F.PH.IM.N.PO.W.B.C.M.UG.BB.SM.LANG.S2.S9.S';
        $rights         = $admin ? '' : $rights;
        $algo           = 'sha256';

        $query = "INSERT INTO ".DB_USERS." (user_name, user_algo, user_salt, user_password, user_admin_algo, user_admin_salt, user_admin_password, user_email, user_hide_email, user_joined, user_lastvisit, user_ip, user_ip_type, user_rights, user_level) VALUES ";

        if (isset($_POST['create_users']) || isset($_POST['create_admins'])) {
            $num_users  = $_POST['num_users'];
            $num_admins = $_POST['num_admins'];
            $num        = $admin ? $num_users : $num_admins;

            for ($i = 1; $i <= $num; $i++) {
                $username  = $this->RandromName();
                $ip        = $this->MakeIP();
                $ii        = rand(1, 4);
                $mail      = strtolower($username.'@'.$mailnames[$ii]);
                $joined_   = rand(0, (time() / 2));
                $joined    = time() - $joined_;
                $lastvisit = time() - rand(0, $joined_);

                $query .= "('".$username."', '".$algo."', '".$salt."', '".$password."', '".$algo."', '".$admin_salt."', '".$admin_password."', '".$mail."', 0, '".$joined."', '".$lastvisit."', '".$ip."', 4, '".$rights."', '".$user_level."')";
                $query .= $i < $num ? ', ' : ';';
            }

            dbquery($query);

            $this->Notice($num);
        }

        if (isset($_POST['delete_users'])) {
            dbquery("DELETE FROM ".DB_USERS." WHERE user_id != 1 AND user_level = ".USER_LEVEL_MEMBER."");
            $this->Notice('', TRUE);
        }

        if (isset($_POST['delete_admins'])) {
            dbquery("DELETE FROM ".DB_USERS." WHERE user_id != 1 AND user_level = ".USER_LEVEL_ADMIN."");
            $this->Notice('', TRUE);
        }
    }

    private function UserGroups() {
        if (isset($_POST['create_user_groups'])) {
            $num    = $_POST['num_user_groups'];
            $insert = 'group_name, group_description';
            $values = '';

            for ($i = 1; $i <= $num; $i++) {
                $values .= "('".$this->locale['CC_006']." ".$i."', '".$this->locale['CC_007']."')";
                $values .= $i < $num ? ', ' : ';';
            }

            $this->Query(DB_USER_GROUPS, $insert, $values);
            $this->Notice($num);
        }

        if (isset($_POST['delete_user_groups'])) {
            $this->Delete(DB_USER_GROUPS);
            $this->Notice('', TRUE);
        }
    }

    private function Articles() {
        if (isset($_POST['create_article_cats'])) {
            $num    = $_POST['num_article_cats'];
            $insert = 'article_cat_parent, article_cat_name, article_cat_description, article_cat_visibility, article_cat_status, article_cat_language';
            $values = '';

            for ($i = 1; $i <= $num; $i++) {
                $values .= "(0, '".$this->locale['CC_009']." ".$i."', '".$this->locale['CC_007']."', 0, 1, '".LANGUAGE."')";
                $values .= $i < $num ? ', ' : ';';
            }

            $this->Query(DB_ARTICLE_CATS, $insert, $values);
            $this->Notice($num);
        }

        if (isset($_POST['create_articles'])) {
            $num    = $_POST['num_articles'];
            $insert = 'article_subject, article_cat, article_snippet, article_article, article_breaks, article_name, article_datestamp, article_reads, article_allow_comments, article_allow_ratings, article_language';
            $values = '';

            for ($i = 1; $i <= $num; $i++) {
                $article_cats = dbcount('(article_cat_id)', DB_ARTICLE_CATS);
                $article_cats = rand(1, $article_cats);
                $values .= "('".$this->locale['CC_010']." ".$i."', ".$article_cats.", '".$this->snippet."', '".$this->body."', 'y', 1, '".(time()-rand(0, time()/2))."', '".rand(1, 10000)."', 1, 1, '".LANGUAGE."')";
                $values .= $i < $num ? ', ' : ';';
            }

            $this->Query(DB_ARTICLES, $insert, $values);
            $this->Notice($num);
        }

        if (isset($_POST['delete_article_cats'])) {
            $this->Delete(DB_ARTICLE_CATS);
            $this->Notice('', TRUE);
        }

        if (isset($_POST['delete_articles'])) {
            $this->Delete(DB_ARTICLES);
            $this->Notice('', TRUE);
        }
    }

    private function Blogs() {
        if (isset($_POST['create_blog_cats'])) {
            $num    = $_POST['num_blog_cats'];
            $insert = 'blog_cat_parent, blog_cat_name, blog_cat_language';
            $values = '';

            for ($i = 1; $i <= $num; $i++) {
                $values .= "(0, '".$this->locale['CC_009']." ".$i."', '".LANGUAGE."')";
                $values .= $i < $num ? ', ' : ';';
            }

            $this->Query(DB_BLOG_CATS, $insert, $values);
            $this->Notice($num);
        }

        if (isset($_POST['create_blogs'])) {
            $num    = $_POST['num_blogs'];
            $insert = 'blog_subject, blog_cat, blog_blog, blog_extended, blog_breaks, blog_name, blog_datestamp, blog_reads, blog_allow_comments, blog_allow_ratings, blog_language';
            $values = '';

            for ($i = 1; $i <= $num; $i++) {
                $blog_cats = dbcount('(blog_cat_id)', DB_BLOG_CATS);
                $blog_cats = rand(1, $blog_cats);
                $values .= "('".$this->locale['CC_013']." ".$i."', ".$blog_cats.", '".$this->snippet."', '".$this->body."', 'y', 1, '".(time()-rand(0, time()/2))."', '".rand(1, 10000)."', 1, 1, '".LANGUAGE."')";
                $values .= $i < $num ? ', ' : ';';
            }

            $this->Query(DB_BLOG, $insert, $values);
            $this->Notice($num);
        }

        if (isset($_POST['delete_blog_cats'])) {
            $this->Delete(DB_BLOG_CATS);
            $this->Notice('', TRUE);
        }

        if (isset($_POST['delete_blogs'])) {
            $this->Delete(DB_BLOG);
            $this->Notice('', TRUE);
        }
    }

    private function CustomPages() {
        if (isset($_POST['create_custom_pages'])) {
            $num = $_POST['num_custom_pages'];

            $insert = 'page_title, page_access, page_content, page_status, page_user, page_datestamp, page_language';

            $values = '';
            for ($i = 1; $i <= $num; $i++) {
                $values .= "('".$this->locale['CC_016']." ".$i."', 0, '".$this->body."', 1, 1, '".(time()-rand(0, time()/2))."', '".LANGUAGE."')";
                $values .= $i < $num ? ', ' : ';';
            }

            $this->Query(DB_CUSTOM_PAGES, $insert, $values);
            $this->Notice($num);
        }


        if (isset($_POST['delete_custom_pages'])) {
            $this->Delete(DB_CUSTOM_PAGES);
            $this->Notice('', TRUE);
        }
    }

    private function Downloads() {
        if (isset($_POST['create_download_cats'])) {
            $num    = $_POST['num_download_cats'];
            $insert = 'download_cat_parent, download_cat_name, download_cat_description, download_cat_sorting, download_cat_language';
            $values = '';

            for ($i = 1; $i <= $num; $i++) {
                $values .= "(0, '".$this->locale['CC_009']." ".$i."', '".$this->locale['CC_007']."', 'download_id ASC', '".LANGUAGE."')";
                $values .= $i < $num ? ', ' : ';';
            }

            $this->Query(DB_DOWNLOAD_CATS, $insert, $values);
            $this->Notice($num);
        }

        if (isset($_POST['create_downloads'])) {
            $num    = $_POST['num_downloads'];
            $insert = 'download_user, download_title, download_description_short, download_description, download_url, download_cat, download_datestamp, download_visibility, download_count, download_allow_comments, download_allow_ratings';
            $values = '';

            for ($i = 1; $i <= $num; $i++) {
                $download_cats = dbcount('(download_cat_id)', DB_DOWNLOAD_CATS);
                $download_cats = rand(1, $download_cats);
                $values .= "(1, '".$this->locale['CC_018']." ".$i."', '".$this->short_text."', '".$this->body."', 'https://www.php-fusion.co.uk/home.php', ".$download_cats.", '".(time()-rand(0, time()/2))."', 0, ".rand(1, 10000).", 1, 0)";
                $values .= $i < $num ? ', ' : ';';
            }

            $this->Query(DB_DOWNLOADS, $insert, $values);
            $this->Notice($num);
        }

        if (isset($_POST['delete_download_cats'])) {
            $this->Delete(DB_DOWNLOAD_CATS);
            $this->Notice('', TRUE);
        }

        if (isset($_POST['delete_downloads'])) {
            $this->Delete(DB_DOWNLOADS);
            $this->Notice('', TRUE);
        }
    }

    private function Faqs() {
        if (isset($_POST['create_faq_cats'])) {
            $num    = $_POST['num_faq_cats'];
            $insert = 'faq_cat_name, faq_cat_description, faq_cat_language';
            $values = '';

            for ($i = 1; $i <= $num; $i++) {
                $values .= "('".$this->locale['CC_009']." ".$i."', '".$this->locale['CC_007']."', '".LANGUAGE."')";
                $values .= $i < $num ? ', ' : ';';
            }

            $this->Query(DB_FAQ_CATS, $insert, $values);
            $this->Notice($num);
        }

        if (isset($_POST['create_faqs'])) {
            $num    = $_POST['num_faqs'];
            $insert = 'faq_cat_id, faq_question, faq_answer, faq_breaks, faq_name, faq_datestamp, faq_visibility, faq_status, faq_language';
            $values = '';

            for ($i = 1; $i <= $num; $i++) {
                $faq_cats = dbcount('(faq_cat_id)', DB_FAQ_CATS);
                $faq_cats = rand(1, $faq_cats);
                $values .= "(".$faq_cats.", '".$this->locale['CC_021']." ".$i."', '".$this->short_text."', 'y', 1, '".(time()-rand(0, time()/2))."', 0, 1, '".LANGUAGE."')";
                $values .= $i < $num ? ', ' : ';';
            }

            $this->Query(DB_FAQS, $insert, $values);
            $this->Notice($num);
        }

        if (isset($_POST['delete_faq_cats'])) {
            $this->Delete(DB_FAQ_CATS);
            $this->Notice('', TRUE);
        }

        if (isset($_POST['delete_faqs'])) {
            $this->Delete(DB_FAQS);
            $this->Notice('', TRUE);
        }
    }

    private function News() {
        if (isset($_POST['create_news_cats'])) {
            $num    = $_POST['num_news_cats'];
            $insert = 'news_cat_parent, news_cat_name, news_cat_visibility, news_cat_language';
            $values = '';

            for ($i = 1; $i <= $num; $i++) {
                $values .= "(0, '".$this->locale['CC_009']." ".$i."', 0, '".LANGUAGE."')";
                $values .= $i < $num ? ', ' : ';';
            }

            $this->Query(DB_NEWS_CATS, $insert, $values);
            $this->Notice($num);
        }

        if (isset($_POST['create_news'])) {
            $num    = $_POST['num_news'];
            $insert = 'news_subject, news_cat, news_news, news_extended, news_breaks, news_name, news_datestamp, news_visibility, news_reads, news_allow_comments, news_allow_ratings, news_language';
            $values = '';

            for ($i = 1; $i <= $num; $i++) {
                $news_cats = dbcount('(news_cat_id)', DB_NEWS_CATS);
                $news_cats = rand(1, $news_cats);
                $values .= "('".$this->locale['CC_024']." ".$i."', ".$news_cats.", '".$this->snippet."', '".$this->body."', 'y', 1, '".(time()-rand(0, time()/2))."', 0, ".rand(1, 10000).", 1, 1, '".LANGUAGE."')";
                $values .= $i < $num ? ', ' : ';';
            }

            $this->Query(DB_NEWS, $insert, $values);
            $this->Notice($num);
        }

        if (isset($_POST['delete_news_cats'])) {
            $this->Delete(DB_NEWS_CATS);
            $this->Notice('', TRUE);
        }

        if (isset($_POST['delete_news'])) {
            $this->Delete(DB_NEWS);
            $this->Notice('', TRUE);
        }
    }

    private function Polls() {
        if (isset($_POST['create_polls'])) {
            $num    = $_POST['num_polls'];
            $insert = 'poll_title, poll_opt, poll_started, poll_ended, poll_visibility';
            $values = '';

            for ($i = 1; $i <= $num; $i++) {
                $values .= "('".serialize([LANGUAGE => $this->locale['CC_027'].' '.$i])."', '".serialize([[LANGUAGE => $this->locale['CC_028']], [LANGUAGE => $this->locale['CC_029']]])."', '".(time()-rand(0, time()/2))."', 0, 0)";
                $values .= $i < $num ? ', ' : ';';
            }

            $this->Query(DB_POLLS, $insert, $values);
            $this->Notice($num);
        }

        if (isset($_POST['delete_polls'])) {
            $this->Delete(DB_POLLS);
            $this->Notice('', TRUE);
        }
    }

    private function Shouts() {
        if (isset($_POST['create_shouts'])) {
            $num    = $_POST['num_shouts'];
            $insert = 'shout_name, shout_message, shout_datestamp, shout_ip, shout_ip_type, shout_hidden, shout_language';
            $values = '';

            for ($i = 1; $i <= $num; $i++) {
                $values .= "(".rand(1, $this->users).", '".$this->shout_text[rand(1, 5)]."', '".(time()-rand(0, time()/2))."', '".$this->MakeIP()."', 4, 0, '".LANGUAGE."')";
                $values .= $i < $num ? ', ' : ';';
            }

            $this->Query(DB_SHOUTBOX, $insert, $values);
            $this->Notice($num);
        }

        if (isset($_POST['delete_shouts'])) {
            $this->Delete(DB_SHOUTBOX);
            $this->Notice('', TRUE);
        }
    }

    private function Weblinks() {
        if (isset($_POST['create_weblink_cats'])) {
            $num    = $_POST['num_weblink_cats'];
            $insert = 'weblink_cat_parent, weblink_cat_name, weblink_cat_description, weblink_cat_status, weblink_cat_visibility, weblink_cat_language';
            $values = '';

            for ($i = 1; $i <= $num; $i++) {
                $values .= "(0, '".$this->locale['CC_009']." ".$i."', '".$this->locale['CC_007']."', 1, 0, '".LANGUAGE."')";
                $values .= $i < $num ? ', ' : ';';
            }

            $this->Query(DB_WEBLINK_CATS, $insert, $values);
            $this->Notice($num);
        }

        if (isset($_POST['create_weblinks'])) {
            $num    = $_POST['num_weblinks'];
            $insert = 'weblink_name, weblink_description, weblink_url, weblink_cat, weblink_datestamp, weblink_visibility, weblink_status, weblink_count, weblink_language';
            $values = '';

            for ($i = 1; $i <= $num; $i++) {
                $weblink_cats = dbcount('(weblink_cat_id)', DB_WEBLINK_CATS);
                $weblink_cats = rand(1, $weblink_cats);
                $values .= "('".$this->locale['CC_033']." ".$i."', '".$this->locale['CC_007']."', 'http://".strtolower($this->RandromName()).".com', ".$weblink_cats.", '".(time()-rand(0, time()/2))."', 0, 1, ".rand(1, 10000).", '".LANGUAGE."')";
                $values .= $i < $num ? ', ' : ';';
            }

            $this->Query(DB_WEBLINKS, $insert, $values);
            $this->Notice($num);
        }

        if (isset($_POST['delete_weblink_cats'])) {
            $this->Delete(DB_WEBLINK_CATS);
            $this->Notice('', TRUE);
        }

        if (isset($_POST['delete_weblinks'])) {
            $this->Delete(DB_WEBLINKS);
            $this->Notice('', TRUE);
        }
    }

    public function DisplayAdmin() {
        opentable($this->locale['CC_title']);

        echo '<div class="well">';
            echo '<strong class="text-danger">'.$this->locale['CC_037'].'</strong><br />';
            echo $this->locale['CC_038'].'<br />';
            echo $this->locale['CC_039'].': <strong>test123456</strong><br />';
            echo $this->locale['CC_040'].': <strong> test123456789</strong>';
        echo '</div>';

        echo openform('content', 'post', FUSION_REQUEST);
        echo '<table class="table table-striped">';
            echo '<tbody>';
                $this->Users();
                $total_users = dbcount('(user_id)', DB_USERS, 'user_status=0');
                echo '<tr><td colspan="4" class="text-center strong">Total Users: '.$total_users.'</td></tr>';
                echo '<tr>';
                    echo '<td>'.$this->NumField('users').'</td>';
                    echo '<td>'.$this->Button('users').'</td>';
                    $users = dbcount('(user_id)', DB_USERS, 'user_status=0 AND user_level='.USER_LEVEL_MEMBER.'');
                    echo '<td>'.$this->locale['CC_004'].': '.$users.'</td>';
                    echo '<td>'.$this->Button('users', TRUE).'</td>';
                echo '</tr>';
                echo '<tr>';
                    echo '<td>'.$this->NumField('admins').'</td>';
                    echo '<td>'.$this->Button('admins').'</td>';
                    $admins = dbcount('(user_id)', DB_USERS, 'user_status=0 AND user_level='.USER_LEVEL_ADMIN.' OR user_level='.USER_LEVEL_SUPER_ADMIN.'');
                    echo '<td>'.$this->locale['CC_005'].': '.$admins.'</td>';
                    echo '<td>'.$this->Button('admins', TRUE).'</td>';
                echo '</tr>';
                echo '<tr>';
                    $this->UserGroups();
                    echo '<td>'.$this->NumField('user_groups').'</td>';
                    echo '<td>'.$this->Button('user_groups').'</td>';
                    $user_groups = dbcount('(group_id)', DB_USER_GROUPS);
                    echo '<td>'.$this->locale['CC_008'].': '.$user_groups.'</td>';
                    echo '<td>'.$this->Button('user_groups', TRUE).'</td>';
                echo '</tr>';

                $articles = function_exists('infusion_exists') ? infusion_exists('articles') : db_exists(DB_PREFIX.'articles');
                if ($articles) {
                    $this->Articles();

                    echo '<tr><td colspan="4" class="text-center strong">'.$this->locale['CC_012'].'</td></tr>';
                    echo '<tr>';
                        echo '<td>'.$this->NumField('article_cats').'</td>';
                        echo '<td>'.$this->Button('article_cats').'</td>';
                        $article_cats = dbcount('(article_cat_id)', DB_ARTICLE_CATS);
                        echo '<td>'.$this->locale['CC_011'].': '.$article_cats.'</td>';
                        echo '<td>'.$this->Button('article_cats', TRUE).'</td>';
                    echo '</tr>';

                    if (!empty($article_cats)) {
                        echo '<tr>';
                            echo '<td>'.$this->NumField('articles').'</td>';
                            echo '<td>'.$this->Button('articles').'</td>';
                            $articles = dbcount('(article_id)', DB_ARTICLES);
                            echo '<td>'.$this->locale['CC_012'].': '.$articles.'</td>';
                            echo '<td>'.$this->Button('articles', TRUE).'</td>';
                        echo '</tr>';
                    } else {
                        echo '<tr><td colspan="4" class="text-center">'.sprintf($this->locale['CC_036'], $this->locale['CC_011']).'</td></tr>';
                    }
                }

                $blog = function_exists('infusion_exists') ? infusion_exists('blog') : db_exists(DB_PREFIX.'blog');
                if ($blog) {
                    $this->Blogs();

                    echo '<tr><td colspan="4" class="text-center strong">'.$this->locale['CC_015'].'</td></tr>';
                    echo '<tr>';
                        echo '<td>'.$this->NumField('blog_cats').'</td>';
                        echo '<td>'.$this->Button('blog_cats').'</td>';
                        $blog_cats = dbcount('(blog_cat_id)', DB_BLOG_CATS);
                        echo '<td>'.$this->locale['CC_014'].': '.$blog_cats.'</td>';
                        echo '<td>'.$this->Button('blog_cats', TRUE).'</td>';
                    echo '</tr>';

                    if (!empty($blog_cats)) {
                        echo '<tr>';
                            echo '<td>'.$this->NumField('blogs').'</td>';
                            echo '<td>'.$this->Button('blogs').'</td>';
                            $blogs = dbcount('(blog_id)', DB_BLOG);
                            echo '<td>'.$this->locale['CC_015'].': '.$blogs.'</td>';
                            echo '<td>'.$this->Button('blogs', TRUE).'</td>';
                        echo '</tr>';
                    } else {
                        echo '<tr><td colspan="4" class="text-center">'.sprintf($this->locale['CC_036'], $this->locale['CC_014']).'</td></tr>';
                    }
                }

                echo '<tr><td colspan="4" class="text-center strong">'.$this->locale['CC_017'].'</td></tr>';
                echo '<tr>';
                    $this->CustomPages();
                    echo '<td>'.$this->NumField('custom_pages').'</td>';
                    echo '<td>'.$this->Button('custom_pages').'</td>';
                    $custom_pages = dbcount('(page_id)', DB_CUSTOM_PAGES);
                    echo '<td>'.$this->locale['CC_017'].': '.$custom_pages.'</td>';
                    echo '<td>'.$this->Button('custom_pages', TRUE).'</td>';
                echo '</tr>';

                $downloads = function_exists('infusion_exists') ? infusion_exists('downloads') : db_exists(DB_PREFIX.'downloads');
                if ($downloads) {
                    $this->Downloads();

                    echo '<tr><td colspan="4" class="text-center strong">'.$this->locale['CC_020'].'</td></tr>';
                    echo '<tr>';
                        echo '<td>'.$this->NumField('download_cats').'</td>';
                        echo '<td>'.$this->Button('download_cats').'</td>';
                        $download_cats = dbcount('(download_cat_id)', DB_DOWNLOAD_CATS);
                        echo '<td>'.$this->locale['CC_019'].': '.$download_cats.'</td>';
                        echo '<td>'.$this->Button('download_cats', TRUE).'</td>';
                    echo '</tr>';

                    if (!empty($download_cats)) {
                        echo '<tr>';
                            echo '<td>'.$this->NumField('downloads').'</td>';
                            echo '<td>'.$this->Button('downloads').'</td>';
                            $downloads = dbcount('(download_id)', DB_DOWNLOADS);
                            echo '<td>'.$this->locale['CC_020'].': '.$downloads.'</td>';
                            echo '<td>'.$this->Button('downloads', TRUE).'</td>';
                        echo '</tr>';
                    } else {
                        echo '<tr><td colspan="4" class="text-center">'.sprintf($this->locale['CC_036'], $this->locale['CC_019']).'</td></tr>';
                    }
                }

                $faqs = function_exists('infusion_exists') ? infusion_exists('faq') : db_exists(DB_PREFIX.'faqs');
                if ($faqs) {
                    $this->Faqs();

                    echo '<tr><td colspan="4" class="text-center strong">'.$this->locale['CC_023'].'</td></tr>';
                    echo '<tr>';
                        echo '<td>'.$this->NumField('faq_cats').'</td>';
                        echo '<td>'.$this->Button('faq_cats').'</td>';
                        $faq_cats = dbcount('(faq_cat_id)', DB_FAQ_CATS);
                        echo '<td>'.$this->locale['CC_022'].': '.$faq_cats.'</td>';
                        echo '<td>'.$this->Button('faq_cats', TRUE).'</td>';
                    echo '</tr>';

                    if (!empty($faq_cats)) {
                        echo '<tr>';
                            echo '<td>'.$this->NumField('faqs').'</td>';
                            echo '<td>'.$this->Button('faqs').'</td>';
                            $faqs = dbcount('(faq_id)', DB_FAQS);
                            echo '<td>'.$this->locale['CC_023'].': '.$faqs.'</td>';
                            echo '<td>'.$this->Button('faqs', TRUE).'</td>';
                        echo '</tr>';
                    } else {
                        echo '<tr><td colspan="4" class="text-center">'.sprintf($this->locale['CC_036'], $this->locale['CC_022']).'</td></tr>';
                    }
                }

                $news = function_exists('infusion_exists') ? infusion_exists('news') : db_exists(DB_PREFIX.'news');
                if ($news) {
                    $this->News();

                    echo '<tr><td colspan="4" class="text-center strong">'.$this->locale['CC_026'].'</td></tr>';
                    echo '<tr>';
                        echo '<td>'.$this->NumField('news_cats').'</td>';
                        echo '<td>'.$this->Button('news_cats').'</td>';
                        $news_cats = dbcount('(news_cat_id)', DB_NEWS_CATS);
                        echo '<td>'.$this->locale['CC_025'].': '.$news_cats.'</td>';
                        echo '<td>'.$this->Button('news_cats', TRUE).'</td>';
                    echo '</tr>';

                    if (!empty($news_cats)) {
                        echo '<tr>';
                            echo '<td>'.$this->NumField('news').'</td>';
                            echo '<td>'.$this->Button('news').'</td>';
                            $news = dbcount('(news_id)', DB_NEWS);
                            echo '<td>'.$this->locale['CC_026'].': '.$news.'</td>';
                            echo '<td>'.$this->Button('news', TRUE).'</td>';
                        echo '</tr>';
                    } else {
                        echo '<tr><td colspan="4" class="text-center">'.sprintf($this->locale['CC_036'], $this->locale['CC_025']).'</td></tr>';
                    }
                }

                $polls = function_exists('infusion_exists') ? infusion_exists('member_poll_panel') : db_exists(DB_PREFIX.'polls');
                if ($polls) {
                    $this->Polls();

                    echo '<tr><td colspan="4" class="text-center strong">'.$this->locale['CC_030'].'</td></tr>';
                    echo '<tr>';
                        echo '<td>'.$this->NumField('polls').'</td>';
                        echo '<td>'.$this->Button('polls').'</td>';
                        $polls = dbcount('(poll_id)', DB_POLLS);
                        echo '<td>'.$this->locale['CC_030'].': '.$polls.'</td>';
                        echo '<td>'.$this->Button('polls', TRUE).'</td>';
                    echo '</tr>';
                }

                $shoutbox = function_exists('infusion_exists') ? infusion_exists('shoutbox_panel') : db_exists(DB_PREFIX.'shoutbox');
                if ($shoutbox) {
                    $this->Shouts();

                    echo '<tr><td colspan="4" class="text-center strong">'.$this->locale['CC_032'].'</td></tr>';
                    echo '<tr>';
                        echo '<td>'.$this->NumField('shouts').'</td>';
                        echo '<td>'.$this->Button('shouts').'</td>';
                        $shouts = dbcount('(shout_id)', DB_SHOUTBOX);
                        echo '<td>'.$this->locale['CC_031'].': '.$shouts.'</td>';
                        echo '<td>'.$this->Button('shouts', TRUE).'</td>';
                    echo '</tr>';
                }

                $weblinks = function_exists('infusion_exists') ? infusion_exists('weblinks') : db_exists(DB_PREFIX.'weblinks');
                if ($weblinks) {
                    $this->Weblinks();

                    echo '<tr><td colspan="4" class="text-center strong">'.$this->locale['CC_035'].'</td></tr>';
                    echo '<tr>';
                        echo '<td>'.$this->NumField('weblink_cats').'</td>';
                        echo '<td>'.$this->Button('weblink_cats').'</td>';
                        $weblink_cats = dbcount('(weblink_cat_id)', DB_WEBLINK_CATS);
                        echo '<td>'.$this->locale['CC_034'].': '.$weblink_cats.'</td>';
                        echo '<td>'.$this->Button('weblink_cats', TRUE).'</td>';
                    echo '</tr>';

                    if (!empty($weblink_cats)) {
                        echo '<tr>';
                            echo '<td>'.$this->NumField('weblinks').'</td>';
                            echo '<td>'.$this->Button('weblinks').'</td>';
                            $weblinks = dbcount('(weblink_id)', DB_WEBLINKS);
                            echo '<td>'.$this->locale['CC_035'].': '.$weblinks.'</td>';
                            echo '<td>'.$this->Button('weblinks', TRUE).'</td>';
                        echo '</tr>';
                    } else {
                        echo '<tr><td colspan="4" class="text-center">'.sprintf($this->locale['CC_036'], $this->locale['CC_034']).'</td></tr>';
                    }
                }
            echo '</tbody>';
        echo '</table>';
        echo closeform();
        closetable();
    }
}
