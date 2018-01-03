<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: sitemap_panel/SitemapGenerator.php
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

require_once INFUSIONS.'sitemap_panel/Sitemap.php';

use \PHPFusion\BreadCrumbs;
use samdark\sitemap\Sitemap;


class SitemapGenerator {
    private $locale = [];
    private $siteurl = '';
    private $sitemap = '';
    public $sitemap_file = BASEDIR.'sitemap.xml';
    public $sitemap_settings = [];
    private $custom_links = [
        'link_id' => 0,
        'url'     => ''
    ];
    private $customlinks;
    private $profiles;
    private $articles;
    private $blogs;
    private $downloads;
    private $faqs;
    private $forum;
    private $gallery;
    private $news;
    private $weblinks;

    public function __construct() {
        $this->locale  = fusion_get_locale('', SMG_LOCALE);
        $this->siteurl = fusion_get_settings('siteurl');
        $this->sitemap = new Sitemap($this->sitemap_file);
        $this->sitemap_settings = get_settings('sitemap_panel');

        $this->customlinks = dbcount('(link_id)', DB_SITEMAP_LINKS) == 0 ? FALSE : TRUE;
        $this->profiles    = fusion_get_settings('hide_userprofiles') == 0 ? TRUE : FALSE;
        $this->articles    = function_exists('infusion_exists') ? infusion_exists('articles') : db_exists(DB_PREFIX.'articles');
        $this->blogs       = function_exists('infusion_exists') ? infusion_exists('blog') : db_exists(DB_PREFIX.'blog');
        $this->downloads   = function_exists('infusion_exists') ? infusion_exists('downloads') : db_exists(DB_PREFIX.'downloads');
        $this->faqs        = function_exists('infusion_exists') ? infusion_exists('faq') : db_exists(DB_PREFIX.'faqs');
        $this->forum       = function_exists('infusion_exists') ? infusion_exists('forum') : db_exists(DB_PREFIX.'forums');
        $this->gallery     = function_exists('infusion_exists') ? infusion_exists('gallery') : db_exists(DB_PREFIX.'photos');
        $this->news        = function_exists('infusion_exists') ? infusion_exists('news') : db_exists(DB_PREFIX.'news');
        $this->weblinks    = function_exists('infusion_exists') ? infusion_exists('weblinks') : db_exists(DB_PREFIX.'weblinks');
    }

    private function CustomLinks($options = []) {
        $result = dbquery("SELECT url FROM ".DB_SITEMAP_LINKS);

        if (dbrows($result) > 0) {
            while ($data = dbarray($result)) {
                $this->sitemap->addItem($data['url'], '', $options['frequency'], $options['priority']);
            }
        }
    }

    private function Profiels($options = []) {
        $result = dbquery("SELECT user_id, user_status
            FROM ".DB_USERS."
            WHERE user_status = 0
        ");

        while ($data = dbarray($result)) {
            $this->sitemap->addItem($this->siteurl.'profile.php?lookup='.$data['user_id'], '', $options['frequency'], $options['priority']);
        }
    }

    private function Articles($cats = FALSE, $base_links = TRUE, $options = []) {
        if ($base_links == TRUE) {
            $this->sitemap->addItem($this->siteurl.'infusions/articles/articles.php', '', $options['frequency'], $options['priority']);
            $this->sitemap->addItem($this->siteurl.'infusions/articles/articles.php?type=recent', '', $options['frequency'], $options['priority']);
            $this->sitemap->addItem($this->siteurl.'infusions/articles/articles.php?type=comment', '', $options['frequency'], $options['priority']);
            $this->sitemap->addItem($this->siteurl.'infusions/articles/articles.php?type=rating', '', $options['frequency'], $options['priority']);
        }

        if ($cats == TRUE) {
            $result = dbquery("SELECT article_cat_id, article_cat_status, article_cat_visibility, article_cat_language
                FROM ".DB_ARTICLE_CATS."
                WHERE article_cat_status = 1 AND ".groupaccess('article_cat_visibility')."
                ".(multilang_table('AR') ? " AND article_cat_language='".LANGUAGE."'" : '')."
                ORDER BY article_cat_id ASC
            ");

            if (dbrows($result) > 0) {
                while ($data = dbarray($result)) {
                    $this->sitemap->addItem($this->siteurl.'infusions/articles/articles.php?cat_id='.$data['article_cat_id'], '', $options['frequency_cat'], $options['priority_cat']);
                }
            }
        } else {
            require_once ARTICLE_CLASS.'autoloader.php';

            $items = \PHPFusion\Articles\ArticlesServer::Articles()->get_ArticleItems();

            foreach ($items['article_items'] as $id => $data) {
                $this->sitemap->addItem($this->siteurl.'infusions/articles/articles.php?article_id='.$data['article_id'], $data['article_datestamp'], $options['frequency'], $options['priority']);
            }
        }
    }

    private function Blogs($cats = FALSE, $base_links = TRUE, $options = []) {
        if ($base_links == TRUE) {
            $this->sitemap->addItem($this->siteurl.'infusions/blog/blog.php', '', $options['frequency'], $options['priority']);
            $this->sitemap->addItem($this->siteurl.'infusions/blog/blog.php?type=recent', '', $options['frequency'], $options['priority']);
            $this->sitemap->addItem($this->siteurl.'infusions/blog/blog.php?type=comment', '', $options['frequency'], $options['priority']);
            $this->sitemap->addItem($this->siteurl.'infusions/blog/blog.php?type=rating', '', $options['frequency'], $options['priority']);
        }

        if ($cats == TRUE) {
            $result = dbquery("SELECT blog_cat_id, blog_cat_language
                FROM ".DB_BLOG_CATS."
                ".(multilang_column('BL') ? "WHERE blog_cat_language='".LANGUAGE."'" : '')."
            ");

            if (dbrows($result) > 0) {
                while ($data = dbarray($result)) {
                    $this->sitemap->addItem($this->siteurl.'infusions/blog/blog.php?cat_id='.$data['blog_cat_id'], '', $options['frequency_cat'], $options['priority_cat']);
                }
            }

            $this->sitemap->addItem($this->siteurl.'infusions/blog/blog.php?cat_id=0', '', $options['frequency_cat'], $options['priority_cat']);
        } else {
            $result = dbquery("SELECT blog_id, blog_datestamp, blog_language, blog_visibility, blog_draft
                FROM ".DB_BLOG."
                ".(multilang_table('BL') ? "WHERE blog_language='".LANGUAGE."' AND" : 'WHERE')." ".groupaccess('blog_visibility')." AND blog_draft = 0
                ORDER BY blog_datestamp DESC
            ");

            if (dbrows($result) > 0) {
                while ($data = dbarray($result)) {
                    $this->sitemap->addItem($this->siteurl.'infusions/blog/blog.php?readmore='.$data['blog_id'], $data['blog_datestamp'], $options['frequency'], $options['priority']);
                }
            }
        }
    }

    private function CustomPages($options = []) {
        $result = dbquery("SELECT page_id, page_datestamp, page_language, page_status
            FROM ".DB_CUSTOM_PAGES."
            ".(multilang_table('CP') ? "WHERE page_language='".LANGUAGE."'" : 'WHERE')." AND page_status = 1
            ORDER BY page_datestamp DESC
        ");

        if (dbrows($result) > 0) {
            while ($data = dbarray($result)) {
                $this->sitemap->addItem($this->siteurl.'viewpage.php?page_id='.$data['page_id'], $data['page_datestamp'], $options['frequency'], $options['priority']);
            }
        }
    }

    private function Downloads($cats = FALSE, $base_links = TRUE, $options = []) {
        if ($base_links == TRUE) {
            $this->sitemap->addItem($this->siteurl.'infusions/downloads/downloads.php', '', $options['frequency'], $options['priority']);
            $this->sitemap->addItem($this->siteurl.'infusions/downloads/downloads.php?type=download', '', $options['frequency'], $options['priority']);
            $this->sitemap->addItem($this->siteurl.'infusions/downloads/downloads.php?type=recent', '',  $options['frequency'], $options['priority']);
            $this->sitemap->addItem($this->siteurl.'infusions/downloads/downloads.php?type=comments', '', $options['frequency'], $options['priority']);
            $this->sitemap->addItem($this->siteurl.'infusions/downloads/downloads.php?type=ratings', '', $options['frequency'], $options['priority']);
        }

        if ($cats == TRUE) {
            $result = dbquery("SELECT download_cat_id, download_cat_language
                FROM ".DB_DOWNLOAD_CATS."
                ".(multilang_table('DL') ? " WHERE download_cat_language='".LANGUAGE."'" : '')."
            ");

            if (dbrows($result) > 0) {
                while ($data = dbarray($result)) {
                    $this->sitemap->addItem($this->siteurl.'infusions/downloads/downloads.php?cat_id='.$data['download_cat_id'], '', $options['frequency_cat'], $options['priority_cat']);
                }
            }
        } else {
            $result = dbquery("SELECT download_id, download_datestamp, download_visibility
                FROM ".DB_DOWNLOADS."
                WHERE ".groupaccess('download_visibility')."
                ORDER BY download_datestamp DESC
            ");

            if (dbrows($result) > 0) {
                while ($data = dbarray($result)) {
                    $this->sitemap->addItem($this->siteurl.'infusions/downloads/downloads.php?download_id='.$data['download_id'], $data['download_datestamp'], $options['frequency'], $options['priority']);
                }
            }
        }
    }

    private function Faqs($options = []) {
        $this->sitemap->addItem($this->siteurl.'infusions/faq/faq.php', '', $options['frequency'], $options['priority']);

        $result = dbquery("SELECT faq_cat_id, faq_cat_language
            FROM ".DB_FAQ_CATS."
            ".(multilang_table('FQ') ? " WHERE faq_cat_language='".LANGUAGE."' " : '')."
        ");

        if (dbrows($result) > 0) {
            while ($data = dbarray($result)) {
                $this->sitemap->addItem($this->siteurl.'infusions/faq/faq.php?cat_id='.$data['faq_cat_id'], '', $options['frequency'], $options['priority']);
            }
        }

        $this->sitemap->addItem($this->siteurl.'infusions/faq/faq.php?cat_id=0', '', $options['frequency'], $options['priority']);
    }

    private function Forum($options = []) {
        $this->sitemap->addItem($this->siteurl.'infusions/forum/index.php', '', $options['frequency'], $options['priority']);
        $this->sitemap->addItem($this->siteurl.'infusions/forum/index.php?section=latest', '', $options['frequency'], $options['priority']);
        $this->sitemap->addItem($this->siteurl.'infusions/forum/index.php?section=unanswered', '', $options['frequency'], $options['priority']);
        $this->sitemap->addItem($this->siteurl.'infusions/forum/index.php?section=unsolved', '', $options['frequency'], $options['priority']);
        $this->sitemap->addItem($this->siteurl.'infusions/forum/tags.php', '', $options['frequency'], $options['priority']);

        $result_tags = dbquery("SELECT tag_id, tag_status, tag_language
            FROM ".DB_FORUM_TAGS."
            WHERE tag_status = 1
            ".(multilang_table('FO') ? "AND tag_language='".LANGUAGE."'" : '')."
        ");

        if (dbrows($result_tags) > 0) {
            while ($data = dbarray($result_tags)) {
                $this->sitemap->addItem($this->siteurl.'infusions/forum/tags.php?tag_id='.$data['tag_id'], '', $options['frequency'], $options['priority']);
            }
        }

        $result_forums = dbquery("SELECT forum_id, forum_access, forum_language
            FROM ".DB_FORUMS."
            ".(multilang_table('FO') ? " WHERE forum_language='".LANGUAGE."' AND " : ' WHERE ').groupaccess('forum_access')."
        ");

        if (dbrows($result_forums) > 0) {
            while ($data = dbarray($result_forums)) {
                $this->sitemap->addItem($this->siteurl.'infusions/forum/index.php?viewforum&forum_id='.$data['forum_id'], '', $options['frequency'], $options['priority']);
            }
        }

        $result_threads = dbquery("SELECT t.thread_id, t.thread_lastpost
            FROM ".DB_FORUM_THREADS." t
            INNER JOIN ".DB_FORUMS." f ON t.forum_id = f.forum_id
            WHERE ".groupaccess('forum_access')." AND t.thread_hidden = 0
        ");

        if (dbrows($result_threads) > 0) {
            while ($data = dbarray($result_threads)) {
                $this->sitemap->addItem($this->siteurl.'infusions/forum/viewthread.php?thread_id='.$data['thread_id'], $data['thread_lastpost'], $options['frequency'], $options['priority']);
            }
        }
    }

    private function Gallery($albums = FALSE, $base_links = TRUE, $options = []) {
        if ($base_links == TRUE) {
            $this->sitemap->addItem($this->siteurl.'infusions/gallery/gallery.php', '', $options['frequency'], $options['priority']);
        }

        if ($albums == TRUE) {
            $result = dbquery("SELECT album_id, album_access, album_datestamp
                FROM ".DB_PHOTO_ALBUMS."
                WHERE ".groupaccess('album_access')."
            ");

            if (dbrows($result) > 0) {
                while ($data = dbarray($result)) {
                    $this->sitemap->addItem($this->siteurl.'infusions/gallery/gallery.php?album_id='.$data['album_id'], $data['album_datestamp'], $options['frequency_alb'], $options['priority_alb']);
                }
            }
        } else {
            $result = dbquery("SELECT p.photo_id, p.album_id, p.photo_datestamp, a.album_access
                FROM ".DB_PHOTOS." p
                LEFT JOIN ".DB_PHOTO_ALBUMS." a ON p.album_id = a.album_id
                WHERE ".groupaccess('a.album_access')."
            ");

            if (dbrows($result) > 0) {
                while ($data = dbarray($result)) {
                    $this->sitemap->addItem($this->siteurl.'infusions/gallery/gallery.php?photo_id='.$data['photo_id'], $data['photo_datestamp'], $options['frequency'], $options['priority']);
                }
            }
        }
    }

    private function News($cats = FALSE, $base_links = TRUE, $options = []) {
        if ($base_links == TRUE) {
            $this->sitemap->addItem($this->siteurl.'infusions/news/news.php', '', $options['frequency'], $options['priority']);
            $this->sitemap->addItem($this->siteurl.'infusions/news/news.php?type=recent', '', $options['frequency'], $options['priority']);
            $this->sitemap->addItem($this->siteurl.'infusions/news/news.php?type=comment', '', $options['frequency'], $options['priority']);
            $this->sitemap->addItem($this->siteurl.'infusions/news/news.php?type=rating', '', $options['frequency'], $options['priority']);
        }

        if ($cats == TRUE) {
            $result = dbquery("SELECT news_cat_id, news_cat_visibility, news_cat_language
                FROM ".DB_NEWS_CATS."
                WHERE ".groupaccess('news_cat_visibility')."
                ".(multilang_table('NS') ? " AND news_cat_language='".LANGUAGE."'" : '')."
                ORDER BY news_cat_id ASC
            ");

            if (dbrows($result) > 0) {
                while ($data = dbarray($result)) {
                    $this->sitemap->addItem($this->siteurl.'infusions/news/news.php?cat_id='.$data['news_cat_id'], '', $options['frequency_cat'], $options['priority_cat']);
                }
            }
        } else {
            require_once NEWS_CLASS.'autoloader.php';

            $items = \PHPFusion\News\NewsView::News()->get_NewsItem();

            foreach ($items['news_items'] as $id => $data) {
                $this->sitemap->addItem($this->siteurl.'infusions/news/news.php?readmore='.$data['news_id'], $data['news_datestamp'], $options['frequency'], $options['priority']);
            }
        }
    }

    private function WebLinks($cats = FALSE, $base_links = TRUE, $options = []) {
        if ($base_links == TRUE) {
            $this->sitemap->addItem($this->siteurl.'infusions/weblinks/weblinks.php', '', $options['frequency'], $options['priority']);
        }

        if ($cats == TRUE) {
            $result = dbquery("SELECT weblink_cat_id, weblink_cat_visibility, weblink_cat_language
                FROM ".DB_WEBLINK_CATS."
                WHERE ".groupaccess('weblink_cat_visibility')."
                ".(multilang_table('WL') ? " AND weblink_cat_language='".LANGUAGE."'" : '')."
                ORDER BY weblink_cat_id ASC
            ");

            if (dbrows($result) > 0) {
                while ($data = dbarray($result)) {
                    $this->sitemap->addItem($this->siteurl.'infusions/weblinks/weblinks.php?cat_id='.$data['weblink_cat_id'], '', $options['frequency_cat'], $options['priority_cat']);
                }
            }
        } else {
            $result = dbquery("SELECT w.*, wc.*
                 FROM ".DB_WEBLINKS." w
                 LEFT JOIN ".DB_WEBLINK_CATS." wc ON wc.weblink_cat_id=w.weblink_cat
                 WHERE w.weblink_status='1' AND ".groupaccess('w.weblink_visibility')." AND wc.weblink_cat_status='1' AND ".groupaccess('wc.weblink_cat_visibility')."
                 ".(multilang_table('WL') ? " AND w.weblink_language='".LANGUAGE."' AND wc.weblink_cat_language='".LANGUAGE."'" : '')."
                 GROUP BY w.weblink_id
            ");

            if (dbrows($result) > 0) {
                while ($data = dbarray($result)) {
                    $this->sitemap->addItem($this->siteurl.'infusions/weblinks/weblinks.php?weblink_id='.$data['weblink_id'], $data['weblink_datestamp'], $options['frequency'], $options['priority']);
                }
            }
        }
    }

    private function Link() {
        if (isset($_POST['save'])) {
            $this->custom_links = [
                'link_id' => form_sanitizer(!empty($_GET['link_id']) ? $_GET['link_id'] : $_POST['link_id'], 0, 'link_id'),
                'url'     => form_sanitizer($_POST['url'], '', 'url'),
            ];

            if (dbcount('(link_id)', DB_SITEMAP_LINKS, "link_id='".$this->custom_links['link_id']."'")) {
                dbquery_insert(DB_SITEMAP_LINKS, $this->custom_links, 'update');
                if (\defender::safe()) {
                    addNotice('success', $this->locale['SMG_notice_01']);

                    redirect(FUSION_SELF.fusion_get_aidlink());
                }
            } else {
                dbquery_insert(DB_SITEMAP_LINKS, $this->custom_links, 'save');

                if (\defender::safe()) {
                    addNotice('success', $this->locale['SMG_notice_02']);
                    redirect(FUSION_SELF.fusion_get_aidlink());
                }
            }
        }

        $result = dbquery("SELECT * FROM ".DB_SITEMAP_LINKS);

        if ((isset($_GET['action']) && $_GET['action'] == 'edit') && (isset($_GET['link_id']) && isnum($_GET['link_id']))) {
            if (dbrows($result)) {
                $this->custom_links = dbarray($result);
            } else {
                redirect(FUSION_SELF.fusion_get_aidlink());
            }
        }

        if ((isset($_GET['action']) && $_GET['action'] == 'delete') && (isset($_GET['link_id']) && isnum($_GET['link_id']))) {
            if (dbrows($result)) dbquery("DELETE FROM ".DB_SITEMAP_LINKS." WHERE link_id='".intval($_GET['link_id'])."'");
            addNotice('success', 'Link has been deleted');
            redirect(FUSION_SELF.fusion_get_aidlink());
        }

        if (isset($_POST['cancel'])) redirect(FUSION_SELF.fusion_get_aidlink());
    }

    public function GenerateXML() {
        if ($this->customlinks) {
            $customlinks = $this->GetSettings('customlinks');
            if ($customlinks['enabled'] == 1) {
                $this->CustomLinks([
                    'frequency' => $customlinks['frequency'],
                    'priority'  => $customlinks['priority']
                ]);
            }
        }

        if ($this->profiles) {
            $profiles = $this->GetSettings('profiles');
            if ($profiles['enabled'] == 1) {
                $this->Profiels([
                    'frequency' => $profiles['frequency'],
                    'priority'  => $profiles['priority']
                ]);
            }
        }

        if ($this->articles) {
            $articles = $this->GetSettings('articles');
            if ($articles['enabled'] == 1) {
                $this->Articles(FALSE, TRUE, [
                    'frequency' => $articles['frequency'],
                    'priority'  => $articles['priority']
                ]);
            }

            $article_cats = $this->GetSettings('article_cats');
            if ($article_cats['enabled'] == 1) {
                $this->Articles(TRUE, FALSE, [
                    'frequency_cat' => $article_cats['frequency'],
                    'priority_cat'  => $article_cats['priority']
                ]);
            }
        }

        if ($this->blogs) {
            $blogs = $this->GetSettings('blogs');
            if ($blogs['enabled'] == 1) {
                $this->Blogs(FALSE, TRUE, [
                    'frequency' => $blogs['frequency'],
                    'priority'  => $blogs['priority']
                ]);
            }

            $blog_cats = $this->GetSettings('blog_cats');
            if ($blog_cats['enabled'] == 1) {
                $this->Blogs(TRUE, FALSE, [
                    'frequency_cat' => $blog_cats['frequency'],
                    'priority_cat'  => $blog_cats['priority']
                ]);
            }
        }

        $custompages = $this->GetSettings('custompages');
        if ($custompages['enabled'] == 1) {
            $this->CustomPages([
                'frequency' => $custompages['frequency'],
                'priority'  => $custompages['priority']
            ]);
        }

        if ($this->downloads) {
            $downloads = $this->GetSettings('downloads');
            if ($downloads['enabled'] == 1) {
                $this->Downloads(FALSE, TRUE, [
                    'frequency' => $downloads['frequency'],
                    'priority'  => $downloads['priority']
                ]);
            }

            $download_cats = $this->GetSettings('download_cats');
            if ($download_cats['enabled'] == 1) {
                $this->Downloads(TRUE, FALSE, [
                    'frequency_cat' => $download_cats['frequency'],
                    'priority_cat'  => $download_cats['priority']
                ]);
            }
        }

        if ($this->faqs) {
            $faqs = $this->GetSettings('faq_cats');
            if ($faqs['enabled'] == 1) {
                $this->Faqs([
                    'frequency' => $faqs['frequency'],
                    'priority'  => $faqs['priority']
                ]);
            }
        }

        if ($this->forum) {
            $forum = $this->GetSettings('forum');
            if ($forum['enabled'] == 1) {
                $this->Forum([
                    'frequency' => $forum['frequency'],
                    'priority'  => $forum['priority']
                ]);
            }
        }

        if ($this->gallery) {
            $gallery = $this->GetSettings('gallery');
            if ($gallery['enabled'] == 1) {
                $this->Gallery(FALSE, TRUE, [
                    'frequency' => $gallery['frequency'],
                    'priority'  => $gallery['priority']
                ]);
            }

            $gallery_albums = $this->GetSettings('gallery_albums');
            if ($gallery_albums['enabled'] == 1) {
                $this->Gallery(TRUE, FALSE, [
                    'frequency_alb' => $gallery_albums['frequency'],
                    'priority_alb'  => $gallery_albums['priority']
                ]);
            }
        }

        if ($this->news) {
            $news = $this->GetSettings('news');
            if ($news['enabled'] == 1) {
                $this->News(FALSE, TRUE, [
                    'frequency' => $news['frequency'],
                    'priority'  => $news['priority']
                ]);
            }

            $news_cats = $this->GetSettings('news_cats');
            if ($news_cats['enabled'] == 1) {
                $this->News(TRUE, FALSE, [
                    'frequency_cat' => $news_cats['frequency'],
                    'priority_cat'  => $news_cats['priority']
                ]);
            }
        }

        if ($this->weblinks) {
            $weblinks = $this->GetSettings('weblinks');
            if ($weblinks['enabled'] == 1) {
                $this->WebLinks(FALSE, TRUE, [
                    'frequency' => $weblinks['frequency'],
                    'priority'  => $weblinks['priority']
                ]);
            }

            $weblink_cats = $this->GetSettings('weblink_cats');
            if ($weblink_cats['enabled'] == 1) {
                $this->WebLinks(TRUE, FALSE, [
                    'frequency_cat' => $weblink_cats['frequency'],
                    'priority_cat'  => $weblink_cats['priority']
                ]);
            }
        }

        $this->sitemap->write();
    }

    private function Modules() {
        $modules = [];

        if ($this->customlinks) {
            $modules['customlinks'] = ['locale' => '01'];
        }

        if ($this->profiles) {
            $modules['profiles'] = ['locale' => '02'];
        }

        if ($this->articles) {
            $modules['articles'] = ['locale' => '03'];
            $modules['article_cats'] = ['locale' => '04'];
        }

        if ($this->blogs) {
            $modules['blogs'] = ['locale' => '05'];
            $modules['blog_cats'] = ['locale' => '06'];
        }

        $modules['custompages'] = ['locale' => '07'];

        if ($this->downloads) {
            $modules['downloads'] = ['locale' => '08'];
            $modules['download_cats'] = ['locale' => '09'];
        }

        if ($this->faqs) {
            $modules['faq_cats'] = ['locale' => '10'];
        }

        if ($this->forum) {
            $modules['forum'] = ['locale' => '11'];
        }

        if ($this->gallery) {
            $modules['gallery'] = ['locale' => '12'];
            $modules['gallery_albums'] = ['locale' => '13'];
        }

        if ($this->news) {
            $modules['news'] = ['locale' => '14'];
            $modules['news_cats'] = ['locale' => '15'];
        }

        if ($this->weblinks) {
            $modules['weblinks'] = ['locale' => '16'];
            $modules['weblink_cats'] = ['locale' => '17'];
        }

        $module = '';

        foreach ($modules as $name => $value) {
            $result = dbquery("SELECT * FROM ".DB_SITEMAP." WHERE name=:name", [':name' => $name]);

            while ($module_settings = dbarray($result)) {
                $module .= '<tr>';
                $module .= '<td class="col-sm-3">';
                $module .= form_checkbox('enabled_'.$name, $this->locale['SMG_type_'.$value['locale']], $module_settings['enabled'], [
                    'reverse_label' => TRUE,
                    'value'         => 1
                ]);
                $module .= '</td>';

                $module .= '<td class="col-sm-7">';
                $module .= form_select('frequency_'.$name, $this->locale['SMG_006'], $module_settings['frequency'], [
                    'inline'      => TRUE,
                    'options'     => [
                        'always'  => $this->locale['SMG_007'],
                        'hourly'  => $this->locale['SMG_008'],
                        'daily'   => $this->locale['SMG_009'],
                        'weekly'  => $this->locale['SMG_010'],
                        'monthly' => $this->locale['SMG_011'],
                        'yearly'  => $this->locale['SMG_012'],
                        'never'   => $this->locale['SMG_013']
                    ]
                ]);
                $module .= '</td>';

                $module .= '<td class="col-sm-2">';
                $module .= form_select('priority_'.$name, $this->locale['SMG_014'], $module_settings['priority'], [
                    'inline'      => TRUE,
                    'inner_width' => '30px',
                    'options'     => [
                        '0.0' => '0.0',
                        '0.1' => '0.1',
                        '0.2' => '0.2',
                        '0.3' => '0.3',
                        '0.4' => '0.4',
                        '0.5' => '0.5',
                        '0.6' => '0.6',
                        '0.7' => '0.7',
                        '0.8' => '0.8',
                        '0.9' => '0.9',
                        '1.0' => '1.0'
                    ]
                ]);
                $module .= '</td>';
                $module .= '</tr>';
            }
        }

        return $module;
    }

    private function GetSettings($module) {
        $result = dbquery("SELECT * FROM ".DB_SITEMAP." WHERE name=:name", [':name' => $module]);

        if (dbrows($result) > 0) {
            $data = dbarray($result);

            return $data;
        } else {
            return NULL;
        }
    }

    public function DisplayAdmin() {
        add_to_title($this->locale['SMG_title_admin']);

        BreadCrumbs::getInstance()->addBreadCrumb([
            'link'  => INFUSIONS.'sitemap/admin.php'.fusion_get_aidlink(),
            'title' => $this->locale['SMG_title_admin']
        ]);

        opentable($this->locale['SMG_title_admin']);

        if (isset($_POST['generate'])) {
            $this->GenerateXML();

            if (\defender::safe()) {
                addNotice('success', $this->locale['SMG_notice_03']);
                redirect(FUSION_SELF.fusion_get_aidlink());
            }
        }

        if (isset($_POST['save_changes'])) {
            $modules = [
                'customlinks'    => [
                    'enabled'   => isset($_POST['enabled_customlinks']) ? 1 : 0,
                    'frequency' => isset($_POST['frequency_customlinks']) ? form_sanitizer($_POST['frequency_customlinks'], '', 'frequency_customlinks') : '',
                    'priority'  => isset($_POST['priority_customlinks']) ? form_sanitizer($_POST['priority_customlinks'], '', 'priority_customlinks') : ''
                ],
                'profiles'       => [
                    'enabled'   => isset($_POST['enabled_profiles']) ? 1 : 0,
                    'frequency' => form_sanitizer($_POST['frequency_profiles'], '', 'frequency_profiles'),
                    'priority'  => form_sanitizer($_POST['priority_profiles'], '', 'priority_profiles')
                ],
                'articles'       => [
                    'enabled'   => isset($_POST['enabled_articles']) ? 1 : 0,
                    'frequency' => form_sanitizer($_POST['frequency_articles'], '', 'frequency_articles'),
                    'priority'  => form_sanitizer($_POST['priority_articles'], '', 'priority_articles')
                ],
                'article_cats'   => [
                    'enabled'   => isset($_POST['enabled_article_cats']) ? 1 : 0,
                    'frequency' => form_sanitizer($_POST['frequency_article_cats'], '', 'frequency_article_cats'),
                    'priority'  => form_sanitizer($_POST['priority_article_cats'], '', 'priority_article_cats')
                ],
                'blogs'          => [
                    'enabled'   => isset($_POST['enabled_blogs']) ? 1 : 0,
                    'frequency' => form_sanitizer($_POST['frequency_blogs'], '', 'frequency_blogs'),
                    'priority'  => form_sanitizer($_POST['priority_blogs'], '', 'priority_blogs')
                ],
                'blog_cats'      => [
                    'enabled'   => isset($_POST['enabled_blog_cats']) ? 1 : 0,
                    'frequency' => form_sanitizer($_POST['frequency_blog_cats'], '', 'frequency_blog_cats'),
                    'priority'  => form_sanitizer($_POST['priority_blog_cats'], '', 'priority_blog_cats')
                ],
                'custompages'    => [
                    'enabled'   => isset($_POST['enabled_custompages']) ? 1 : 0,
                    'frequency' => form_sanitizer($_POST['frequency_custompages'], '', 'frequency_custompages'),
                    'priority'  => form_sanitizer($_POST['priority_custompages'], '', 'priority_custompages')
                ],
                'downloads'      => [
                    'enabled'   => isset($_POST['enabled_downloads']) ? 1 : 0,
                    'frequency' => form_sanitizer($_POST['frequency_downloads'], '', 'frequency_downloads'),
                    'priority'  => form_sanitizer($_POST['priority_downloads'], '', 'priority_downloads')
                ],
                'download_cats'  => [
                    'enabled'   => isset($_POST['enabled_download_cats']) ? 1 : 0,
                    'frequency' => form_sanitizer($_POST['frequency_download_cats'], '', 'frequency_download_cats'),
                    'priority'  => form_sanitizer($_POST['priority_download_cats'], '', 'priority_download_cats')
                ],
                'faq_cats'       => [
                    'enabled'   => isset($_POST['enabled_faq_cats']) ? 1 : 0,
                    'frequency' => form_sanitizer($_POST['frequency_faq_cats'], '', 'frequency_faq_cats'),
                    'priority'  => form_sanitizer($_POST['priority_faq_cats'], '', 'priority_faq_cats')
                ],
                'forum'          => [
                    'enabled'   => isset($_POST['enabled_forum']) ? 1 : 0,
                    'frequency' => form_sanitizer($_POST['frequency_forum'], '', 'frequency_forum'),
                    'priority'  => form_sanitizer($_POST['priority_forum'], '', 'priority_forum')
                ],
                'gallery'        => [
                    'enabled'   => isset($_POST['enabled_gallery']) ? 1 : 0,
                    'frequency' => form_sanitizer($_POST['frequency_gallery'], '', 'frequency_gallery'),
                    'priority'  => form_sanitizer($_POST['priority_gallery'], '', 'priority_gallery')
                ],
                'gallery_albums' => [
                    'enabled'   => isset($_POST['enabled_gallery_albums']) ? 1 : 0,
                    'frequency' => form_sanitizer($_POST['frequency_gallery_albums'], '', 'frequency_gallery_albums'),
                    'priority'  => form_sanitizer($_POST['priority_gallery_albums'], '', 'priority_gallery_albums')
                ],
                'news'           => [
                    'enabled'   => isset($_POST['enabled_news']) ? 1 : 0,
                    'frequency' => form_sanitizer($_POST['frequency_news'], '', 'frequency_news'),
                    'priority'  => form_sanitizer($_POST['priority_news'], '', 'priority_news')
                ],
                'news_cats'      => [
                    'enabled'   => isset($_POST['enabled_news_cats']) ? 1 : 0,
                    'frequency' => form_sanitizer($_POST['frequency_news_cats'], '', 'frequency_news_cats'),
                    'priority'  => form_sanitizer($_POST['priority_news_cats'], '', 'priority_news_cats')
                ],
                'weblinks'       => [
                    'enabled'   => isset($_POST['enabled_weblinks']) ? 1 : 0,
                    'frequency' => form_sanitizer($_POST['frequency_weblinks'], '', 'frequency_weblinks'),
                    'priority'  => form_sanitizer($_POST['priority_weblinks'], '', 'priority_weblinks')
                ],
                'weblink_cats'   => [
                    'enabled'   => isset($_POST['enabled_weblink_cats']) ? 1 : 0,
                    'frequency' => form_sanitizer($_POST['frequency_weblink_cats'], '', 'frequency_weblink_cats'),
                    'priority'  => form_sanitizer($_POST['priority_weblink_cats'], '', 'priority_weblink_cats')
                ]
            ];

            if (\defender::safe()) {
                foreach ($modules as $name => $data) {
                    $db = [
                        'name'      => $name,
                        'enabled'   => $data['enabled'],
                        'frequency' => $data['frequency'],
                        'priority'  => $data['priority']
                    ];

                    dbquery_insert(DB_SITEMAP, $db, 'update', ['primary_key' => 'name']);
                }

                addNotice('success', $this->locale['SMG_notice_04']);
                redirect(FUSION_SELF.fusion_get_aidlink());
            }
        }

        if (isset($_POST['save_settings'])) {
            $settings = [
                'auto_update' => isset($_POST['auto_update']) ? 1 : 0,
                'update_interval' => form_sanitizer(($_POST['update_interval'] * 60 * 60), '', 'update_interval')
            ];

            if (\defender::safe()) {
                foreach ($settings as $settings_name => $settings_value) {
                    $db = [
                        'settings_name'  => $settings_name,
                        'settings_value' => $settings_value,
                        'settings_inf'   => 'sitemap_panel'
                    ];

                    dbquery_insert(DB_SETTINGS_INF, $db, 'update', ['primary_key' => 'settings_name']);
                }

                addNotice('success', $this->locale['SMG_notice_05']);
                redirect(FUSION_SELF.fusion_get_aidlink());
            }
        }

        if (file_exists($this->sitemap_file)) {
            echo '<div class="well">';
                echo $this->locale['SMG_001'].' '.showdate('longdate', filemtime($this->sitemap_file));
                echo '<br/><a href="'.$this->sitemap_file.'" target="_blank">'.$this->locale['SMG_002'].'</a></span>';
            echo '</div>';
        }

        add_to_head('<style>#sitemaptable .form-group{margin-bottom: 0;}</style>');

        echo openform('savesettings', 'post', FUSION_REQUEST, ['class' => 'm-t-15']);
        openside();
            echo '<div class="row">';
            echo '<div class="col-xs-12 col-sm-3">';
                echo form_checkbox('auto_update', $this->locale['SMG_015'], $this->sitemap_settings['auto_update'], ['toggle' => TRUE]);
            echo '</div>';

            echo '<div class="col-xs-12 col-sm-7" style="margin-bottom: -15px;">';
                $update_interval = $this->sitemap_settings['update_interval'] / 60 / 60;
                echo form_text('update_interval', $this->locale['SMG_016'], $update_interval, ['type' => 'number', 'inline' => TRUE]);
            echo '</div>';

            echo '<div class="col-xs-12 col-sm-2">';
                echo form_button('save_settings', $this->locale['save'], 'save', ['class' => 'btn-success m-t-5']);
            echo '</div>';
            echo '</div>';
        closeside();
        echo closeform();

        echo openform('savechanges', 'post', FUSION_REQUEST, ['class' => 'm-t-15']);
        echo '<div class="panel panel-default" id="sitemaptable">';
        echo '<div class="table-responsive"><table class="table table-striped">';
            echo '<tbody>';
                echo $this->Modules();
            echo '</tbody>';
        echo '</table></div>';

        $selectall = form_checkbox('selectall', $this->locale['SMG_003'], '', ['class' => 'm-b-0']);
        $save = form_button('save_changes', $this->locale['save_changes'], 'save', ['class' => 'btn-success btn-sm']);

        echo '<div class="panel-footer">';
        echo '<div class="pull-left">'.$selectall.'</div><div class="text-center">'.$save.'</div>';
        echo '</div>';
        echo '</div>'; // .panel

        add_to_jquery('
            var checkbox = $(\'input[id ^= "enabled_"]\');

            if ($(\'input[id ^= "enabled_"]:checked\').length == checkbox.length) {
                $("#selectall").prop("checked", true);
            }

            $("#selectall").click(function() {
                checkbox.prop("checked", !checkbox.prop("checked"));
            });
        ');

        echo closeform();

        echo openform('generatexml', 'post', FUSION_REQUEST, ['class' => 'm-t-15']);
            echo form_button('generate', $this->locale['SMG_004'], 'generate', ['class' => 'btn-default text-center']);
        echo closeform();

        echo '<div class="row m-t-30">';
            echo '<div class="col-xs-12 col-sm-6">';
                $this->Link();

                openside();
                echo openform('addlink', 'post', FUSION_REQUEST);
                    echo form_hidden('link_id', '', $this->custom_links['link_id']);
                    echo form_text('url', $this->locale['SMG_005'], $this->custom_links['url'], ['type' => 'url', 'inline' => TRUE]);
                    echo form_button('save', $this->locale['save'], 'save', ['class' => 'btn-success']);
                    echo form_button('cancel', $this->locale['cancel'], 'cancel');
                echo closeform();
                closeside();
            echo '</div>';

            echo '<div class="col-xs-12 col-sm-6">';
                $result = dbquery("SELECT * FROM ".DB_SITEMAP_LINKS);

                if (dbrows($result) > 0) {
                    openside($this->locale['SMG_type_01']);
                    while ($data = dbarray($result)) {
                        echo '<div>';
                            echo '<span class="badge">'.$data['url'].'</span> ';
                            echo '<span class="pull-right">';
                                echo '<a href="'.FUSION_SELF.fusion_get_aidlink().'&amp;action=edit&amp;link_id='.$data['link_id'].'">'.$this->locale['edit'].'</a>';
                                echo ' | ';
                                echo '<a href="'.FUSION_SELF.fusion_get_aidlink().'&amp;action=delete&amp;link_id='.$data['link_id'].'">'.$this->locale['delete'].'</a>';
                            echo '</span>';
                        echo '</div>';
                    }
                    closeside();
                }
            echo '</div>';
        echo '</div>';

        closetable();
    }
}
