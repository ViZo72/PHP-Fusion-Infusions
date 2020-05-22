<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: content_creator/content_creator.php
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

pageAccess('CC');

class ContentCreator {
    private $locale;
    private $snippet;
    private $body;
    private $short_text ;
    private $shout_text;
    private $message_text;
    private $users;

    public function __construct() {
        $this->locale = fusion_get_locale('', CC_LOCALE);

        $this->snippet = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vestibulum aliquam felis nunc, in dignissim metus suscipit eget. Nunc scelerisque laoreet purus, in ullamcorper magna sagittis eget. Aliquam ac rhoncus orci, a lacinia ante. Integer sed erat ligula. Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia Curae; Fusce ullamcorper sapien mauris, et tempus mi tincidunt laoreet. Proin aliquam vulputate felis in viverra.';
        $this->body = $this->snippet."\n<p>Duis sed lorem vitae nibh sagittis tempus sed sed enim. Mauris egestas varius purus, a varius odio vehicula quis. Donec cursus interdum libero, et ornare tellus mattis vitae. Phasellus et ligula velit. Vivamus ac turpis dictum, congue metus facilisis, ultrices lorem. Cras imperdiet lacus in tincidunt pellentesque. Sed consectetur nunc vitae fringilla volutpat. Mauris nibh justo, luctus eu dapibus in, pellentesque non urna. Nulla ullamcorper varius lacus, ut finibus eros interdum id. Proin at pellentesque sapien. Integer imperdiet, sapien nec tristique laoreet, sapien lacus porta nunc, tincidunt cursus risus mauris id quam.</p>";
        $this->short_text = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vestibulum tempor aliquam nulla eu dapibus. Donec pulvinar porttitor urna, in ultrices dolor cursus et. Quisque vitae eros imperdiet, dictum orci lacinia, scelerisque est.';
        $this->shout_text = [
            1 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. :D',
            2 => 'Aliquam ac rhoncus orci, a lacinia ante.',
            3 => 'Mauris nibh justo, luctus eu dapibus in, pellentesque non urna. Nulla ullamcorper varius lacus, ut finibus eros interdum id. :)',
            4 => 'Quisque vitae eros imperdiet, dictum orci lacinia, scelerisque est.',
            5 => 'Proin aliquam vulputate felis in viverra.'
        ];
        $this->message_text = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Aliquam a tempus lectus, eu posuere ipsum. Etiam et odio laoreet quam cursus sollicitudin. Donec ac eros non mi lacinia volutpat quis ultrices odio.';

        $this->users = dbcount('(user_id)', DB_USERS, 'user_status = 0');
    }

    private function numField($id, $value = 20) {
        return form_text('num_'.$id, $this->locale['cc_001'], $value, [
            'type'        => 'number',
            'number_min'  => 1,
            'number_max'  => 2000,
            'inline'      => TRUE,
            'class'       => 'm-b-0',
            'inner_class' => 'input-sm'
        ]);
    }

    private function button($id, $delete = FALSE) {
        if ($delete == TRUE) {
            $button = form_button('delete_'.$id, $this->locale['delete'], $this->locale['delete'], ['class' => 'btn-sm btn-danger']);
        } else {
            $button = form_button('create_'.$id, $this->locale['cc_001'], $this->locale['cc_001'], ['class' => 'btn-sm btn-default']);
        }

        return $button;
    }

    private function randomName() {
        $length = 8;
        $name = '';
        $characters = array_merge(range('A', 'Z'), range('a', 'z'), range('0', '9'));
        $max = count($characters) - 1;

        for ($i = 0; $i < $length; $i++) {
            $rand = mt_rand(0, $max);
            $name .= $characters[$rand];
        }

        return $name;
    }

    private function randomIp() {
        $num1 = mt_rand(0, 255);
        $num2 = mt_rand(0, 255);
        $num3 = mt_rand(0, 255);
        $num4 = mt_rand(0, 255);

        return $num1.'.'.$num2.'.'.$num3.'.'.$num4;
    }

    private function notice($num, $delete = FALSE) {
        if ($delete == TRUE) {
            addNotice('success', $this->locale['cc_002']);
        } else {
            addNotice('success', $this->locale['cc_003'].' ('.$num.')');
        }

        redirect(FUSION_REQUEST);
    }

    private function query($table, $insert, $values) {
        dbquery("INSERT INTO  ".$table." (".$insert.") VALUES ".$values);
    }

    private function delete($table) {
        dbquery("TRUNCATE TABLE ".$table);
    }

    private function users() {
        $admin = !isset($_POST['create_admins']);
        $mailnames = ['gmail.com', 'hotmail.com', 'yahoo.com', 'outlook.com', 'yandex.com', 'protonmail.com', 'aol.com'];
        $password = '8a724b7684e0254527cf990012e93b6ec988e71a612419da0938a78e096c79be'; // test123456
        $salt = '2038a428a612fef1930f9cbfc34ac617931d9ac5';
        $passworda = '116c3754c28c691f4c7769487fd41a2f9e6b85a41034cc84533c9a2923267fd1'; // test123456789
        $admin_salt = $admin ? '' : '0d406b98c9e42c0223754fce4d8150a5f70f4d17';
        $user_level = $admin ? USER_LEVEL_MEMBER : USER_LEVEL_ADMIN;
        $admin_password = $admin ? '' : $passworda;
        $rights = 'A.BLOG.D.FQ.F.PH.IM.N.PO.W.B.C.M.UG.BB.SM.LANG.S2.S9.S';
        $rights = $admin ? '' : $rights;
        $algo = 'sha256';

        $query = "INSERT INTO ".DB_USERS." (user_name, user_algo, user_salt, user_password, user_admin_algo, user_admin_salt, user_admin_password, user_email, user_hide_email, user_joined, user_lastvisit, user_ip, user_ip_type, user_rights, user_level) VALUES ";

        if (isset($_POST['create_users']) || isset($_POST['create_admins'])) {
            $num_users = $_POST['num_users'];
            $num_admins = $_POST['num_admins'];
            $num = $admin ? $num_users : $num_admins;

            for ($i = 1; $i <= $num; $i++) {
                $username = $this->randomName();
                $ip = $this->randomIp();
                $mail = strtolower($username.'@'.$mailnames[rand(1, 6)]);
                $joined_rand = rand(0, (time() / 2));
                $joined = time() - $joined_rand;
                $lastvisit = time() - rand(0, $joined_rand);

                $query .= "('".$username."', '".$algo."', '".$salt."', '".$password."', '".$algo."', '".$admin_salt."', '".$admin_password."', '".$mail."', 0, '".$joined."', '".$lastvisit."', '".$ip."', 4, '".$rights."', '".$user_level."')";
                $query .= $i < $num ? ', ' : ';';
            }

            dbquery($query);

            $this->notice($num);
        }

        if (isset($_POST['delete_users'])) {
            dbquery("DELETE FROM ".DB_USERS." WHERE user_id != 1 AND user_level = ".USER_LEVEL_MEMBER."");
            $this->notice('', TRUE);
        }

        if (isset($_POST['delete_admins'])) {
            dbquery("DELETE FROM ".DB_USERS." WHERE user_id != 1 AND user_level = ".USER_LEVEL_ADMIN."");
            $this->notice('', TRUE);
        }
    }

    private function userGroups() {
        if (isset($_POST['create_user_groups'])) {
            $num = $_POST['num_user_groups'];
            $insert = 'group_name, group_description';
            $values = '';

            for ($i = 1; $i <= $num; $i++) {
                $values .= "('".$this->locale['cc_006']." ".$i."', '".$this->locale['cc_007']."')";
                $values .= $i < $num ? ', ' : ';';
            }

            $this->query(DB_USER_GROUPS, $insert, $values);
            $this->notice($num);
        }

        if (isset($_POST['delete_user_groups'])) {
            $this->delete(DB_USER_GROUPS);
            $this->notice('', TRUE);
        }
    }

    private function privateMessages() {
        if (isset($_POST['create_private_messages'])) {
            $num = $_POST['num_private_messages'];
            for ($i = 1; $i <= $num; $i++) {
                send_pm(rand(1, $this->users / 2), rand($this->users / 2, $this->users), $this->locale['cc_041'].' '.$i, $this->message_text);
            }

            $this->notice($num);
        }

        if (isset($_POST['delete_private_messages'])) {
            $this->delete(DB_MESSAGES);
            $this->notice('', TRUE);
        }
    }

    private function articles() {
        if (isset($_POST['create_article_cats'])) {
            $num = $_POST['num_article_cats'];
            $insert = 'article_cat_parent, article_cat_name, article_cat_description, article_cat_visibility, article_cat_status, article_cat_language';
            $values = '';

            for ($i = 1; $i <= $num; $i++) {
                $values .= "(0, '".$this->locale['cc_009']." ".$i."', '".$this->locale['cc_007']."', 0, 1, '".LANGUAGE."')";
                $values .= $i < $num ? ', ' : ';';
            }

            $this->query(DB_ARTICLE_CATS, $insert, $values);
            $this->notice($num);
        }

        if (isset($_POST['create_articles'])) {
            $num = $_POST['num_articles'];
            $insert = 'article_subject, article_cat, article_snippet, article_article, article_breaks, article_name, article_datestamp, article_reads, article_allow_comments, article_allow_ratings, article_language';
            $values = '';

            for ($i = 1; $i <= $num; $i++) {
                $article_cats = dbcount('(article_cat_id)', DB_ARTICLE_CATS);
                $article_cats = rand(1, $article_cats);
                $values .= "('".$this->locale['cc_010']." ".$i."', ".$article_cats.", '".$this->snippet."', '".$this->body."', 'y', '".rand(1, $this->users)."', '".(time() - rand(0, time() / 2))."', '".rand(1, 10000)."', 1, 1, '".LANGUAGE."')";
                $values .= $i < $num ? ', ' : ';';
            }

            $this->query(DB_ARTICLES, $insert, $values);
            $this->notice($num);
        }

        if (isset($_POST['delete_article_cats'])) {
            $this->delete(DB_ARTICLE_CATS);
            $this->notice('', TRUE);
        }

        if (isset($_POST['delete_articles'])) {
            $this->delete(DB_ARTICLES);
            $this->notice('', TRUE);
        }
    }

    private function blog() {
        if (isset($_POST['create_blog_cats'])) {
            $num = $_POST['num_blog_cats'];
            $insert = 'blog_cat_parent, blog_cat_name, blog_cat_language';
            $values = '';

            for ($i = 1; $i <= $num; $i++) {
                $values .= "(0, '".$this->locale['cc_009']." ".$i."', '".LANGUAGE."')";
                $values .= $i < $num ? ', ' : ';';
            }

            $this->query(DB_BLOG_CATS, $insert, $values);
            $this->notice($num);
        }

        if (isset($_POST['create_blogs'])) {
            $num = $_POST['num_blogs'];
            $insert = 'blog_subject, blog_cat, blog_blog, blog_extended, blog_breaks, blog_name, blog_datestamp, blog_reads, blog_allow_comments, blog_allow_ratings, blog_language';
            $values = '';

            for ($i = 1; $i <= $num; $i++) {
                $blog_cats = dbcount('(blog_cat_id)', DB_BLOG_CATS);
                $blog_cats = rand(1, $blog_cats);
                $values .= "('".$this->locale['cc_013']." ".$i."', ".$blog_cats.", '".$this->snippet."', '".$this->body."', 'y', '".rand(1, $this->users)."', '".(time() - rand(0, time() / 2))."', '".rand(1, 10000)."', 1, 1, '".LANGUAGE."')";
                $values .= $i < $num ? ', ' : ';';
            }

            $this->query(DB_BLOG, $insert, $values);
            $this->notice($num);
        }

        if (isset($_POST['delete_blog_cats'])) {
            $this->delete(DB_BLOG_CATS);
            $this->notice('', TRUE);
        }

        if (isset($_POST['delete_blogs'])) {
            $this->delete(DB_BLOG);
            $this->notice('', TRUE);
        }
    }

    private function commentsAndRatings() {
        $type = [];
        $max_items = [];

        if (defined('ARTICLES_EXIST')) {
            $type[1] = 'A';
            $max_items[1] = dbcount('(article_id)', DB_ARTICLES);
        }

        if (defined('BLOG_EXIST')) {
            $type[2] = 'B';
            $max_items[2] = dbcount('(blog_id)', DB_BLOG);
        }

        if (defined('DOWNLOADS_EXIST')) {
            $type[3] = 'D';
            $max_items[3] = dbcount('(download_id)', DB_DOWNLOADS);
        }

        if (defined('GALLERY_EXIST')) {
            $type[4] = 'P';
            $max_items[4] = dbcount('(album_id)', DB_PHOTO_ALBUMS);
        }

        if (defined('NEWS_EXIST')) {
            $type[5] = 'N';
            $max_items[5] = dbcount('(news_id)', DB_NEWS);
        }

        if (defined('VIDEOS_EXIST')) {
            $type[6] = 'VID';
            $max_items[6] = dbcount('(video_id)', DB_VIDEOS);
        }

        if (isset($_POST['create_comments'])) {
            $num = $_POST['num_comments'];

            $insert = 'comment_item_id, comment_type, comment_name, comment_subject, comment_message, comment_datestamp, comment_ip, comment_hidden';

            $values = '';
            for ($i = 1; $i <= $num; $i++) {
                $values .= "('".rand(1, $max_items[rand(1, count($max_items))])."', '".$type[rand(1, count($type))]."', '".rand(1, $this->users)."', '".$this->locale['cc_048']." ".$i."', '".$this->shout_text[rand(1, 5)]."', '".(time() - rand(0, time() / 2))."', '".$this->randomIp()."', 0)";
                $values .= $i < $num ? ', ' : ';';
            }

            $this->query(DB_COMMENTS, $insert, $values);
            $this->notice($num);
        }


        if (isset($_POST['delete_ratings'])) {
            $this->delete(DB_COMMENTS);
            $this->notice('', TRUE);
        }

        if (isset($_POST['create_ratings'])) {
            $num = $_POST['num_ratings'];

            $insert = 'rating_item_id, rating_type, rating_user, rating_vote, rating_datestamp, rating_ip, rating_ip_type';

            $values = '';
            for ($i = 1; $i <= $num; $i++) {
                $values .= "('".rand(1, $max_items[rand(1, count($max_items))])."', '".$type[rand(1, count($type))]."', '".rand(1, $this->users)."', '".rand(1, 5)."', '".(time() - rand(0, time() / 2))."', '".$this->randomIp()."', 4)";
                $values .= $i < $num ? ', ' : ';';
            }

            $this->query(DB_RATINGS, $insert, $values);
            $this->notice($num);
        }


        if (isset($_POST['delete_ratings'])) {
            $this->delete(DB_RATINGS);
            $this->notice('', TRUE);
        }
    }

    private function customPages() {
        if (isset($_POST['create_custom_pages'])) {
            $num = $_POST['num_custom_pages'];

            $insert = 'page_title, page_access, page_content, page_status, page_user, page_datestamp, page_language';

            $values = '';
            for ($i = 1; $i <= $num; $i++) {
                $values .= "('".$this->locale['cc_016']." ".$i."', 0, '".$this->body."', 1, 1, '".(time() - rand(0, time() / 2))."', '".LANGUAGE."')";
                $values .= $i < $num ? ', ' : ';';
            }

            $this->query(DB_CUSTOM_PAGES, $insert, $values);
            $this->notice($num);
        }


        if (isset($_POST['delete_custom_pages'])) {
            $this->delete(DB_CUSTOM_PAGES);
            $this->notice('', TRUE);
        }
    }

    private function downloads() {
        if (isset($_POST['create_download_cats'])) {
            $num = $_POST['num_download_cats'];
            $insert = 'download_cat_parent, download_cat_name, download_cat_description, download_cat_sorting, download_cat_language';
            $values = '';

            for ($i = 1; $i <= $num; $i++) {
                $values .= "(0, '".$this->locale['cc_009']." ".$i."', '".$this->locale['cc_007']."', 'download_id ASC', '".LANGUAGE."')";
                $values .= $i < $num ? ', ' : ';';
            }

            $this->query(DB_DOWNLOAD_CATS, $insert, $values);
            $this->notice($num);
        }

        if (isset($_POST['create_downloads'])) {
            $num = $_POST['num_downloads'];
            $insert = 'download_user, download_title, download_description_short, download_description, download_url, download_cat, download_datestamp, download_visibility, download_count, download_allow_comments, download_allow_ratings';
            $values = '';

            for ($i = 1; $i <= $num; $i++) {
                $download_cats = dbcount('(download_cat_id)', DB_DOWNLOAD_CATS);
                $download_cats = rand(1, $download_cats);
                $values .= "('".rand(1, $this->users)."', '".$this->locale['cc_018']." ".$i."', '".$this->short_text."', '".$this->body."', 'https://www.php-fusion.co.uk/home.php', ".$download_cats.", '".(time() - rand(0, time() / 2))."', 0, ".rand(1, 10000).", 1, 1)";
                $values .= $i < $num ? ', ' : ';';
            }

            $this->query(DB_DOWNLOADS, $insert, $values);
            $this->notice($num);
        }

        if (isset($_POST['delete_download_cats'])) {
            $this->delete(DB_DOWNLOAD_CATS);
            $this->notice('', TRUE);
        }

        if (isset($_POST['delete_downloads'])) {
            $this->delete(DB_DOWNLOADS);
            $this->notice('', TRUE);
        }
    }

    private function faq() {
        if (isset($_POST['create_faq_cats'])) {
            $num = $_POST['num_faq_cats'];
            $insert = 'faq_cat_name, faq_cat_description, faq_cat_language';
            $values = '';

            for ($i = 1; $i <= $num; $i++) {
                $values .= "('".$this->locale['cc_009']." ".$i."', '".$this->locale['cc_007']."', '".LANGUAGE."')";
                $values .= $i < $num ? ', ' : ';';
            }

            $this->query(DB_FAQ_CATS, $insert, $values);
            $this->notice($num);
        }

        if (isset($_POST['create_faqs'])) {
            $num = $_POST['num_faqs'];
            $insert = 'faq_cat_id, faq_question, faq_answer, faq_breaks, faq_name, faq_datestamp, faq_visibility, faq_status, faq_language';
            $values = '';

            for ($i = 1; $i <= $num; $i++) {
                $faq_cats = dbcount('(faq_cat_id)', DB_FAQ_CATS);
                $faq_cats = rand(1, $faq_cats);
                $values .= "(".$faq_cats.", '".$this->locale['cc_021']." ".$i."', '".$this->short_text."', 'y', '".rand(1, $this->users)."', '".(time() - rand(0, time() / 2))."', 0, 1, '".LANGUAGE."')";
                $values .= $i < $num ? ', ' : ';';
            }

            $this->query(DB_FAQS, $insert, $values);
            $this->notice($num);
        }

        if (isset($_POST['delete_faq_cats'])) {
            $this->delete(DB_FAQ_CATS);
            $this->notice('', TRUE);
        }

        if (isset($_POST['delete_faqs'])) {
            $this->delete(DB_FAQS);
            $this->notice('', TRUE);
        }
    }

    private function forum() {
        if (isset($_POST['create_forums'])) {
            $num = $_POST['num_forums'];
            $insert = 'forum_name, forum_type, forum_description, forum_post, forum_reply, forum_language';
            $values = '';

            for ($i = 1; $i <= $num; $i++) {
                $type = rand(1, 4);
                $values .= "('".$this->locale['cc_044']." ".$i."', '".$type."', '".$this->locale['cc_007']."', '".USER_LEVEL_MEMBER."', '".USER_LEVEL_MEMBER."', '".LANGUAGE."')";
                $values .= $i < $num ? ', ' : ';';
            }

            $this->query(DB_FORUMS, $insert, $values);
            $this->notice($num);
        }

        if (isset($_POST['delete_forums'])) {
            $this->delete(DB_FORUMS);
            $this->notice('', TRUE);
        }
    }

    private function gallery() {
        if (isset($_POST['create_photo_albums'])) {
            $num = $_POST['num_photo_albums'];
            $insert = 'album_title, album_description, album_user, album_datestamp, album_language';
            $values = '';

            for ($i = 1; $i <= $num; $i++) {
                $values .= "('".$this->locale['cc_046']." ".$i."', '".$this->locale['cc_007']."', '".rand(1, $this->users)."', '".(time() - rand(0, time() / 2))."', '".LANGUAGE."')";
                $values .= $i < $num ? ', ' : ';';
            }

            $this->query(DB_PHOTO_ALBUMS, $insert, $values);
            $this->notice($num);
        }

        if (isset($_POST['delete_photo_albums'])) {
            $this->delete(DB_PHOTO_ALBUMS);
            $this->notice('', TRUE);
        }
    }

    private function news() {
        if (isset($_POST['create_news_cats'])) {
            $num = $_POST['num_news_cats'];
            $insert = 'news_cat_parent, news_cat_name, news_cat_visibility, news_cat_language';
            $values = '';

            for ($i = 1; $i <= $num; $i++) {
                $values .= "(0, '".$this->locale['cc_009']." ".$i."', 0, '".LANGUAGE."')";
                $values .= $i < $num ? ', ' : ';';
            }

            $this->query(DB_NEWS_CATS, $insert, $values);
            $this->notice($num);
        }

        if (isset($_POST['create_news'])) {
            $num = $_POST['num_news'];
            $insert = 'news_subject, news_cat, news_news, news_extended, news_breaks, news_name, news_datestamp, news_visibility, news_reads, news_allow_comments, news_allow_ratings, news_language';
            $values = '';

            for ($i = 1; $i <= $num; $i++) {
                $news_cats = dbcount('(news_cat_id)', DB_NEWS_CATS);
                $news_cats = rand(1, $news_cats);
                $values .= "('".$this->locale['cc_024']." ".$i."', ".$news_cats.", '".$this->snippet."', '".$this->body."', 'y', '".rand(1, $this->users)."', '".(time() - rand(0, time() / 2))."', 0, ".rand(1, 10000).", 1, 1, '".LANGUAGE."')";
                $values .= $i < $num ? ', ' : ';';
            }

            $this->query(DB_NEWS, $insert, $values);
            $this->notice($num);
        }

        if (isset($_POST['delete_news_cats'])) {
            $this->delete(DB_NEWS_CATS);
            $this->notice('', TRUE);
        }

        if (isset($_POST['delete_news'])) {
            $this->delete(DB_NEWS);
            $this->notice('', TRUE);
        }
    }

    private function polls() {
        if (isset($_POST['create_polls'])) {
            $num = $_POST['num_polls'];
            $insert = 'poll_title, poll_opt, poll_started, poll_ended, poll_visibility';
            $values = '';

            for ($i = 1; $i <= $num; $i++) {
                $values .= "('".serialize([LANGUAGE => $this->locale['cc_027'].' '.$i])."', '".serialize([[LANGUAGE => $this->locale['cc_028']], [LANGUAGE => $this->locale['cc_029']]])."', '".(time() - rand(0, time() / 2))."', 0, 0)";
                $values .= $i < $num ? ', ' : ';';
            }

            $this->query(DB_POLLS, $insert, $values);
            $this->notice($num);
        }

        if (isset($_POST['delete_polls'])) {
            $this->delete(DB_POLLS);
            $this->notice('', TRUE);
        }
    }

    private function shouts() {
        if (isset($_POST['create_shouts'])) {
            $num = $_POST['num_shouts'];
            $insert = 'shout_name, shout_message, shout_datestamp, shout_ip, shout_ip_type, shout_hidden, shout_language';
            $values = '';

            for ($i = 1; $i <= $num; $i++) {
                $values .= "('".rand(1, $this->users)."', '".$this->shout_text[rand(1, 5)]."', '".(time() - rand(0, time() / 2))."', '".$this->randomIp()."', 4, 0, '".LANGUAGE."')";
                $values .= $i < $num ? ', ' : ';';
            }

            $this->query(DB_SHOUTBOX, $insert, $values);
            $this->notice($num);
        }

        if (isset($_POST['delete_shouts'])) {
            $this->delete(DB_SHOUTBOX);
            $this->notice('', TRUE);
        }
    }

    private function videos() {
        if (isset($_POST['create_video_cats'])) {
            $num = $_POST['num_video_cats'];
            $insert = 'video_cat_parent, video_cat_name, video_cat_description, video_cat_sorting, video_cat_language';
            $values = '';

            for ($i = 1; $i <= $num; $i++) {
                $values .= "(0, '".$this->locale['cc_009']." ".$i."', '".$this->locale['cc_007']."', 'video_id ASC', '".LANGUAGE."')";
                $values .= $i < $num ? ', ' : ';';
            }

            $this->query(DB_VIDEO_CATS, $insert, $values);
            $this->notice($num);
        }

        if (isset($_POST['create_videos'])) {
            $num = $_POST['num_videos'];
            $insert = 'video_cat, video_user, video_title, video_description, video_length, video_datestamp, video_visibility, video_type, video_url, video_views, video_allow_comments, video_allow_ratings';
            $values = '';

            $video_urls = [
                1 => 'https://www.youtube.com/watch?v=C0DPdy98e4c',
                2 => 'https://www.youtube.com/watch?v=xcJtL7QggTI',
                3 => 'https://www.youtube.com/watch?v=2MpUj-Aua48',
            ];

            for ($i = 1; $i <= $num; $i++) {
                $video_cats = dbcount('(video_cat_id)', DB_VIDEO_CATS);
                $video_cats = rand(1, $video_cats);
                $values .= "(".$video_cats.", '".rand(1, $this->users)."', '".$this->locale['cc_050']." ".$i."', '".$this->body."', '".rand(0, 60).":".rand(0, 60)."', '".(time() - rand(0, time() / 2))."', 0, 'youtube', '".$video_urls[rand(1, 3)]."', ".rand(1, 10000).", 1, 1)";
                $values .= $i < $num ? ', ' : ';';
            }

            $this->query(DB_VIDEOS, $insert, $values);
            $this->notice($num);
        }

        if (isset($_POST['delete_video_cats'])) {
            $this->delete(DB_VIDEO_CATS);
            $this->notice('', TRUE);
        }

        if (isset($_POST['delete_videos'])) {
            $this->delete(DB_VIDEOS);
            $this->notice('', TRUE);
        }
    }

    private function weblinks() {
        if (isset($_POST['create_weblink_cats'])) {
            $num = $_POST['num_weblink_cats'];
            $insert = 'weblink_cat_parent, weblink_cat_name, weblink_cat_description, weblink_cat_status, weblink_cat_visibility, weblink_cat_language';
            $values = '';

            for ($i = 1; $i <= $num; $i++) {
                $values .= "(0, '".$this->locale['cc_009']." ".$i."', '".$this->locale['cc_007']."', 1, 0, '".LANGUAGE."')";
                $values .= $i < $num ? ', ' : ';';
            }

            $this->query(DB_WEBLINK_CATS, $insert, $values);
            $this->notice($num);
        }

        if (isset($_POST['create_weblinks'])) {
            $num = $_POST['num_weblinks'];
            $insert = 'weblink_name, weblink_description, weblink_url, weblink_cat, weblink_datestamp, weblink_visibility, weblink_status, weblink_count, weblink_language';
            $values = '';

            for ($i = 1; $i <= $num; $i++) {
                $weblink_cats = dbcount('(weblink_cat_id)', DB_WEBLINK_CATS);
                $weblink_cats = rand(1, $weblink_cats);
                $values .= "('".$this->locale['cc_033']." ".$i."', '".$this->locale['cc_007']."', 'http://".strtolower($this->randomName()).".com', ".$weblink_cats.", '".(time() - rand(0, time() / 2))."', 0, 1, ".rand(1, 10000).", '".LANGUAGE."')";
                $values .= $i < $num ? ', ' : ';';
            }

            $this->query(DB_WEBLINKS, $insert, $values);
            $this->notice($num);
        }

        if (isset($_POST['delete_weblink_cats'])) {
            $this->delete(DB_WEBLINK_CATS);
            $this->notice('', TRUE);
        }

        if (isset($_POST['delete_weblinks'])) {
            $this->delete(DB_WEBLINKS);
            $this->notice('', TRUE);
        }
    }

    public function displayAdmin() {
        add_to_title($this->locale['cc_title']);

        add_breadcrumb([
            'link'  => INFUSIONS.'content_creator/content_creator.php'.fusion_get_aidlink(),
            'title' => $this->locale['cc_title']
        ]);

        opentable($this->locale['cc_title']);

        echo '<div class="well">';
        echo '<strong class="text-danger">'.$this->locale['cc_037'].'</strong><br />';
        echo $this->locale['cc_038'].'<br />';
        echo $this->locale['cc_039'].': <strong>test123456</strong><br />';
        echo $this->locale['cc_040'].': <strong> test123456789</strong>';
        echo '</div>';

        echo openform('content', 'post', FUSION_REQUEST);

        echo '<div class="table-responsive"><table class="table table-striped">';
        echo '<tbody>';
        $this->users();
        $total_users = dbcount('(user_id)', DB_USERS, 'user_status=0');
        echo '<tr><td colspan="4" class="info text-center strong">'.$this->locale['cc_042'].': '.$total_users.'</td></tr>';
        echo '<tr>';
        echo '<td>'.$this->numField('users', 50).'</td>';
        echo '<td>'.$this->button('users').'</td>';
        $users = dbcount('(user_id)', DB_USERS, 'user_status=0 AND user_level='.USER_LEVEL_MEMBER.'');
        echo '<td>'.$this->locale['cc_004'].': '.$users.'</td>';
        echo '<td>'.$this->button('users', TRUE).'</td>';
        echo '</tr>';

        echo '<tr>';
        echo '<td>'.$this->numField('admins', 5).'</td>';
        echo '<td>'.$this->button('admins').'</td>';
        $admins = dbcount('(user_id)', DB_USERS, 'user_status=0 AND user_level='.USER_LEVEL_ADMIN.' OR user_level='.USER_LEVEL_SUPER_ADMIN.'');
        echo '<td>'.$this->locale['cc_005'].': '.$admins.'</td>';
        echo '<td>'.$this->button('admins', TRUE).'</td>';
        echo '</tr>';

        echo '<tr>';
        $this->userGroups();
        echo '<td>'.$this->numField('user_groups', 5).'</td>';
        echo '<td>'.$this->button('user_groups').'</td>';
        $user_groups = dbcount('(group_id)', DB_USER_GROUPS);
        echo '<td>'.$this->locale['cc_008'].': '.$user_groups.'</td>';
        echo '<td>'.$this->button('user_groups', TRUE).'</td>';
        echo '</tr>';

        echo '<tr>';
        $this->privateMessages();
        echo '<td>'.$this->numField('private_messages', 50).'</td>';
        echo '<td>'.$this->button('private_messages').'</td>';
        $private_messages = dbcount('(message_id)', DB_MESSAGES) / 2;
        echo '<td>'.$this->locale['cc_041'].': '.$private_messages.'</td>';
        echo '<td>'.$this->button('private_messages', TRUE).'</td>';
        echo '</tr>';

        if (defined('ARTICLES_EXIST')) {
            $this->articles();
            echo '<tr><td colspan="4" class="info text-center strong">'.$this->locale['cc_012'].'</td></tr>';
            echo '<tr>';
            echo '<td>'.$this->numField('article_cats', 5).'</td>';
            echo '<td>'.$this->button('article_cats').'</td>';
            $article_cats = dbcount('(article_cat_id)', DB_ARTICLE_CATS);
            echo '<td>'.$this->locale['cc_011'].': '.$article_cats.'</td>';
            echo '<td>'.$this->button('article_cats', TRUE).'</td>';
            echo '</tr>';
            if (!empty($article_cats)) {
                echo '<tr>';
                echo '<td>'.$this->numField('articles').'</td>';
                echo '<td>'.$this->button('articles').'</td>';
                $articles = dbcount('(article_id)', DB_ARTICLES);
                echo '<td>'.$this->locale['cc_012'].': '.$articles.'</td>';
                echo '<td>'.$this->button('articles', TRUE).'</td>';
                echo '</tr>';
            } else {
                echo '<tr><td colspan="4" class="warning text-center">'.sprintf($this->locale['cc_036'], $this->locale['cc_011']).'</td></tr>';
            }
        }

        if (defined('BLOG_EXIST')) {
            $this->blog();
            echo '<tr><td colspan="4" class="info text-center strong">'.$this->locale['cc_015'].'</td></tr>';
            echo '<tr>';
            echo '<td>'.$this->numField('blog_cats', 5).'</td>';
            echo '<td>'.$this->button('blog_cats').'</td>';
            $blog_cats = dbcount('(blog_cat_id)', DB_BLOG_CATS);
            echo '<td>'.$this->locale['cc_014'].': '.$blog_cats.'</td>';
            echo '<td>'.$this->button('blog_cats', TRUE).'</td>';
            echo '</tr>';
            if (!empty($blog_cats)) {
                echo '<tr>';
                echo '<td>'.$this->numField('blogs').'</td>';
                echo '<td>'.$this->button('blogs').'</td>';
                $blogs = dbcount('(blog_id)', DB_BLOG);
                echo '<td>'.$this->locale['cc_015'].': '.$blogs.'</td>';
                echo '<td>'.$this->button('blogs', TRUE).'</td>';
                echo '</tr>';
            } else {
                echo '<tr><td colspan="4" class="warning text-center">'.sprintf($this->locale['cc_036'], $this->locale['cc_014']).'</td></tr>';
            }
        }

        echo '<tr><td colspan="4" class="info text-center strong">'.$this->locale['cc_017'].'</td></tr>';
        echo '<tr>';
        $this->customPages();
        echo '<td>'.$this->numField('custom_pages', 5).'</td>';
        echo '<td>'.$this->button('custom_pages').'</td>';
        $custom_pages = dbcount('(page_id)', DB_CUSTOM_PAGES);
        echo '<td>'.$this->locale['cc_017'].': '.$custom_pages.'</td>';
        echo '<td>'.$this->button('custom_pages', TRUE).'</td>';
        echo '</tr>';

        if (defined('DOWNLOADS_EXIST')) {
            $this->downloads();
            echo '<tr><td colspan="4" class="info text-center strong">'.$this->locale['cc_020'].'</td></tr>';
            echo '<tr>';
            echo '<td>'.$this->numField('download_cats', 5).'</td>';
            echo '<td>'.$this->button('download_cats').'</td>';
            $download_cats = dbcount('(download_cat_id)', DB_DOWNLOAD_CATS);
            echo '<td>'.$this->locale['cc_019'].': '.$download_cats.'</td>';
            echo '<td>'.$this->button('download_cats', TRUE).'</td>';
            echo '</tr>';
            if (!empty($download_cats)) {
                echo '<tr>';
                echo '<td>'.$this->numField('downloads').'</td>';
                echo '<td>'.$this->button('downloads').'</td>';
                $downloads = dbcount('(download_id)', DB_DOWNLOADS);
                echo '<td>'.$this->locale['cc_020'].': '.$downloads.'</td>';
                echo '<td>'.$this->button('downloads', TRUE).'</td>';
                echo '</tr>';
            } else {
                echo '<tr><td colspan="4" class="warning text-center">'.sprintf($this->locale['cc_036'], $this->locale['cc_019']).'</td></tr>';
            }
        }

        if (defined('FAQ_EXIST')) {
            $this->faq();
            echo '<tr><td colspan="4" class="info text-center strong">'.$this->locale['cc_023'].'</td></tr>';
            echo '<tr>';
            echo '<td>'.$this->numField('faq_cats', 5).'</td>';
            echo '<td>'.$this->button('faq_cats').'</td>';
            $faq_cats = dbcount('(faq_cat_id)', DB_FAQ_CATS);
            echo '<td>'.$this->locale['cc_022'].': '.$faq_cats.'</td>';
            echo '<td>'.$this->button('faq_cats', TRUE).'</td>';
            echo '</tr>';
            if (!empty($faq_cats)) {
                echo '<tr>';
                echo '<td>'.$this->numField('faqs').'</td>';
                echo '<td>'.$this->button('faqs').'</td>';
                $faqs = dbcount('(faq_id)', DB_FAQS);
                echo '<td>'.$this->locale['cc_023'].': '.$faqs.'</td>';
                echo '<td>'.$this->button('faqs', TRUE).'</td>';
                echo '</tr>';
            } else {
                echo '<tr><td colspan="4" class="warning text-center">'.sprintf($this->locale['cc_036'], $this->locale['cc_022']).'</td></tr>';
            }
        }

        if (defined('FORUM_EXIST')) {
            $this->forum();
            echo '<tr><td colspan="4" class="info text-center strong">'.$this->locale['cc_043'].'</td></tr>';
            echo '<tr>';
            echo '<td>'.$this->numField('forums', 5).'</td>';
            echo '<td>'.$this->button('forums').'</td>';
            $forums = dbcount('(forum_id)', DB_FORUMS);
            echo '<td>'.$this->locale['cc_043'].': '.$forums.'</td>';
            echo '<td>'.$this->button('forums', TRUE).'</td>';
            echo '</tr>';
        }

        if (defined('GALLERY_EXIST')) {
            $this->gallery();
            echo '<tr><td colspan="4" class="info text-center strong">'.$this->locale['cc_045'].'</td></tr>';
            echo '<tr>';
            echo '<td>'.$this->numField('photo_albums', 5).'</td>';
            echo '<td>'.$this->button('photo_albums').'</td>';
            $photo_albums = dbcount('(album_id)', DB_PHOTO_ALBUMS);
            echo '<td>'.$this->locale['cc_045'].': '.$photo_albums.'</td>';
            echo '<td>'.$this->button('photo_albums', TRUE).'</td>';
            echo '</tr>';
        }

        if (defined('NEWS_EXIST')) {
            $this->news();
            echo '<tr><td colspan="4" class="info text-center strong">'.$this->locale['cc_026'].'</td></tr>';
            echo '<tr>';
            echo '<td>'.$this->numField('news_cats', 5).'</td>';
            echo '<td>'.$this->button('news_cats').'</td>';
            $news_cats = dbcount('(news_cat_id)', DB_NEWS_CATS);
            echo '<td>'.$this->locale['cc_025'].': '.$news_cats.'</td>';
            echo '<td>'.$this->button('news_cats', TRUE).'</td>';
            echo '</tr>';

            if (!empty($news_cats)) {
                echo '<tr>';
                echo '<td>'.$this->numField('news').'</td>';
                echo '<td>'.$this->button('news').'</td>';
                $news = dbcount('(news_id)', DB_NEWS);
                echo '<td>'.$this->locale['cc_026'].': '.$news.'</td>';
                echo '<td>'.$this->button('news', TRUE).'</td>';
                echo '</tr>';
            } else {
                echo '<tr><td colspan="4" class="warning text-center">'.sprintf($this->locale['cc_036'], $this->locale['cc_025']).'</td></tr>';
            }
        }

        if (defined('MEMBER_POLL_PANEL_EXIST')) {
            $this->polls();
            echo '<tr><td colspan="4" class="info text-center strong">'.$this->locale['cc_030'].'</td></tr>';
            echo '<tr>';
            echo '<td>'.$this->numField('polls', 5).'</td>';
            echo '<td>'.$this->button('polls').'</td>';
            $polls = dbcount('(poll_id)', DB_POLLS);
            echo '<td>'.$this->locale['cc_030'].': '.$polls.'</td>';
            echo '<td>'.$this->button('polls', TRUE).'</td>';
            echo '</tr>';
        }

        if (defined('SHOUTBOX_PANEL_EXIST')) {
            $this->shouts();
            echo '<tr><td colspan="4" class="info text-center strong">'.$this->locale['cc_032'].'</td></tr>';
            echo '<tr>';
            echo '<td>'.$this->numField('shouts').'</td>';
            echo '<td>'.$this->button('shouts').'</td>';
            $shouts = dbcount('(shout_id)', DB_SHOUTBOX);
            echo '<td>'.$this->locale['cc_031'].': '.$shouts.'</td>';
            echo '<td>'.$this->button('shouts', TRUE).'</td>';
            echo '</tr>';
        }

        if (defined('VIDEOS_EXIST')) {
            $this->videos();
            echo '<tr><td colspan="4" class="info text-center strong">'.$this->locale['cc_052'].'</td></tr>';
            echo '<tr>';
            echo '<td>'.$this->numField('video_cats', 5).'</td>';
            echo '<td>'.$this->button('video_cats').'</td>';
            $video_cats = dbcount('(video_cat_id)', DB_VIDEO_CATS);
            echo '<td>'.$this->locale['cc_051'].': '.$video_cats.'</td>';
            echo '<td>'.$this->button('video_cats', TRUE).'</td>';
            echo '</tr>';

            if (!empty($video_cats)) {
                echo '<tr>';
                echo '<td>'.$this->numField('videos').'</td>';
                echo '<td>'.$this->button('videos').'</td>';
                $videos = dbcount('(video_id)', DB_VIDEOS);
                echo '<td>'.$this->locale['cc_052'].': '.$videos.'</td>';
                echo '<td>'.$this->button('videos', TRUE).'</td>';
                echo '</tr>';
            } else {
                echo '<tr><td colspan="4" class="warning text-center">'.sprintf($this->locale['cc_036'], $this->locale['cc_051']).'</td></tr>';
            }
        }

        if (defined('WEBLINKS_EXIST')) {
            $this->weblinks();
            echo '<tr><td colspan="4" class="info text-center strong">'.$this->locale['cc_035'].'</td></tr>';
            echo '<tr>';
            echo '<td>'.$this->numField('weblink_cats', 5).'</td>';
            echo '<td>'.$this->button('weblink_cats').'</td>';
            $weblink_cats = dbcount('(weblink_cat_id)', DB_WEBLINK_CATS);
            echo '<td>'.$this->locale['cc_034'].': '.$weblink_cats.'</td>';
            echo '<td>'.$this->button('weblink_cats', TRUE).'</td>';
            echo '</tr>';

            if (!empty($weblink_cats)) {
                echo '<tr>';
                echo '<td>'.$this->numField('weblinks').'</td>';
                echo '<td>'.$this->button('weblinks').'</td>';
                $weblinks = dbcount('(weblink_id)', DB_WEBLINKS);
                echo '<td>'.$this->locale['cc_035'].': '.$weblinks.'</td>';
                echo '<td>'.$this->button('weblinks', TRUE).'</td>';
                echo '</tr>';
            } else {
                echo '<tr><td colspan="4" class="warning text-center">'.sprintf($this->locale['cc_036'], $this->locale['cc_034']).'</td></tr>';
            }
        }

        echo '<tr><td colspan="4" class="info text-center strong">'.$this->locale['cc_047'].' & '.$this->locale['cc_049'].'</td></tr>';
        $this->commentsAndRatings();
        echo '<tr>';
        echo '<td>'.$this->numField('comments', 50).'</td>';
        echo '<td>'.$this->button('comments').'</td>';
        $comments = dbcount('(comment_id)', DB_COMMENTS);
        echo '<td>'.$this->locale['cc_047'].': '.$comments.'</td>';
        echo '<td>'.$this->button('comments', TRUE).'</td>';
        echo '</tr>';

        echo '<tr>';
        echo '<td>'.$this->numField('ratings', 50).'</td>';
        echo '<td>'.$this->button('ratings').'</td>';
        $ratings = dbcount('(rating_id)', DB_RATINGS);
        echo '<td>'.$this->locale['cc_049'].': '.$ratings.'</td>';
        echo '<td>'.$this->button('ratings', TRUE).'</td>';
        echo '</tr>';

        echo '</tbody>';
        echo '</table></div>';

        echo closeform();
        closetable();
    }
}

$cc = new ContentCreator();
$cc->displayAdmin();

require_once THEMES.'templates/footer.php';
