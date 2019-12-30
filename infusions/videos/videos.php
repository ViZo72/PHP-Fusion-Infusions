<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: videos/videos.php
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

if (!defined('VIDEOS_EXIST')) {
    redirect(BASEDIR.'error.php?code=404');
}

require_once THEMES.'templates/header.php';
require_once INCLUDES.'infusions_include.php';
require_once VIDEOS.'templates/videos.php';
require_once INFUSIONS.'videos/OpenGraphVideos.php';
require_once INFUSIONS.'videos/functions.php';

$locale = fusion_get_locale('', VID_LOCALE);
$userdata = fusion_get_userdata();
$video_settings = get_settings('videos');

$video_settings['video_pagination'] = !empty($video_settings['video_pagination']) ? $video_settings['video_pagination'] : 15;

add_breadcrumb(['link' => INFUSIONS.'videos/videos.php', 'title' => \PHPFusion\SiteLinks::get_current_SiteLinks('infusions/videos/videos.php', 'link_name')]);

if (file_exists(INFUSIONS.'rss_feeds_panel/feeds/rss_videos.php')) {
    add_to_head('<link rel="alternate" type="application/rss+xml" title="'.fusion_get_locale('vid_title').' - RSS Feed" href="'.fusion_get_settings('siteurl').'infusions/rss_feeds_panel/feeds/rss_videos.php"/>');
}

$info = [
    'video_title'        => $locale['vid_067'],
    'video_language'     => LANGUAGE,
    'video_categories'   => get_video_cats(),
    'video_last_updated' => 0,
    'video_max_rows'     => 0,
    'video_rows'         => 0,
    'video_nav'          => ''
];

$info['allowed_filters'] = [
    'view'   => $locale['vid_068'],
    'recent' => $locale['vid_069']
];

if (fusion_get_settings('comments_enabled') == 1) {
    $info['allowed_filters']['comments'] = $locale['vid_070'];
}

if (fusion_get_settings('ratings_enabled') == 1) {
    $info['allowed_filters']['ratings'] = $locale['vid_071'];
}

$filter = array_keys($info['allowed_filters']);
$_GET['type'] = isset($_GET['type']) && in_array($_GET['type'], array_keys($info['allowed_filters'])) ? $_GET['type'] : '';

foreach ($info['allowed_filters'] as $type => $filter_name) {
    $filter_link = VIDEOS.'videos.php?'.(isset($_GET['cat_id']) ? 'cat_id='.$_GET['cat_id'].'&amp;' : '').(isset($_GET['archive']) ? 'archive='.$_GET['archive'].'&amp;' : '').'type='.$type;
    $active = isset($_GET['type']) && $_GET['type'] == $type ? 1 : 0;
    $info['video_filter'][$type] = ['title' => $filter_name, 'link' => $filter_link, 'active' => $active];
    unset($filter_link);
}

switch ($_GET['type']) {
    case 'recent':
        $filter_condition = 'video_datestamp DESC';
        break;
    case 'comments':
        $filter_condition = 'count_comment DESC';
        $filter_count = 'COUNT(c.comment_item_id) AS count_comment,';
        $filter_join = "LEFT JOIN ".DB_COMMENTS." c ON c.comment_item_id = v.video_id AND c.comment_type='VID' AND c.comment_hidden='0'";
        break;
    case 'ratings':
        $filter_condition = 'sum_rating DESC';
        $filter_count = 'IF(SUM(r.rating_vote) > 0, SUM(r.rating_vote), 0) AS sum_rating, COUNT(r.rating_item_id) AS count_votes,';
        $filter_join = "LEFT JOIN ".DB_RATINGS." r ON r.rating_item_id = v.video_id AND r.rating_type='VID'";
        break;
    case 'view':
        $filter_condition = 'video_views DESC';
        break;
    default:
        $filter_condition = '';
}

if (isset($_GET['video_id'])) {
    if (validate_video($_GET['video_id'])) {
        dbquery("UPDATE ".DB_VIDEOS." SET video_views=video_views+1 WHERE video_id='".intval($_GET['video_id'])."'");

        $result = dbquery("SELECT v.*, vc.*, u.user_id, u.user_name, u.user_status, u.user_avatar, u.user_level, u.user_joined, SUM(r.rating_vote) AS sum_rating, COUNT(r.rating_item_id) AS count_votes, v.video_datestamp AS last_updated
            FROM ".DB_VIDEOS." v
            INNER JOIN ".DB_VIDEO_CATS." vc ON v.video_cat=vc.video_cat_id
            LEFT JOIN ".DB_USERS." u ON v.video_user=u.user_id
            LEFT JOIN ".DB_RATINGS." r ON r.rating_item_id = v.video_id AND r.rating_type='V'
            ".(multilang_table('VL') ? "WHERE ".in_group('vc.video_cat_language', LANGUAGE)." AND" : 'WHERE')." ".groupaccess('video_visibility')." AND v.video_id='".intval($_GET['video_id'])."'
            GROUP BY v.video_id
        ");

        $info['video_rows'] = dbrows($result);

        if ($info['video_rows'] > 0) {
            include_once INCLUDES.'comments_include.php';
            include_once INCLUDES.'ratings_include.php';

            $data = dbarray($result);

            $data['video_user_like_type'] = '';

            if (iMEMBER) {
                $like_type_result = dbquery("SELECT * FROM ".DB_VIDEO_LIKES." WHERE video_id=:video_id AND like_user=:user_id", [
                    ':video_id' => $data['video_id'],
                    ':user_id'  => $userdata['user_id']
                ]);

                if (dbrows($like_type_result) > 0) {
                    $like_type_data = dbarray($like_type_result);
                    $data['video_user_like_type'] = $like_type = $like_type_data['like_type'];

                    if (isset($_GET['action'])) {
                        if (\defender::safe()) {
                            switch ($_GET['action']) {
                                case 'like':
                                    if (dbrows($like_type_result) == 0) {
                                        dbquery_insert(DB_VIDEO_LIKES, [
                                            'video_id'  => $data['video_id'],
                                            'like_user' => $userdata['user_id'],
                                            'like_type' => 'like'
                                        ], 'save');
                                    }
                                    if ($like_type === 'dislike') {
                                        dbquery("UPDATE ".DB_VIDEO_LIKES." SET like_type='like' WHERE video_id=:video_id AND like_user=:user_id", [
                                            ':video_id' => $data['video_id'],
                                            ':user_id'  => $userdata['user_id']
                                        ]);
                                    }
                                    break;
                                case 'dislike':
                                    if (dbrows($like_type_result) == 0) {
                                        dbquery_insert(DB_VIDEO_LIKES, [
                                            'video_id'  => $data['video_id'],
                                            'like_user' => $userdata['user_id'],
                                            'like_type' => 'dislike'
                                        ], 'save');
                                    }
                                    if ($like_type === 'like') {
                                        dbquery("UPDATE ".DB_VIDEO_LIKES." SET like_type='dislike' WHERE video_id=:video_id AND like_user=:user_id", [
                                            ':video_id' => $data['video_id'],
                                            ':user_id'  => $userdata['user_id']
                                        ]);
                                    }
                                    break;
                                case 'unlike':
                                case 'undislike':
                                    dbquery("DELETE FROM ".DB_VIDEO_LIKES." WHERE video_id=:video_id AND like_user=:user_id", [':video_id' => $data['video_id'], ':user_id' => $userdata['user_id']]);
                                    break;
                                default:
                                    break;
                            }
                        }

                        redirect(INFUSIONS.'videos/videos.php?video_id='.$data['video_id']);
                    }
                }
            }

            $data['video_description'] = nl2br(parse_textarea($data['video_description'], FALSE, FALSE, TRUE, FALSE));
            $data['video_post_author'] = profile_link($data['user_id'], $data['user_name'], $data['user_status']);
            $data['video_post_author_avatar'] = display_avatar($data, '45px', '', TRUE, 'img-circle');
            $data['video_post_cat'] = '<a href="'.INFUSIONS.'videos/videos.php?cat_id='.$data['video_cat_id'].'">'.$data['video_cat_name'].'</a>';
            $data['video_post_time'] = showdate('shortdate', $data['video_datestamp']);
            $data['video_views'] = format_word($data['video_views'], $locale['fmt_views']);

            $like = iMEMBER && $data['video_user_like_type'] == 'like' ? 'unlike' : 'like';
            $dislike = iMEMBER && $data['video_user_like_type'] == 'dislike' ? 'undislike' : 'dislike';

            $data['video_like_url'] = INFUSIONS.'videos/videos.php?video_id='.$data['video_id'].'&amp;action='.$like;
            $data['video_dislike_url'] = INFUSIONS.'videos/videos.php?video_id='.$data['video_id'].'&amp;action='.$dislike;
            $data['video_likes'] = get_likes($data['video_id'], 'like');
            $data['video_dislikes'] = get_likes($data['video_id'], 'dislike');

            $video_id = '';
            if ($data['video_type'] == 'youtube' || $data['video_type'] == 'vimeo') {
                $video_data = get_video_data($data['video_url'], $data['video_type']);
                $video_id = $video_data['video_id'];
            }

            $video = '';
            if ($data['video_type'] == 'file' && !empty($data['video_file'])) {
                $video = '<div class="embed-responsive embed-responsive-16by9"><video class="embed-responsive-item" controls><source src="'.VIDEOS.'videos/'.$data['video_file'].'"></video></div>';
            } else if ($data['video_type'] == 'url' && !empty($data['video_url'])) {
                $video = '<div class="embed-responsive embed-responsive-16by9"><video class="embed-responsive-item" controls><source src="'.$data['video_url'].'"></video></div>';
            } else if ($data['video_type'] == 'youtube' && !empty($data['video_url'])) {
                $video = '<div class="embed-responsive embed-responsive-16by9"><iframe class="embed-responsive-item" src="https://www.youtube.com/embed/'.$video_id.'" allowfullscreen></iframe></div>';
            } else if ($data['video_type'] == 'vimeo' && !empty($data['video_url'])) {
                $video = '<div class="embed-responsive embed-responsive-16by9"><iframe class="embed-responsive-item" src="https://player.vimeo.com/video/'.$video_id.'" allowfullscreen></iframe></div>';
            } else if ($data['video_type'] == 'embed' && !empty($data['video_embed'])) {
                $video = htmlspecialchars_decode($data['video_embed']);
            }

            $data['video_video'] = $video;

            $data['admin_link'] = '';
            if (iADMIN && checkrights('VID')) {
                $data['admin_link'] = [
                    'edit'   => INFUSIONS.'videos/admin.php'.$aidlink.'&amp;action=edit&amp;section=form&amp;video_id='.$data['video_id'],
                    'delete' => INFUSIONS.'videos/admin.php'.$aidlink.'&amp;action=delete&amp;section=form&amp;video_id='.$data['video_id']
                ];
            }

            add_breadcrumb(['link' => INFUSIONS.'videos/videos.php?cat_id='.$data['video_cat_id'], 'title' => $data['video_cat_name']]);
            add_breadcrumb(['link' => INFUSIONS.'videos/videos.php?video_id='.$_GET['video_id'], 'title' => $data['video_title']]);

            set_title(\PHPFusion\SiteLinks::get_current_SiteLinks('infusions/videos/videos.php', 'link_name').$locale['global_201']);
            add_to_title($data['video_title']);
            add_to_meta($data['video_title'].($data['video_keywords'] ? ','.$data['video_keywords'] : ''));

            if ($data['video_keywords'] !== '') {
                set_meta('keywords', $data['video_keywords']);
            }

            $data['video_show_comments'] = get_video_comments($data);
            $data['video_show_ratings'] = get_video_ratings($data);

            $info['video_item'] = $data;

            OpenGraphVideos::ogVideo($_GET['video_id']);
        } else {
            redirect(VIDEOS.'videos.php');
        }
    } else {
        redirect(VIDEOS.'videos.php');
    }
} else {
    $condition = '';
    if (isset($_GET['author']) && isnum($_GET['author'])) {
        $condition = "AND video_user = '".intval($_GET['author'])."'";
    }

    if (isset($_GET['cat_id']) && isnum($_GET['cat_id'])) {
        set_title($locale['vid_title']);
        set_meta('name', $locale['vid_title']);

        $res = dbarray(dbquery("SELECT * FROM ".DB_VIDEO_CATS.(multilang_table('VL') ? " WHERE ".in_group('video_cat_language', LANGUAGE)." AND " : " WHERE ")."video_cat_id='".intval($_GET['cat_id'])."'"));
        if (!empty($res)) {
            $info += $res;
        } else {
            redirect(clean_request('', ['cat_id'], FALSE));
        }

        video_cats_breadcrumbs(get_video_cats_index());

        $info['video_max_rows'] = dbcount("('video_id')", DB_VIDEOS, "video_cat='".intval($_GET['cat_id'])."' AND ".groupaccess('video_visibility'));
        $_GET['rowstart'] = (isset($_GET['rowstart']) && isnum($_GET['rowstart']) && $_GET['rowstart'] <= $info['video_max_rows']) ? $_GET['rowstart'] : 0;

        if ($info['video_max_rows']) {
            switch ($_GET['type']) {
                case 'recent':
                    $filter_condition = 'video_datestamp DESC';
                    break;
                case 'comments':
                    $filter_condition = 'count_comment DESC';
                    $filter_count = 'COUNT(c.comment_item_id) AS count_comment,';
                    $filter_join = "LEFT JOIN ".DB_COMMENTS." c ON c.comment_item_id = v.video_id AND c.comment_type='VID' AND c.comment_hidden='0'";
                    break;
                case 'ratings':
                    $filter_condition = 'sum_rating DESC';
                    $filter_count = 'IF(SUM(r.rating_vote) > 0, SUM(r.rating_vote), 0) AS sum_rating, COUNT(r.rating_item_id) AS count_votes,';
                    $filter_join = "LEFT JOIN ".DB_RATINGS." r ON r.rating_item_id = v.video_id AND r.rating_type='VID'";
                    break;
                case 'view':
                    $filter_condition = 'video_views DESC';
                    break;
                default:
                    $filter_condition = dbresult(dbquery("SELECT video_cat_sorting FROM ".DB_VIDEO_CATS." WHERE video_cat_id='".intval($_GET['cat_id'])."'"), 0);
            }

            $result = dbquery("SELECT v.*, vc.*, u.user_id, u.user_name, u.user_status, u.user_avatar , u.user_level, u.user_joined, ".(!empty($filter_count) ? $filter_count : '')." MAX(v.video_datestamp) as last_updated
                FROM ".DB_VIDEOS." v
                INNER JOIN ".DB_VIDEO_CATS." vc ON v.video_cat=vc.video_cat_id
                LEFT JOIN ".DB_USERS." u ON v.video_user=u.user_id
                ".(!empty($filter_join) ? $filter_join : '')."
                ".(multilang_table('VL') ? " WHERE ".in_group('video_cat_language', LANGUAGE)." AND " : " WHERE ")." ".groupaccess('video_visibility')."
                AND v.video_cat = '".intval($_GET['cat_id'])."'
                GROUP BY v.video_id
                ORDER BY ".(!empty($filter_condition) ? $filter_condition : 'vc.video_cat_sorting')."
                LIMIT ".intval($_GET['rowstart']).', '.intval($video_settings['video_pagination'])
            );

            $info['video_rows'] = dbrows($result);
        }

        OpenGraphVideos::ogVideoCat($_GET['cat_id']);
    } else {
        set_title($locale['vid_title']);

        $info['video_max_rows'] = dbcount("('video_id')", DB_VIDEOS, groupaccess('video_visibility'));
        $_GET['rowstart'] = (isset($_GET['rowstart']) && isnum($_GET['rowstart']) && $_GET['rowstart'] <= $info['video_max_rows']) ? $_GET['rowstart'] : 0;

        if ($info['video_max_rows'] > 0) {
            $result = dbquery("SELECT v.*, vc.*, u.user_id, u.user_name, u.user_status, u.user_avatar, u.user_level, u.user_joined, ".(!empty($filter_count) ? $filter_count : '')." MAX(v.video_datestamp) AS last_updated
                FROM ".DB_VIDEOS." v
                INNER JOIN ".DB_VIDEO_CATS." vc ON v.video_cat=vc.video_cat_id
                LEFT JOIN ".DB_USERS." u ON v.video_user=u.user_id
                ".(!empty($filter_join) ? $filter_join : '')."
                ".(multilang_table('VL') ? "WHERE ".in_group('vc.video_cat_language', LANGUAGE)." AND" : "WHERE")." ".groupaccess('video_visibility')."
                ".$condition."
                GROUP BY v.video_id
                ORDER BY ".($filter_condition ? $filter_condition : "vc.video_cat_sorting")."
                LIMIT ".intval($_GET['rowstart']).",".intval($video_settings['video_pagination'])
            );

            $info['video_rows'] = dbrows($result);
        }
    }
}

if (!empty($info['video_max_rows']) && ($info['video_max_rows'] > $video_settings['video_pagination']) && !isset($_GET['video_id'])) {
    $page_nav_link = (!empty($_GET['type']) ? '?type='.$_GET['type'].'&amp;' : '');

    if (!empty($_GET['cat_id']) && isnum($_GET['cat_id'])) {
        $page_nav_link = INFUSIONS.'videos/videos.php?cat_id='.$_GET['cat_id'].(!empty($_GET['type']) ? '&amp;type='.$_GET['type'] : '').'&amp;';
    } else if (!empty($_GET['author']) && isnum($_GET['author'])) {
        $page_nav_link = INFUSIONS.'videos/videos.php?author='.$_GET['author'].'&amp;';
    }

    $info['video_nav'] = makepagenav($_GET['rowstart'], $video_settings['video_pagination'], $info['video_max_rows'], 3, $page_nav_link);
}

if (!empty($info['video_rows'])) {
    while ($data = dbarray($result)) {
        $data['count_comment'] = !empty($data['count_comment']) ? $data['count_comment'] : count_db($data['video_id'], 'VID');
        $data['count_votes'] = !empty($data['count_votes']) ? $data['count_votes'] : sum_db($data['video_id'], 'VID');
        $data['sum_rating'] = !empty($data['sum_rating']) ? $data['sum_rating'] : rating_db($data['video_id'], 'VID');

        $data = array_merge($data, parse_video_info($data));
        $info['video_item'][$data['video_id']] = $data;
    }
}

$author_result = dbquery("SELECT v.video_title, v.video_user, COUNT(v.video_id) as video_count, u.user_id, u.user_name, u.user_status
    FROM ".DB_VIDEOS." v
    INNER JOIN ".DB_USERS." u ON (v.video_user = u.user_id)
    GROUP BY v.video_user ORDER BY v.video_user ASC
");

if (dbrows($author_result)) {
    while ($at_data = dbarray($author_result)) {
        $active = isset($_GET['author']) && $_GET['author'] == $at_data['video_user'] ? 1 : 0;
        $info['video_author'][$at_data['video_user']] = [
            'title'  => $at_data['user_name'],
            'link'   => INFUSIONS.'videos/videos.php?author='.$at_data['video_user'],
            'count'  => $at_data['video_count'],
            'active' => $active
        ];
    }
}

render_videos($info);

require_once THEMES.'templates/footer.php';

function get_video_cats() {
    $data = dbquery_tree_full(DB_VIDEO_CATS, 'video_cat_id', 'video_cat_parent', (multilang_table('VL') ? "WHERE ".in_group('video_cat_language', LANGUAGE) : ''));

    foreach ($data as $index => $cat_data) {
        foreach ($cat_data as $video_cat_id => $cat) {
            $data[$index][$video_cat_id]['video_cat_link'] = '<a title="'.$cat['video_cat_description'].'" href="'.VIDEOS.'videos.php?cat_id='.$cat['video_cat_id'].'">'.$cat['video_cat_name'].'</a>';
        }
    }

    return $data;
}

function validate_video($id) {
    if (isnum($id)) {
        return (int)dbcount("('video_id')", DB_VIDEOS, "video_id='".intval($id)."'");
    }

    return (int)FALSE;
}

function get_video_comments($data) {
    $html = '';
    if (fusion_get_settings('comments_enabled') && $data['video_allow_comments']) {
        ob_start();
        showcomments('VID', DB_VIDEOS, 'video_id', $data['video_id'], INFUSIONS.'videos/videos.php?cat_id='.$data['video_cat'].'&amp;video_id='.$data['video_id'], $data['video_allow_ratings']);
        $html = ob_get_contents();
        ob_end_clean();
    }

    return (string)$html;
}

function get_video_ratings($data) {
    $html = '';
    if (fusion_get_settings('ratings_enabled') && $data['video_allow_ratings']) {
        ob_start();
        showratings('VID', $data['video_id'], INFUSIONS.'videos/videos.php?cat_id='.$data['video_cat'].'&amp;video_id='.$data['video_id']);
        $html = ob_get_contents();
        ob_end_clean();
    }

    return (string)$html;
}

function video_cats_breadcrumbs($index) {
    $locale = fusion_get_locale();

    function breadcrumb_arrays($index, $id) {
        $crumb = [];
        if (isset($index[get_parent($index, $id)])) {
            $_name = dbarray(dbquery("SELECT video_cat_id, video_cat_name, video_cat_parent FROM ".DB_VIDEO_CATS.(multilang_table('VL') ? " WHERE ".in_group('video_cat_language', LANGUAGE)." AND " : " WHERE ")." video_cat_id='".intval($id)."'"));

            $crumb = [
                'link'  => INFUSIONS.'videos/videos.php?cat_id='.$_name['video_cat_id'],
                'title' => $_name['video_cat_name']
            ];

            if (isset($index[get_parent($index, $id)])) {
                if (get_parent($index, $id) == 0) {
                    return $crumb;
                }

                $crumb_1 = breadcrumb_arrays($index, get_parent($index, $id));
                $crumb = array_merge_recursive($crumb, $crumb_1);
            }
        }

        return $crumb;
    }

    $crumb = breadcrumb_arrays($index, $_GET['cat_id']);
    $title_count = !empty($crumb['title']) && is_array($crumb['title']) ? count($crumb['title']) > 1 : 0;

    if ($title_count) {
        krsort($crumb['title']);
        krsort($crumb['link']);
    }

    if ($title_count) {
        foreach ($crumb['title'] as $i => $value) {
            add_breadcrumb(['link' => $crumb['link'][$i], 'title' => $value]);

            if ($i == count($crumb['title']) - 1) {
                add_to_title($locale['global_201'].$value);
                add_to_meta($value);
            }
        }
    } else if (isset($crumb['title'])) {
        add_to_title($locale['global_201'].$crumb['title']);
        add_to_meta($crumb['title']);
        add_breadcrumb(['link' => $crumb['link'], 'title' => $crumb['title']]);
    }
}

function get_video_cats_index() {
    return dbquery_tree(DB_VIDEO_CATS, 'video_cat_id', 'video_cat_parent',"".(multilang_table('VL') ? "WHERE ".in_group('video_cat_language', LANGUAGE) : '')."");
}

function rating_db($id, $type) {
    $count_db = dbarray(dbquery("SELECT IF(SUM(rating_vote) > 0, SUM(rating_vote), 0) AS sum_rating
        FROM ".DB_RATINGS."
        WHERE rating_item_id='".$id."' AND rating_type='".$type."'
    "));

    return $count_db['sum_rating'];
}

function sum_db($id, $type) {
    $count_db = dbarray(dbquery("SELECT COUNT(rating_item_id) AS count_votes
        FROM ".DB_RATINGS."
        WHERE rating_item_id='".$id."' AND rating_type='".$type."'
    "));

    return $count_db['count_votes'];
}

function count_db($id, $type) {
    $count_db = dbarray(dbquery("SELECT COUNT(comment_item_id) AS count_comment
        FROM ".DB_COMMENTS."
        WHERE comment_item_id='".$id."' AND comment_type='".$type."' AND comment_hidden='0'
    "));

    return $count_db['count_comment'];
}

function get_likes($id, $type) {
    $count_db = dbarray(dbquery("SELECT COUNT(video_id) AS count_likes
        FROM ".DB_VIDEO_LIKES."
        WHERE video_id='".$id."' AND like_type='".$type."'
    "));

    return $count_db['count_likes'];
}

function parse_video_info($data) {
    $locale = fusion_get_locale();

    return [
        'video_image'       => get_video_thumb($data),
        'video_user_avatar' => display_avatar($data, '25px', '', TRUE, 'img-rounded'),
        'video_user_link'   => profile_link($data['user_id'], $data['user_name'], $data['user_status']),
        'video_post_time'   => showdate('shortdate', $data['video_datestamp']),
        'video_views'       => format_word($data['video_views'], $locale['fmt_views'])
    ];
}
