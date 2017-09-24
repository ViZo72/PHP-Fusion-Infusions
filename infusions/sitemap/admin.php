<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: sitemap/admin.php
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

require_once INFUSIONS.'sitemap/Sitemap.php';

pageAccess('SM');

use \PHPFusion\BreadCrumbs;
use samdark\sitemap\Sitemap;

class SitemapGenerator {
    private $locale = [];
    private $siteurl = '';
    private $sitemap = '';
    private $sitemap_file = BASEDIR.'sitemap.xml';
    private $custom_links = [
        'link_id' => 0,
        'url'     => ''
    ];

    public function __construct() {
        $this->locale  = fusion_get_locale('', SM_LOCALE);
        $this->siteurl = fusion_get_settings('siteurl');
        $this->sitemap = new Sitemap($this->sitemap_file);
    }

    private function Profiels() {
        if (fusion_get_settings('hide_userprofiles') == 0) {
            $result = dbquery("SELECT user_id, user_status
                FROM ".DB_USERS."
                WHERE user_status = 0
            ");

            while ($data = dbarray($result)) {
                $this->sitemap->addItem($this->siteurl.'profile.php?lookup='.$data['user_id']);
            }
        }
    }

    private function CustomLinks() {
        $result = dbquery("SELECT url FROM ".DB_SM_LINKS);

        if (dbrows($result) > 0) {
            while ($data = dbarray($result)) {
                $this->sitemap->addItem($data['url']);
            }
        }
    }

    private function Articles($cats = FALSE, $base_links = TRUE) {
        if ($base_links == TRUE) {
            $this->sitemap->addItem($this->siteurl.'infusions/articles/articles.php');
            $this->sitemap->addItem($this->siteurl.'infusions/articles/articles.php?type=recent');
            $this->sitemap->addItem($this->siteurl.'infusions/articles/articles.php?type=comment');
            $this->sitemap->addItem($this->siteurl.'infusions/articles/articles.php?type=rating');
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
                    $this->sitemap->addItem($this->siteurl.'infusions/articles/articles.php?cat_id='.$data['article_cat_id']);
                }
            }
        } else {
            require_once ARTICLE_CLASS.'autoloader.php';

            $items = \PHPFusion\Articles\ArticlesServer::Articles()->get_ArticleItems();

            foreach ($items['article_items'] as $id => $data) {
                $this->sitemap->addItem($this->siteurl.'infusions/articles/articles.php?article_id='.$data['article_id'], $data['article_datestamp']);
            }
        }
    }

    private function Blogs($cats = FALSE, $base_links = TRUE) {
        if ($base_links == TRUE) {
            $this->sitemap->addItem($this->siteurl.'infusions/blog/blog.php');
            $this->sitemap->addItem($this->siteurl.'infusions/blog/blog.php?type=recent');
            $this->sitemap->addItem($this->siteurl.'infusions/blog/blog.php?type=comment');
            $this->sitemap->addItem($this->siteurl.'infusions/blog/blog.php?type=rating');
        }

        if ($cats == TRUE) {
            $result = dbquery("SELECT blog_cat_id, blog_cat_language
                FROM ".DB_BLOG_CATS."
                ".(multilang_column('BL') ? "WHERE blog_cat_language='".LANGUAGE."'" : '')."
            ");

            if (dbrows($result) > 0) {
                while ($data = dbarray($result)) {
                    $this->sitemap->addItem($this->siteurl.'infusions/blog/blog.php?cat_id='.$data['blog_cat_id']);
                }
            }

            $this->sitemap->addItem($this->siteurl.'infusions/blog/blog.php?cat_id=0');
        } else {
            $result = dbquery("SELECT blog_id, blog_datestamp, blog_language, blog_visibility, blog_draft
                FROM ".DB_BLOG."
                ".(multilang_table('BL') ? "WHERE blog_language='".LANGUAGE."' AND" : 'WHERE')." ".groupaccess('blog_visibility')." AND blog_draft = 0
                ORDER BY blog_datestamp DESC
            ");

            if (dbrows($result) > 0) {
                while ($data = dbarray($result)) {
                    $this->sitemap->addItem($this->siteurl.'infusions/blog/blog.php?readmore='.$data['blog_id'], $data['blog_datestamp']);
                }
            }
        }
    }

    private function CustomPages() {
        $result = dbquery("SELECT page_id, page_datestamp, page_language, page_status
            FROM ".DB_CUSTOM_PAGES."
            ".(multilang_table('CP') ? "WHERE page_language='".LANGUAGE."'" : 'WHERE')." AND page_status = 1
            ORDER BY page_datestamp DESC
        ");

        if (dbrows($result) > 0) {
            while ($data = dbarray($result)) {
                $this->sitemap->addItem($this->siteurl.'viewpage.php?page_id='.$data['page_id'], $data['page_datestamp']);
            }
        }
    }

    private function Downloads($cats = FALSE, $base_links = TRUE) {
        if ($base_links == TRUE) {
            $this->sitemap->addItem($this->siteurl.'infusions/downloads/downloads.php');
            $this->sitemap->addItem($this->siteurl.'infusions/downloads/downloads.php?type=download');
            $this->sitemap->addItem($this->siteurl.'infusions/downloads/downloads.php?type=recent');
            $this->sitemap->addItem($this->siteurl.'infusions/downloads/downloads.php?type=comments');
            $this->sitemap->addItem($this->siteurl.'infusions/downloads/downloads.php?type=ratings');
        }

        if ($cats == TRUE) {
            $result = dbquery("SELECT download_cat_id, download_cat_language
                FROM ".DB_DOWNLOAD_CATS."
                ".(multilang_table('DL') ? " WHERE download_cat_language='".LANGUAGE."'" : '')."
            ");

            if (dbrows($result) > 0) {
                while ($data = dbarray($result)) {
                    $this->sitemap->addItem($this->siteurl.'downloads/downloads.php?cat_id='.$data['download_cat_id']);
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
                    $this->sitemap->addItem($this->siteurl.'downloads/downloads.php?download_id='.$data['download_id'], $data['download_datestamp']);
                }
            }
        }
    }

    private function Faqs() {
        $result = dbquery("SELECT faq_cat_id, faq_cat_language
            FROM ".DB_FAQ_CATS."
            ".(multilang_table('FQ') ? " WHERE faq_cat_language='".LANGUAGE."' " : '')."
        ");

        if (dbrows($result) > 0) {
            while ($data = dbarray($result)) {
                $this->sitemap->addItem($this->siteurl.'faq/faq.php?cat_id='.$data['faq_cat_id']);
            }
        }

        $this->sitemap->addItem($this->siteurl.'faq/faq.php?cat_id=0');
    }

    private function Forum() {
        $this->sitemap->addItem($this->siteurl.'forum/index.php');
        $this->sitemap->addItem($this->siteurl.'forum/index.php?section=latest');
        $this->sitemap->addItem($this->siteurl.'forum/index.php?section=unanswered');
        $this->sitemap->addItem($this->siteurl.'forum/index.php?section=unsolved');
        $this->sitemap->addItem($this->siteurl.'forum/tags.php');

        $result_tags = dbquery("SELECT tag_id, tag_status, tag_language
            FROM ".DB_FORUM_TAGS."
            WHERE tag_status = 1
            ".(multilang_table('FO') ? "AND tag_language='".LANGUAGE."'" : '')."
        ");

        if (dbrows($result_tags) > 0) {
            while ($data = dbarray($result_tags)) {
                $this->sitemap->addItem($this->siteurl.'forum/tags.php?tag_id='.$data['tag_id']);
            }
        }

        $result_forums = dbquery("SELECT forum_id, forum_access, forum_language
            FROM ".DB_FORUMS."
            ".(multilang_table('FO') ? " WHERE forum_language='".LANGUAGE."' AND " : ' WHERE ').groupaccess('forum_access')."
        ");

        if (dbrows($result_forums) > 0) {
            while ($data = dbarray($result_forums)) {
                $this->sitemap->addItem($this->siteurl.'forum/index.php?viewforum&forum_id='.$data['forum_id']);
            }
        }

        $result_threads = dbquery("SELECT t.thread_id, t.thread_lastpost
            FROM ".DB_FORUM_THREADS." t
            INNER JOIN ".DB_FORUMS." f ON t.forum_id = f.forum_id
            WHERE ".groupaccess('forum_access')." AND t.thread_hidden = 0
        ");

        if (dbrows($result_threads) > 0) {
            while ($data = dbarray($result_threads)) {
                $this->sitemap->addItem($this->siteurl.'forum/viewthread.php?thread_id='.$data['thread_id'], $data['thread_lastpost']);
            }
        }
    }

    private function Gallery($albums = FALSE, $base_links = TRUE) {
        if ($base_links == TRUE) {
            $this->sitemap->addItem($this->siteurl.'infusions/gallery/gallery.php');
        }

        if ($albums == TRUE) {
            $result = dbquery("SELECT album_id, album_access, album_datestamp
                FROM ".DB_PHOTO_ALBUMS."
                WHERE ".groupaccess('album_access')."
            ");

            if (dbrows($result) > 0) {
                while ($data = dbarray($result)) {
                    $this->sitemap->addItem($this->siteurl.'infusions/gallery/gallery.php?album_id='.$data['album_id'], $data['album_datestamp']);
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
                    $this->sitemap->addItem($this->siteurl.'infusions/gallery/gallery.php?photo_id='.$data['photo_id'], $data['photo_datestamp']);
                }
            }
        }
    }

    private function News($cats = FALSE, $base_links = TRUE) {
        if ($base_links == TRUE) {
            $this->sitemap->addItem($this->siteurl.'infusions/news/news.php');
            $this->sitemap->addItem($this->siteurl.'infusions/news/news.php?type=recent');
            $this->sitemap->addItem($this->siteurl.'infusions/news/news.php?type=comment');
            $this->sitemap->addItem($this->siteurl.'infusions/news/news.php?type=rating');
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
                    $this->sitemap->addItem($this->siteurl.'infusions/news/news.php?cat_id='.$data['news_cat_id']);
                }
            }
        } else {
            require_once NEWS_CLASS.'autoloader.php';

            $items = \PHPFusion\News\NewsView::News()->get_NewsItem();

            foreach ($items['news_items'] as $id => $data) {
                $this->sitemap->addItem($this->siteurl.'infusions/news/news.php?readmore='.$data['news_id'], $data['news_datestamp']);
            }
        }
    }

    private function Weblinks($cats = FALSE, $base_links = TRUE) {
        if ($base_links == TRUE) {
            $this->sitemap->addItem($this->siteurl.'infusions/weblinks/weblinks.php');
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
                    $this->sitemap->addItem($this->siteurl.'infusions/weblinks/weblinks.php?cat_id='.$data['weblink_cat_id']);
                }
            }
        } else {
            require_once WEBLINKS_CLASS.'autoloader.php';

            $items = \PHPFusion\Weblinks\WeblinksServer::Weblinks()->get_WeblinkItems();

            foreach ($items['weblink_items'] as $id => $data) {
                $this->sitemap->addItem($this->siteurl.'infusions/weblinks/weblinks.php?weblink_id='.$data['weblink_id'], $data['weblink_datestamp']);
            }
        }
    }

    private function Link() {
        if (isset($_POST['save'])) {
            $this->custom_links = [
                'link_id' => form_sanitizer(!empty($_GET['link_id']) ? $_GET['link_id'] : $_POST['link_id'], 0, 'link_id'),
                'url'     => form_sanitizer($_POST['url'], '', 'url'),
            ];

            if (dbcount('(link_id)', DB_SM_LINKS, "link_id='".$this->custom_links['link_id']."'")) {
                dbquery_insert(DB_SM_LINKS, $this->custom_links, 'update');
                if (\defender::safe()) {
                    addNotice('success', $this->locale['SM_notice_001']);

                    redirect(FUSION_SELF.fusion_get_aidlink());
                }
            } else {
                dbquery_insert(DB_SM_LINKS, $this->custom_links, 'save');

                if (\defender::safe()) {
                    addNotice('success', $this->locale['SM_notice_002']);
                    redirect(FUSION_SELF.fusion_get_aidlink());
                }
            }
        }

        $result = dbquery("SELECT * FROM ".DB_SM_LINKS);

        if ((isset($_GET['action']) && $_GET['action'] == 'edit') && (isset($_GET['link_id']) && isnum($_GET['link_id']))) {
            if (dbrows($result)) {
                $this->custom_links = dbarray($result);
            } else {
                redirect(FUSION_SELF.fusion_get_aidlink());
            }
        }

        if ((isset($_GET['action']) && $_GET['action'] == 'delete') && (isset($_GET['link_id']) && isnum($_GET['link_id']))) {
            if (dbrows($result)) dbquery("DELETE FROM ".DB_SM_LINKS." WHERE link_id='".intval($_GET['link_id'])."'");
            addNotice('success', 'Link has been deleted');
            redirect(FUSION_SELF.fusion_get_aidlink());
        }

        if (isset($_POST['cancel'])) redirect(FUSION_SELF.fusion_get_aidlink());
    }

    public function Display() {
        add_to_title($this->locale['SM_title_admin']);

        BreadCrumbs::getInstance()->addBreadCrumb([
            'link'  => INFUSIONS.'sitemap/admin.php'.fusion_get_aidlink(),
            'title' => $this->locale['SM_title_admin']
        ]);

        opentable($this->locale['SM_title_admin']);

        $articles  = function_exists('infusion_exists') ? infusion_exists('articles') : db_exists(DB_PREFIX.'articles');
        $blog      = function_exists('infusion_exists') ? infusion_exists('blog') : db_exists(DB_PREFIX.'blog');
        $downloads = function_exists('infusion_exists') ? infusion_exists('downloads') : db_exists(DB_PREFIX.'downloads');
        $faqs      = function_exists('infusion_exists') ? infusion_exists('faq') : db_exists(DB_PREFIX.'faqs');
        $forum     = function_exists('infusion_exists') ? infusion_exists('forum') : db_exists(DB_PREFIX.'forums');
        $gallery   = function_exists('infusion_exists') ? infusion_exists('gallery') : db_exists(DB_PREFIX.'photos');
        $news      = function_exists('infusion_exists') ? infusion_exists('news') : db_exists(DB_PREFIX.'news');
        $weblinks  = function_exists('infusion_exists') ? infusion_exists('weblinks') : db_exists(DB_PREFIX.'weblinks');

        if (isset($_POST['generate'])) {
            if (isset($_POST['enabled_profiles']) && $_POST['enabled_profiles'] == 1) $this->Profiels();
            if (isset($_POST['enabled_customlinks']) && $_POST['enabled_customlinks'] == 1) $this->CustomLinks();

            if ($articles) {
                if (isset($_POST['enabled_articles']) && $_POST['enabled_articles'] == 1) $this->Articles();
                if (isset($_POST['enabled_article_cats']) && $_POST['enabled_article_cats'] == 1) $this->Articles(true, false);
            }

            if ($blog) {
                if (isset($_POST['enabled_blogs']) && $_POST['enabled_blogs'] == 1) $this->Blogs();
                if (isset($_POST['enabled_blog_cats']) && $_POST['enabled_blog_cats'] == 1) $this->Blogs(true, false);
            }

            if (isset($_POST['enabled_custompages']) && $_POST['enabled_custompages'] == 1) $this->CustomPages();

            if ($downloads) {
                if (isset($_POST['enabled_downloads']) && $_POST['enabled_downloads'] == 1) $this->Downloads();
                if (isset($_POST['enabled_download_cats']) && $_POST['enabled_download_cats'] == 1) $this->Downloads(true, false);
            }

            if ($faqs) {
                if (isset($_POST['enabled_faq_cats']) && $_POST['enabled_faq_cats'] == 1) $this->Faqs();
            }

            if ($forum) {
                if (isset($_POST['enabled_forum']) && $_POST['enabled_forum'] == 1) $this->Forum();
            }

            if ($gallery) {
                if (isset($_POST['enabled_gallery']) && $_POST['enabled_gallery'] == 1) $this->Gallery();
                if (isset($_POST['enabled_gallery_albums']) && $_POST['enabled_gallery_albums'] == 1) $this->Gallery(true, false);
            }

            if ($news) {
                if (isset($_POST['enabled_news']) && $_POST['enabled_news'] == 1) $this->News();
                if (isset($_POST['enabled_news_cats']) && $_POST['enabled_news_cats'] == 1) $this->News(true, false);
            }

            if ($weblinks) {
                if (isset($_POST['enabled_weblinks']) && $_POST['enabled_weblinks'] == 1) $this->Weblinks();
                if (isset($_POST['enabled_weblinks_cats']) && $_POST['enabled_weblinks_cats'] == 1) $this->Weblinks(true, false);
            }

            $this->sitemap->write();

            if (\defender::safe()) {
                addNotice('success', $this->locale['SM_notice_003']);
                redirect(FUSION_SELF.fusion_get_aidlink());
            }
        }

        if (file_exists($this->sitemap_file)) {
            echo '<div class="well">';
                echo $this->locale['SM_001'].' '.showdate('longdate', filemtime($this->sitemap_file));
                echo '<br/><a href="'.$this->sitemap_file.'" target="_blank">'.$this->locale['SM_002'].'</a></span>';
            echo '</div>';
        }

        echo openform('generate', 'post', FUSION_REQUEST, ['class' => 'm-t-15']);
        echo openside();
        echo '<div class="row">';
        echo '<div class="col-xs-12 col-sm-6">';
        echo form_checkbox('enabled_profiles', $this->locale['SM_007'], 0, ['reverse_label' => TRUE]);
        echo form_checkbox('enabled_customlinks', $this->locale['SM_006'], 0, ['reverse_label' => TRUE]);

        if ($articles) {
            echo form_checkbox('enabled_articles', $this->locale['SM_008'], 0, ['reverse_label' => true]);
            echo form_checkbox('enabled_article_cats', $this->locale['SM_009'], 0, ['reverse_label' => true]);
        }

        if ($blog) {
            echo form_checkbox('enabled_blogs', $this->locale['SM_010'], 0, ['reverse_label' => true]);
            echo form_checkbox('enabled_blog_cats', $this->locale['SM_011'], 0, ['reverse_label' => true]);
        }

        echo form_checkbox('enabled_custompages', $this->locale['SM_012'], 0, ['reverse_label' => TRUE]);

        if ($downloads) {
            echo form_checkbox('enabled_downloads', $this->locale['SM_013'], 0, ['reverse_label' => true]);
            echo form_checkbox('enabled_download_cats', $this->locale['SM_014'], 0, ['reverse_label' => true]);
        }

        echo '</div>';
        echo '<div class="col-xs-12 col-sm-6">';

        if ($faqs) {
            echo form_checkbox('enabled_faq_cats', $this->locale['SM_015'], 0, ['reverse_label' => true]);
        }

        if ($forum) {
            echo form_checkbox('enabled_forum', $this->locale['SM_016'], 0, ['reverse_label' => true]);
        }

        if ($gallery) {
            echo form_checkbox('enabled_gallery', $this->locale['SM_017'], 0, ['reverse_label' => true]);
            echo form_checkbox('enabled_gallery_albums', $this->locale['SM_018'], 0, ['reverse_label' => true]);
        }

        if ($news) {
            echo form_checkbox('enabled_news', $this->locale['SM_019'], 0, ['reverse_label' => true]);
            echo form_checkbox('enabled_news_cats', $this->locale['SM_020'], 0, ['reverse_label' => true]);
        }

        if ($weblinks) {
            echo form_checkbox('enabled_weblinks', $this->locale['SM_021'], 0, ['reverse_label' => true]);
            echo form_checkbox('enabled_weblinks_cats', $this->locale['SM_022'], 0, ['reverse_label' => true]);
        }

        echo '</div>';

        echo '</div>';

        $selectall = form_checkbox('selectall', $this->locale['SM_003'], '', ['class' => 'm-b-0']);
        $generate = form_button('generate', $this->locale['SM_004'], 'generate', ['class' => 'btn-default btn-sm']);

        echo closeside('<div class="pull-left">'.$selectall.'</div><div class="text-center">'.$generate.'</div>');

        add_to_jquery('$("#selectall").click(function() {
            var checkbox = $(\'input[id^="enabled_"]\');
            checkbox.prop("checked", !checkbox.prop("checked"));
        });');

        echo closeform();

        echo '<div class="row m-t-30">';
        echo '<div class="col-xs-12 col-sm-6">';
            $result = dbquery("SELECT * FROM ".DB_SM_LINKS);

            if (dbrows($result) > 0) {
                echo openside($this->locale['SM_006']);
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
                echo closeside();
            }
        echo '</div>';
        echo '<div class="col-xs-12 col-sm-6">';
            $this->Link();

            echo openside();
            echo openform('addlink', 'post', FUSION_REQUEST);
                echo form_hidden('link_id', '', $this->custom_links['link_id']);
                echo form_text('url', $this->locale['SM_005'], $this->custom_links['url'], ['type' => 'url', 'inline' => TRUE]);
                echo form_button('save', $this->locale['save'], 'save', ['class' => 'btn-success']);
                echo form_button('cancel', $this->locale['cancel'], 'cancel');
            echo closeform();
            echo closeside();
        echo '</div>';

        echo '</div>';

        closetable();
    }
}

$sm = new SitemapGenerator();
$sm->Display();

require_once THEMES.'templates/footer.php';
