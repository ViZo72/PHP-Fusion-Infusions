<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: sitemap_panel/classes/SitemapGenerator.php
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

class SitemapGenerator {
    private $locale;
    private $settings;
    private $siteurl;
    private $sitemap;
    public $sitemap_folder = BASEDIR.'sitemaps/';
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
    private $videos;
    private $weblinks;
    private $modules = [
        'customlinks',
        'profiles',
        'articles',
        'blogs',
        'custompages',
        'downloads',
        'faqs',
        'forum',
        'gallery',
        'news',
        'videos',
        'weblinks'
    ];

    public function __construct() {
        $this->locale = fusion_get_locale('', SMG_LOCALE);
        $this->settings = fusion_get_settings();
        $this->siteurl = $this->settings['siteurl'];
        $this->sitemap = new Sitemap($this->sitemap_file);
        $this->sitemap_settings = get_settings('sitemap_panel');

        $this->customlinks = dbcount('(link_id)', DB_SITEMAP_LINKS) > 0;
        $this->profiles = $this->settings['hide_userprofiles'] == 0;
        $this->articles = function_exists('infusion_exists') ? infusion_exists('articles') : db_exists(DB_PREFIX.'articles');
        $this->blogs = function_exists('infusion_exists') ? infusion_exists('blog') : db_exists(DB_PREFIX.'blog');
        $this->downloads = function_exists('infusion_exists') ? infusion_exists('downloads') : db_exists(DB_PREFIX.'downloads');
        $this->faqs = function_exists('infusion_exists') ? infusion_exists('faq') : db_exists(DB_PREFIX.'faqs');
        $this->forum = function_exists('infusion_exists') ? infusion_exists('forum') : db_exists(DB_PREFIX.'forums');
        $this->gallery = function_exists('infusion_exists') ? infusion_exists('gallery') : db_exists(DB_PREFIX.'photos');
        $this->news = function_exists('infusion_exists') ? infusion_exists('news') : db_exists(DB_PREFIX.'news');
        $this->videos = function_exists('infusion_exists') ? infusion_exists('videos') : db_exists(DB_PREFIX.'videos');
        $this->weblinks = function_exists('infusion_exists') ? infusion_exists('weblinks') : db_exists(DB_PREFIX.'weblinks');
    }

    private function customLinks($options = []) {
        $result = dbquery("SELECT url FROM ".DB_SITEMAP_LINKS);

        if (dbrows($result) > 0) {
            while ($data = dbarray($result)) {
                $this->sitemap->addItem($data['url'], '', $options['frequency'], $options['priority']);
            }
        }
    }

    private function profiels($options = []) {
        $result = dbquery("SELECT user_id, user_status
            FROM ".DB_USERS."
            WHERE user_status=0
        ");

        while ($data = dbarray($result)) {
            $this->sitemap->addItem($this->siteurl.'profile.php?lookup='.$data['user_id'], '', $options['frequency'], $options['priority']);
        }
    }

    private function articles($cats = FALSE, $base_links = TRUE, $options = []) {
        if ($base_links == TRUE) {
            $this->sitemap->addItem($this->siteurl.'infusions/articles/articles.php', '', $options['frequency'], $options['priority']);
            $this->sitemap->addItem($this->siteurl.'infusions/articles/articles.php?type=recent', '', $options['frequency'], $options['priority']);

            if ($this->settings['comments_enabled'] == 1) {
                $this->sitemap->addItem($this->siteurl.'infusions/articles/articles.php?type=comment', '', $options['frequency'], $options['priority']);
            }

            if ($this->settings['ratings_enabled'] == 1) {
                $this->sitemap->addItem($this->siteurl.'infusions/articles/articles.php?type=rating', '', $options['frequency'], $options['priority']);
            }
        }

        if ($cats == TRUE) {
            $result = dbquery("SELECT article_cat_id, article_cat_status, article_cat_visibility, article_cat_language
                FROM ".DB_ARTICLE_CATS."
                WHERE article_cat_status=1 AND ".groupaccess('article_cat_visibility')."
                ".(multilang_table('AR') ? " AND ".in_group('article_cat_language', LANGUAGE) : '')."
                ORDER BY article_cat_id ASC
            ");

            if (dbrows($result) > 0) {
                while ($data = dbarray($result)) {
                    $this->sitemap->addItem($this->siteurl.'infusions/articles/articles.php?cat_id='.$data['article_cat_id'], '', $options['frequency_cat'], $options['priority_cat']);
                }
            }
        } else {
            $result = dbquery("SELECT a.article_id, a.article_datestamp, a.article_language, a.article_visibility, a.article_draft
                FROM ".DB_ARTICLES." AS a
                LEFT JOIN ".DB_ARTICLE_CATS." AS ac ON a.article_cat=ac.article_cat_id
                ".(multilang_table('AR') ? "WHERE ".in_group('a.article_language', LANGUAGE)." AND ".in_group('ac.article_cat_language', LANGUAGE)." AND " : "WHERE ")."
                a.article_draft=0 AND ".groupaccess('a.article_visibility')." AND ac.article_cat_status=1 AND ".groupaccess('ac.article_cat_visibility')."
                ORDER BY article_datestamp DESC
            ");

            if (dbrows($result) > 0) {
                while ($data = dbarray($result)) {
                    $this->sitemap->addItem($this->siteurl.'infusions/articles/articles.php?article_id='.$data['article_id'], $data['article_datestamp'], $options['frequency'], $options['priority']);
                }
            }
        }
    }

    private function blog($cats = FALSE, $base_links = TRUE, $options = []) {
        if ($base_links == TRUE) {
            $this->sitemap->addItem($this->siteurl.'infusions/blog/blog.php', '', $options['frequency'], $options['priority']);
            $this->sitemap->addItem($this->siteurl.'infusions/blog/blog.php?type=recent', '', $options['frequency'], $options['priority']);

            if ($this->settings['comments_enabled'] == 1) {
                $this->sitemap->addItem($this->siteurl.'infusions/blog/blog.php?type=comment', '', $options['frequency'], $options['priority']);
            }

            if ($this->settings['ratings_enabled'] == 1) {
                $this->sitemap->addItem($this->siteurl.'infusions/blog/blog.php?type=rating', '', $options['frequency'], $options['priority']);
            }
        }

        if ($cats == TRUE) {
            $result = dbquery("SELECT blog_cat_id, blog_cat_language
                FROM ".DB_BLOG_CATS."
                ".(multilang_column('BL') ? "WHERE ".in_group('blog_cat_language', LANGUAGE) : '')."
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
                ".(multilang_table('BL') ? "WHERE ".in_group('blog_language', LANGUAGE)." AND" : 'WHERE')." ".groupaccess('blog_visibility')." AND blog_draft=0
                AND (blog_start=0 || blog_start<=".TIME.") AND (blog_end=0 || blog_end>=".TIME.")
                ORDER BY blog_datestamp DESC
            ");

            if (dbrows($result) > 0) {
                while ($data = dbarray($result)) {
                    $this->sitemap->addItem($this->siteurl.'infusions/blog/blog.php?readmore='.$data['blog_id'], $data['blog_datestamp'], $options['frequency'], $options['priority']);
                }
            }
        }
    }

    private function customPages($options = []) {
        $result = dbquery("SELECT page_id, page_datestamp, page_language, page_status
            FROM ".DB_CUSTOM_PAGES."
            ".(multilang_table('CP') ? "WHERE page_language='".LANGUAGE."'" : 'WHERE')." AND page_status=1
            ORDER BY page_datestamp DESC
        ");

        if (dbrows($result) > 0) {
            while ($data = dbarray($result)) {
                $this->sitemap->addItem($this->siteurl.'viewpage.php?page_id='.$data['page_id'], $data['page_datestamp'], $options['frequency'], $options['priority']);
            }
        }
    }

    private function downloads($cats = FALSE, $base_links = TRUE, $options = []) {
        if ($base_links == TRUE) {
            $this->sitemap->addItem($this->siteurl.'infusions/downloads/downloads.php', '', $options['frequency'], $options['priority']);
            $this->sitemap->addItem($this->siteurl.'infusions/downloads/downloads.php?type=download', '', $options['frequency'], $options['priority']);
            $this->sitemap->addItem($this->siteurl.'infusions/downloads/downloads.php?type=recent', '', $options['frequency'], $options['priority']);

            if ($this->settings['comments_enabled'] == 1) {
                $this->sitemap->addItem($this->siteurl.'infusions/downloads/downloads.php?type=comments', '', $options['frequency'], $options['priority']);
            }

            if ($this->settings['ratings_enabled'] == 1) {
                $this->sitemap->addItem($this->siteurl.'infusions/downloads/downloads.php?type=ratings', '', $options['frequency'], $options['priority']);
            }
        }

        if ($cats == TRUE) {
            $result = dbquery("SELECT download_cat_id, download_cat_language
                FROM ".DB_DOWNLOAD_CATS."
                ".(multilang_table('DL') ? " WHERE ".in_group('download_cat_language', LANGUAGE) : '')."
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

    private function faq($options = []) {
        $this->sitemap->addItem($this->siteurl.'infusions/faq/faq.php', '', $options['frequency'], $options['priority']);

        $result = dbquery("SELECT faq_cat_id, faq_cat_language
            FROM ".DB_FAQ_CATS."
            ".(multilang_table('FQ') ? " WHERE ".in_group('faq_cat_language', LANGUAGE) : '')."
        ");

        if (dbrows($result) > 0) {
            while ($data = dbarray($result)) {
                $this->sitemap->addItem($this->siteurl.'infusions/faq/faq.php?cat_id='.$data['faq_cat_id'], '', $options['frequency'], $options['priority']);
            }
        }
    }

    private function forum($options = []) {
        $this->sitemap->addItem($this->siteurl.'infusions/forum/index.php', '', $options['frequency'], $options['priority']);
        $this->sitemap->addItem($this->siteurl.'infusions/forum/index.php?section=latest', '', $options['frequency'], $options['priority']);
        $this->sitemap->addItem($this->siteurl.'infusions/forum/index.php?section=unanswered', '', $options['frequency'], $options['priority']);
        $this->sitemap->addItem($this->siteurl.'infusions/forum/index.php?section=unsolved', '', $options['frequency'], $options['priority']);
        $this->sitemap->addItem($this->siteurl.'infusions/forum/tags.php', '', $options['frequency'], $options['priority']);

        $result_tags = dbquery("SELECT tag_id, tag_status, tag_language
            FROM ".DB_FORUM_TAGS."
            WHERE tag_status=1
            ".(multilang_table('FO') ? "AND ".in_group('tag_language', LANGUAGE) : '')."
        ");

        if (dbrows($result_tags) > 0) {
            while ($data = dbarray($result_tags)) {
                $this->sitemap->addItem($this->siteurl.'infusions/forum/tags.php?tag_id='.$data['tag_id'], '', $options['frequency'], $options['priority']);
            }
        }

        $result_forums = dbquery("SELECT forum_id, forum_access, forum_language
            FROM ".DB_FORUMS."
            ".(multilang_table('FO') ? " WHERE ".in_group('forum_language', LANGUAGE)." AND " : ' WHERE ').groupaccess('forum_access')."
        ");

        if (dbrows($result_forums) > 0) {
            while ($data = dbarray($result_forums)) {
                $this->sitemap->addItem($this->siteurl.'infusions/forum/index.php?viewforum&forum_id='.$data['forum_id'], '', $options['frequency'], $options['priority']);
            }
        }

        $result_threads = dbquery("SELECT t.thread_id, t.thread_lastpost
            FROM ".DB_FORUM_THREADS." t
            INNER JOIN ".DB_FORUMS." f ON t.forum_id=f.forum_id
            WHERE ".groupaccess('forum_access')." AND t.thread_hidden=0
        ");

        if (dbrows($result_threads) > 0) {
            while ($data = dbarray($result_threads)) {
                $this->sitemap->addItem($this->siteurl.'infusions/forum/viewthread.php?thread_id='.$data['thread_id'], $data['thread_lastpost'], $options['frequency'], $options['priority']);
            }
        }
    }

    private function gallery($albums = FALSE, $base_links = TRUE, $options = []) {
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
                LEFT JOIN ".DB_PHOTO_ALBUMS." a ON p.album_id=a.album_id
                WHERE ".groupaccess('a.album_access')."
            ");

            if (dbrows($result) > 0) {
                while ($data = dbarray($result)) {
                    $this->sitemap->addItem($this->siteurl.'infusions/gallery/gallery.php?photo_id='.$data['photo_id'], $data['photo_datestamp'], $options['frequency'], $options['priority']);
                }
            }
        }
    }

    private function news($cats = FALSE, $base_links = TRUE, $options = []) {
        if ($base_links == TRUE) {
            $this->sitemap->addItem($this->siteurl.'infusions/news/news.php', '', $options['frequency'], $options['priority']);
            $this->sitemap->addItem($this->siteurl.'infusions/news/news.php?type=recent', '', $options['frequency'], $options['priority']);

            if ($this->settings['comments_enabled'] == 1) {
                $this->sitemap->addItem($this->siteurl.'infusions/news/news.php?type=comment', '', $options['frequency'], $options['priority']);
            }

            if ($this->settings['ratings_enabled'] == 1) {
                $this->sitemap->addItem($this->siteurl.'infusions/news/news.php?type=rating', '', $options['frequency'], $options['priority']);
            }
        }

        if ($cats == TRUE) {
            $result = dbquery("SELECT news_cat_id, news_cat_visibility, news_cat_language
                FROM ".DB_NEWS_CATS."
                WHERE ".groupaccess('news_cat_visibility')."
                ".(multilang_table('NS') ? " AND ".in_group('news_cat_language', LANGUAGE) : '')."
                ORDER BY news_cat_id ASC
            ");

            if (dbrows($result) > 0) {
                while ($data = dbarray($result)) {
                    $this->sitemap->addItem($this->siteurl.'infusions/news/news.php?cat_id='.$data['news_cat_id'], '', $options['frequency_cat'], $options['priority_cat']);
                }
            }
        } else {
            $result = dbquery("SELECT news_id, news_datestamp, news_language, news_visibility, news_draft
                FROM ".DB_NEWS."
                ".(multilang_table('NS') ? "WHERE ".in_group('news_language', LANGUAGE)." AND " : "WHERE ").groupaccess('news_visibility')." AND news_draft=0
                AND (news_start=0 || news_start<='".TIME."') AND (news_end=0 || news_end>='".TIME."')
                ORDER BY news_datestamp DESC
            ");

            if (dbrows($result) > 0) {
                while ($data = dbarray($result)) {
                    $this->sitemap->addItem($this->siteurl.'infusions/news/news.php?readmore='.$data['news_id'], $data['news_datestamp'], $options['frequency'], $options['priority']);
                }
            }
        }
    }

    private function videos($cats = FALSE, $base_links = TRUE, $options = []) {
        if ($base_links == TRUE) {
            $this->sitemap->addItem($this->siteurl.'infusions/videos/videos.php', '', $options['frequency'], $options['priority']);
            $this->sitemap->addItem($this->siteurl.'infusions/videos/videos.php?type=view', '', $options['frequency'], $options['priority']);
            $this->sitemap->addItem($this->siteurl.'infusions/videos/videos.php?type=recent', '', $options['frequency'], $options['priority']);

            if ($this->settings['comments_enabled'] == 1) {
                $this->sitemap->addItem($this->siteurl.'infusions/videos/videos.php?type=comments', '', $options['frequency'], $options['priority']);
            }

            if ($this->settings['ratings_enabled'] == 1) {
                $this->sitemap->addItem($this->siteurl.'infusions/videos/videos.php?type=ratings', '', $options['frequency'], $options['priority']);
            }
        }

        if ($cats == TRUE) {
            $result = dbquery("SELECT video_cat_id, video_cat_language
                FROM ".DB_VIDEO_CATS."
                ".(multilang_table('VL') ? " WHERE ".in_group('video_cat_language', LANGUAGE) : '')."
                ORDER BY video_cat_id ASC
            ");

            if (dbrows($result) > 0) {
                while ($data = dbarray($result)) {
                    $this->sitemap->addItem($this->siteurl.'infusions/videos/videos.php?cat_id='.$data['video_cat_id'], '', $options['frequency_cat'], $options['priority_cat']);
                }
            }
        } else {
            $result = dbquery("SELECT v.*, vc.*
                FROM ".DB_VIDEOS." v
                INNER JOIN ".DB_VIDEO_CATS." vc ON v.video_cat=vc.video_cat_id
                ".(multilang_table('VL') ? "WHERE ".in_group('vc.video_cat_language', LANGUAGE)." AND" : "WHERE")." ".groupaccess('video_visibility')."
                ORDER BY v.video_datestamp DESC
            ");

            if (dbrows($result) > 0) {
                require_once INFUSIONS.'videos/functions.php';

                while ($data = dbarray($result)) {
                    $video = '';
                    if ($data['video_type'] == 'file' && !empty($data['video_file'])) {
                        $video = fusion_get_settings('siteurl').'infusions/videos/videos/'.$data['video_file'];
                    } else if ($data['video_type'] == 'url' && !empty($data['video_url'])) {
                        $video = $data['video_url'];
                    } else if ($data['video_type'] == 'embed' && !empty($data['video_embed'])) {
                        preg_match('/src="([^"]+)"/', htmlspecialchars_decode($data['video_embed']), $match);
                        $video_settings = get_settings('videos');
                        $allowd_extensions = explode(',', $video_settings['video_types']);

                        foreach ($allowd_extensions as $key => $extension) {
                            if (strpos($match[1], $extension) !== FALSE) {
                                $video = $match[1];
                            }
                        }
                    }

                    $player = '';
                    if ($data['video_type'] == 'youtube' || $data['video_type'] == 'vimeo' && !empty($data['video_url'])) {
                        $video_data = get_video_data($data['video_url'], $data['video_type']);

                        if ($data['video_type'] == 'youtube') {
                            $player = 'https://www.youtube.com/embed/'.$video_data['video_id'];
                        } else if ($data['video_type'] == 'vimeo') {
                            $player = 'https://player.vimeo.com/video/'.$video_data['video_id'];
                        }
                    }

                    $this->sitemap->setVideoOptions(TRUE, [
                        'title'       => $data['video_title'],
                        'thumbnail'   => get_video_thumb($data, TRUE),
                        'description' => $data['video_description'],
                        'video'       => $video,
                        'views'       => $data['video_views'],
                        'player_loc'  => $player
                    ]);

                    $this->sitemap->addItem($this->siteurl.'infusions/videos/videos.php?video_id='.$data['video_id'], $data['video_datestamp'], $options['frequency'], $options['priority']);
                }
            }
        }
    }

    private function webLinks($cats = FALSE, $base_links = TRUE, $options = []) {
        if ($base_links == TRUE) {
            $this->sitemap->addItem($this->siteurl.'infusions/weblinks/weblinks.php', '', $options['frequency'], $options['priority']);
        }

        if ($cats == TRUE) {
            $result = dbquery("SELECT weblink_cat_id, weblink_cat_visibility, weblink_cat_language
                FROM ".DB_WEBLINK_CATS."
                WHERE ".groupaccess('weblink_cat_visibility')."
                ".(multilang_table('WL') ? " AND ".in_group('weblink_cat_language', LANGUAGE) : '')."
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

    public function generateXml() {
        if ($this->sitemap_settings['sitemap_index'] == 1) {
            if (!is_dir($this->sitemap_folder)) {
                mkdir($this->sitemap_folder, 0777, TRUE);
            }
        }

        if ($this->customlinks) {
            $customlinks = $this->getSettings('customlinks');
            if ($customlinks['enabled'] == 1) {
                if ($this->sitemap_settings['sitemap_index'] == 1) $this->sitemap = new Sitemap($this->sitemap_folder.'sitemap_customlinks.xml');

                $this->customLinks([
                    'frequency' => $customlinks['frequency'],
                    'priority'  => $customlinks['priority']
                ]);

                if ($this->sitemap_settings['sitemap_index'] == 1) $this->sitemap->write();
            }
        }

        if ($this->profiles) {
            $profiles = $this->getSettings('profiles');
            if ($profiles['enabled'] == 1) {
                if ($this->sitemap_settings['sitemap_index'] == 1) $this->sitemap = new Sitemap($this->sitemap_folder.'sitemap_profiles.xml');

                $this->profiels([
                    'frequency' => $profiles['frequency'],
                    'priority'  => $profiles['priority']
                ]);

                if ($this->sitemap_settings['sitemap_index'] == 1) $this->sitemap->write();
            }
        }

        if ($this->articles) {
            if ($this->sitemap_settings['sitemap_index'] == 1) $this->sitemap = new Sitemap($this->sitemap_folder.'sitemap_articles.xml');

            $articles = $this->getSettings('articles');
            if ($articles['enabled'] == 1) {
                $this->articles(FALSE, TRUE, [
                    'frequency' => $articles['frequency'],
                    'priority'  => $articles['priority']
                ]);
            }

            $article_cats = $this->getSettings('article_cats');
            if ($article_cats['enabled'] == 1) {
                $this->articles(TRUE, FALSE, [
                    'frequency_cat' => $article_cats['frequency'],
                    'priority_cat'  => $article_cats['priority']
                ]);
            }

            if ($this->sitemap_settings['sitemap_index'] == 1) $this->sitemap->write();
        }

        if ($this->blogs) {
            if ($this->sitemap_settings['sitemap_index'] == 1) $this->sitemap = new Sitemap($this->sitemap_folder.'sitemap_blogs.xml');

            $blogs = $this->getSettings('blogs');
            if ($blogs['enabled'] == 1) {
                $this->blog(FALSE, TRUE, [
                    'frequency' => $blogs['frequency'],
                    'priority'  => $blogs['priority']
                ]);
            }

            $blog_cats = $this->getSettings('blog_cats');
            if ($blog_cats['enabled'] == 1) {
                $this->blog(TRUE, FALSE, [
                    'frequency_cat' => $blog_cats['frequency'],
                    'priority_cat'  => $blog_cats['priority']
                ]);
            }

            if ($this->sitemap_settings['sitemap_index'] == 1) $this->sitemap->write();
        }

        $custompages = $this->getSettings('custompages');
        if ($custompages['enabled'] == 1) {
            if ($this->sitemap_settings['sitemap_index'] == 1) $this->sitemap = new Sitemap($this->sitemap_folder.'sitemap_custompages.xml');

            $this->customPages([
                'frequency' => $custompages['frequency'],
                'priority'  => $custompages['priority']
            ]);

            if ($this->sitemap_settings['sitemap_index'] == 1) $this->sitemap->write();
        }

        if ($this->downloads) {
            if ($this->sitemap_settings['sitemap_index'] == 1) $this->sitemap = new Sitemap($this->sitemap_folder.'sitemap_downloads.xml');

            $downloads = $this->getSettings('downloads');
            if ($downloads['enabled'] == 1) {
                $this->downloads(FALSE, TRUE, [
                    'frequency' => $downloads['frequency'],
                    'priority'  => $downloads['priority']
                ]);
            }

            $download_cats = $this->getSettings('download_cats');
            if ($download_cats['enabled'] == 1) {
                $this->downloads(TRUE, FALSE, [
                    'frequency_cat' => $download_cats['frequency'],
                    'priority_cat'  => $download_cats['priority']
                ]);
            }

            if ($this->sitemap_settings['sitemap_index'] == 1) $this->sitemap->write();
        }

        if ($this->faqs) {
            $faqs = $this->getSettings('faq_cats');
            if ($faqs['enabled'] == 1) {
                if ($this->sitemap_settings['sitemap_index'] == 1) $this->sitemap = new Sitemap($this->sitemap_folder.'sitemap_faqs.xml');

                $this->faq([
                    'frequency' => $faqs['frequency'],
                    'priority'  => $faqs['priority']
                ]);

                if ($this->sitemap_settings['sitemap_index'] == 1) $this->sitemap->write();
            }
        }

        if ($this->forum) {
            $forum = $this->getSettings('forum');
            if ($forum['enabled'] == 1) {
                if ($this->sitemap_settings['sitemap_index'] == 1) $this->sitemap = new Sitemap($this->sitemap_folder.'sitemap_forum.xml');

                $this->forum([
                    'frequency' => $forum['frequency'],
                    'priority'  => $forum['priority']
                ]);

                if ($this->sitemap_settings['sitemap_index'] == 1) $this->sitemap->write();
            }
        }

        if ($this->gallery) {
            if ($this->sitemap_settings['sitemap_index'] == 1) $this->sitemap = new Sitemap($this->sitemap_folder.'sitemap_gallery.xml');

            $gallery = $this->getSettings('gallery');
            if ($gallery['enabled'] == 1) {
                $this->gallery(FALSE, TRUE, [
                    'frequency' => $gallery['frequency'],
                    'priority'  => $gallery['priority']
                ]);
            }

            $gallery_albums = $this->getSettings('gallery_albums');
            if ($gallery_albums['enabled'] == 1) {
                $this->gallery(TRUE, FALSE, [
                    'frequency_alb' => $gallery_albums['frequency'],
                    'priority_alb'  => $gallery_albums['priority']
                ]);
            }

            if ($this->sitemap_settings['sitemap_index'] == 1) $this->sitemap->write();
        }

        if ($this->news) {
            if ($this->sitemap_settings['sitemap_index'] == 1) $this->sitemap = new Sitemap($this->sitemap_folder.'sitemap_news.xml');

            $news = $this->getSettings('news');
            if ($news['enabled'] == 1) {
                $this->news(FALSE, TRUE, [
                    'frequency' => $news['frequency'],
                    'priority'  => $news['priority']
                ]);
            }

            $news_cats = $this->getSettings('news_cats');
            if ($news_cats['enabled'] == 1) {
                $this->news(TRUE, FALSE, [
                    'frequency_cat' => $news_cats['frequency'],
                    'priority_cat'  => $news_cats['priority']
                ]);
            }

            if ($this->sitemap_settings['sitemap_index'] == 1) $this->sitemap->write();
        }

        if ($this->videos) {
            if ($this->sitemap_settings['sitemap_index'] == 1) $this->sitemap = new Sitemap($this->sitemap_folder.'sitemap_videos.xml');

            $videos = $this->getSettings('videos');
            if ($videos['enabled'] == 1) {
                $this->videos(FALSE, TRUE, [
                    'frequency' => $videos['frequency'],
                    'priority'  => $videos['priority']
                ]);
            }

            $video_cats = $this->getSettings('video_cats');
            if ($video_cats['enabled'] == 1) {
                $this->videos(TRUE, FALSE, [
                    'frequency_cat' => $video_cats['frequency'],
                    'priority_cat'  => $video_cats['priority']
                ]);
            }

            if ($this->sitemap_settings['sitemap_index'] == 1) $this->sitemap->write();
        }

        if ($this->weblinks) {
            if ($this->sitemap_settings['sitemap_index'] == 1) $this->sitemap = new Sitemap($this->sitemap_folder.'sitemap_weblinks.xml');

            $weblinks = $this->getSettings('weblinks');
            if ($weblinks['enabled'] == 1) {
                $this->webLinks(FALSE, TRUE, [
                    'frequency' => $weblinks['frequency'],
                    'priority'  => $weblinks['priority']
                ]);
            }

            $weblink_cats = $this->getSettings('weblink_cats');
            if ($weblink_cats['enabled'] == 1) {
                $this->webLinks(TRUE, FALSE, [
                    'frequency_cat' => $weblink_cats['frequency'],
                    'priority_cat'  => $weblink_cats['priority']
                ]);
            }

            if ($this->sitemap_settings['sitemap_index'] == 1) $this->sitemap->write();
        }

        if ($this->sitemap_settings['sitemap_index'] == 1) {
            $index = new SitemapIndex($this->sitemap_file);

            foreach ($this->modules as $module) {
                if (file_exists($this->sitemap_folder.'sitemap_'.$module.'.xml')) {
                    $index->addSitemap($this->settings['siteurl'].str_replace('../', '', $this->sitemap_folder).'sitemap_'.$module.'.xml', time());
                }
            }

            $index->write();
        } else {
            $this->sitemap->write();
        }
    }

    private function modules() {
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

        if ($this->videos) {
            $modules['videos'] = ['locale' => '16'];
            $modules['video_cats'] = ['locale' => '17'];
        }

        if ($this->weblinks) {
            $modules['weblinks'] = ['locale' => '18'];
            $modules['weblink_cats'] = ['locale' => '19'];
        }

        $module = '';

        foreach ($modules as $name => $value) {
            $result = dbquery("SELECT * FROM ".DB_SITEMAP." WHERE name=:name", [':name' => $name]);

            while ($module_settings = dbarray($result)) {
                $module .= '<tr>';
                $module .= '<td class="col-sm-3">';
                $module .= form_checkbox('enabled_'.$name, $this->locale['smg_type_'.$value['locale']], $module_settings['enabled'], [
                    'reverse_label' => TRUE,
                    'value'         => 1
                ]);
                $module .= '</td>';

                $module .= '<td class="col-sm-6">';
                $module .= form_select('frequency_'.$name, '', $module_settings['frequency'], [
                    'inline'  => TRUE,
                    'options' => [
                        'always'  => $this->locale['smg_007'],
                        'hourly'  => $this->locale['smg_008'],
                        'daily'   => $this->locale['smg_009'],
                        'weekly'  => $this->locale['smg_010'],
                        'monthly' => $this->locale['smg_011'],
                        'yearly'  => $this->locale['smg_012'],
                        'never'   => $this->locale['smg_013']
                    ]
                ]);
                $module .= '</td>';

                $module .= '<td class="col-sm-3">';
                $module .= form_select('priority_'.$name, '', $module_settings['priority'], [
                    'inline'  => TRUE,
                    'options' => [
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

    private function getSettings($module) {
        $result = dbquery("SELECT * FROM ".DB_SITEMAP." WHERE name=:name", [':name' => $module]);

        if (dbrows($result) > 0) {
            return dbarray($result);
        } else {
            return NULL;
        }
    }

    private function sitemapAdmin() {
        if (isset($_POST['generate'])) {
            $this->generateXml();

            addNotice('success', $this->locale['smg_notice_03']);
            redirect(FUSION_SELF.fusion_get_aidlink());
        }

        if (isset($_POST['save_changes'])) {
            $available_modules = [
                'customlinks',
                'profiles',
                'articles',
                'article_cats',
                'blogs',
                'blog_cats',
                'custompages',
                'downloads',
                'download_cats',
                'faq_cats',
                'forum',
                'gallery',
                'gallery_albums',
                'news',
                'news_cats',
                'videos',
                'video_cats',
                'weblinks',
                'weblink_cats'
            ];

            $modules = [];

            foreach ($available_modules as $name) {
                $modules[$name] = [
                    'enabled'   => isset($_POST['enabled_'.$name]) ? 1 : 0,
                    'frequency' => form_sanitizer(isset($_POST['frequency_'.$name]) ? $_POST['frequency_'.$name] : '', '', 'frequency_'.$name),
                    'priority'  => form_sanitizer(isset($_POST['priority_'.$name]) ? $_POST['priority_'.$name] : '', '', 'priority_'.$name)
                ];
            }

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
            }

            addNotice('success', $this->locale['smg_notice_04']);
            redirect(FUSION_SELF.fusion_get_aidlink());
        }

        if (file_exists($this->sitemap_file)) {
            echo '<div class="well">';
                echo $this->locale['smg_001'].' '.showdate('longdate', filemtime($this->sitemap_file));
                echo '<br/><a href="'.$this->sitemap_file.'" target="_blank">'.$this->locale['smg_002'].'</a></span>';
            echo '</div>';
        }

        add_to_css('#sitemaptable .form-group {margin-bottom: 0;}');

        echo openform('savechanges', 'post', FUSION_REQUEST, ['class' => 'm-t-15']);
            echo '<div class="panel panel-default" id="sitemaptable">';
                echo '<div class="table-responsive"><table class="table table-striped">';
                    echo '<thead><tr>';
                        echo '<th></th>';
                        echo '<th>'.$this->locale['smg_006'].'</th>';
                        echo '<th>'.$this->locale['smg_014'].'</th>';
                    echo '</tr></thead>';
                    echo '<tbody>';
                        echo $this->modules();
                    echo '</tbody>';
                echo '</table></div>';

                $selectall = form_checkbox('selectall', $this->locale['smg_003'], '', [
                    'reverse_label' => TRUE,
                    'class'         => 'm-b-0'
                ]);
                $save = form_button('save_changes', $this->locale['save_changes'], 'save', ['class' => 'btn-success btn-sm']);
                echo '<div class="panel-footer"><div class="pull-left">'.$selectall.'</div><div class="text-center">'.$save.'</div></div>';
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
            echo form_button('generate', $this->locale['smg_004'], 'generate', ['class' => 'btn-default text-center']);
        echo closeform();
    }

    private function links() {
        if (isset($_POST['save'])) {
            $this->custom_links = [
                'link_id' => form_sanitizer(!empty($_GET['link_id']) ? $_GET['link_id'] : $_POST['link_id'], 0, 'link_id'),
                'url'     => form_sanitizer($_POST['url'], '', 'url'),
            ];

            if (dbcount('(link_id)', DB_SITEMAP_LINKS, "link_id='".$this->custom_links['link_id']."'")) {
                dbquery_insert(DB_SITEMAP_LINKS, $this->custom_links, 'update');
                if (\defender::safe()) {
                    addNotice('success', $this->locale['smg_notice_01']);

                    redirect(FUSION_SELF.fusion_get_aidlink().'&section=links');
                }
            } else {
                dbquery_insert(DB_SITEMAP_LINKS, $this->custom_links, 'save');

                if (\defender::safe()) {
                    addNotice('success', $this->locale['smg_notice_02']);
                    redirect(FUSION_SELF.fusion_get_aidlink().'&section=links');
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
            if (dbrows($result)) {
                dbquery("DELETE FROM ".DB_SITEMAP_LINKS." WHERE link_id='".intval($_GET['link_id'])."'");
            }

            addNotice('success', $this->locale['smg_notice_06']);
            redirect(INFUSIONS.'sitemap_panel/admin.php'.fusion_get_aidlink());
        }

        if (isset($_POST['cancel'])) {
            redirect(FUSION_SELF.fusion_get_aidlink());
        }

        echo '<div class="row m-t-30">';
            echo '<div class="col-xs-12 col-sm-6">';
                openside();
                    echo openform('addlink', 'post', FUSION_REQUEST);
                    echo form_hidden('link_id', '', $this->custom_links['link_id']);
                    echo form_text('url', $this->locale['smg_005'], $this->custom_links['url'], ['type' => 'url', 'required' => TRUE, 'placeholder' => 'https://example.com/']);
                    echo form_button('save', $this->locale['save'], 'save', ['class' => 'btn-success']);
                    echo form_button('cancel', $this->locale['cancel'], 'cancel');
                    echo closeform();
                closeside();
            echo '</div>';

            echo '<div class="col-xs-12 col-sm-6">';
                $result = dbquery("SELECT * FROM ".DB_SITEMAP_LINKS);

                if (dbrows($result) > 0) {
                    openside($this->locale['smg_type_01']);
                    while ($data = dbarray($result)) {
                        echo '<div>';
                            echo '<span class="badge">'.$data['url'].'</span> ';
                            echo '<span class="pull-right">';
                                echo '<a href="'.FUSION_SELF.fusion_get_aidlink().'&section=links&action=edit&link_id='.$data['link_id'].'">'.$this->locale['edit'].'</a>';
                                echo ' | ';
                                echo '<a href="'.FUSION_SELF.fusion_get_aidlink().'&section=links&action=delete&link_id='.$data['link_id'].'">'.$this->locale['delete'].'</a>';
                            echo '</span>';
                        echo '</div>';
                    }
                    closeside();
                }
            echo '</div>';
        echo '</div>';
    }

    private function settings() {
        if (isset($_POST['save_settings'])) {
            $settings = [
                'auto_update'     => isset($_POST['auto_update']) ? 1 : 0,
                'update_interval' => form_sanitizer(($_POST['update_interval'] * 60 * 60), '', 'update_interval'),
                'sitemap_index'   => form_sanitizer($_POST['sitemap_index'], '0', 'sitemap_index'),
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
            }

            addNotice('success', $this->locale['smg_notice_05']);
            redirect(FUSION_REQUEST);
        }

        echo openform('savesettings', 'post', FUSION_REQUEST, ['class' => 'm-t-15']);
        openside();
            $update_interval = $this->sitemap_settings['update_interval'] / 60 / 60;
            echo form_text('update_interval', $this->locale['smg_016'], $update_interval, ['type' => 'number', 'inline' => TRUE]);
            echo form_checkbox('auto_update', $this->locale['smg_015'], $this->sitemap_settings['auto_update'], ['reverse_label' => TRUE]);

            echo form_select('sitemap_index', $this->locale['smg_018'], $this->sitemap_settings['sitemap_index'], [
                'options' => [1 => $this->locale['yes'], 0 => $this->locale['no']],
                'inline'  => TRUE
            ]);

            echo form_button('save_settings', $this->locale['save'], 'save', ['class' => 'btn-success m-t-5']);
        closeside();
        echo closeform();
    }

    public function displayAdmin() {
        pageAccess('SMG');

        add_to_title($this->locale['smg_title_admin']);

        add_breadcrumb(['link' => FUSION_REQUEST, 'title' => $this->locale['smg_title_admin']]);

        opentable($this->locale['smg_title_admin']);

        $allowed_section = ['sitemap', 'links', 'settings'];
        $_GET['section'] = isset($_GET['section']) && in_array($_GET['section'], $allowed_section) ? $_GET['section'] : 'sitemap';

        $tab['title'][] = $this->locale['smg_title'];
        $tab['id'][] = 'sitemap';
        $tab['icon'][] = 'fa fa-sitemap';

        $tab['title'][] = $this->locale['smg_type_01'];
        $tab['id'][] = 'links';
        $tab['icon'][] = 'fa fa-link';

        $tab['title'][] = $this->locale['smg_017'];
        $tab['id'][] = 'settings';
        $tab['icon'][] = 'fa fa-cog';

        echo opentab($tab, $_GET['section'], 'sitemapadmin', TRUE, 'nav-tabs m-b-20');
        switch ($_GET['section']) {
            case 'links':
                add_breadcrumb(['link' => FUSION_REQUEST, 'title' => $this->locale['smg_type_01']]);
                $this->links();
                break;
            case 'settings':
                add_breadcrumb(['link' => FUSION_REQUEST, 'title' => $this->locale['smg_017']]);
                $this->settings();
                break;
            default:
                $this->sitemapAdmin();
        }
        echo closetab();

        closetable();
    }
}
