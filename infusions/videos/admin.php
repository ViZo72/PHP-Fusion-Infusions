<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: videos/admin.php
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
require_once INFUSIONS.'videos/functions.php';

pageAccess('VID');


class VideosAdmin {
    private $locale = [];
    private $video_settings = [];

    public function __construct() {
        $this->locale = fusion_get_locale('', VID_LOCALE);
        $this->video_settings = get_settings('videos');

        if (isset($_GET['section']) && $_GET['section'] == 'back') {
            redirect(FUSION_SELF.fusion_get_aidlink());
        }
    }

    private function listing() {
        $aidlink = fusion_get_aidlink();

        $limit = 15;
        $total_rows = dbcount("(video_id)", DB_VIDEOS);
        $rowstart = isset($_GET['rowstart']) && ($_GET['rowstart'] <= $total_rows) ? $_GET['rowstart'] : 0;

        $cat_opts['all'] = $this->locale['vid_005'];

        $categories = dbquery("SELECT video_cat_id, video_cat_name FROM ".DB_VIDEO_CATS." ".(multilang_table('VL') ? "WHERE video_cat_language='".LANGUAGE."'" : ""));

        if (dbrows($categories) > 0) {
            while ($cat_data = dbarray($categories)) {
                $cat_opts[$cat_data['video_cat_id']] = $cat_data['video_cat_name'];
            }
        }

        $cat_filter = '';
        if (isset($_GET['filter_cid']) && isnum($_GET['filter_cid']) && isset($cat_opts[$_GET['filter_cid']])) {
            if ($_GET['filter_cid'] > 0) {
                $cat_filter = "video_cat='".intval($_GET['filter_cid'])."'";
            }
        }

        $lang_filter = multilang_table('VL') ? "video_cat_language='".LANGUAGE."'" : '';

        if ($cat_filter && $lang_filter) {
            $filter = $cat_filter." AND ".$lang_filter;
        } else {
            $filter = $cat_filter.$lang_filter;
        }

        $result = dbquery("SELECT v.*, vc.video_cat_id, vc.video_cat_name
            FROM ".DB_VIDEOS." v
            INNER JOIN ".DB_VIDEO_CATS." vc on v.video_cat = vc.video_cat_id
            ".($filter ? "WHERE $filter " : '')."
            ORDER BY v.video_datestamp DESC LIMIT $rowstart, $limit
        ");

        $rows = dbrows($result);

        echo '<div class="clearfix m-b-10">';
            echo '<span class="pull-right">'.sprintf($this->locale['vid_006'], $rows, $total_rows).'</span>';

            if (!empty($cat_opts) > 0 && $total_rows > 0) {
                echo '<div class="dropdown pull-left m-r-10">';
                    echo '<a class="btn btn-default btn-sm dropdown-toggle" style="width: 200px;" data-toggle="dropdown" aria-expanded="false">';
                        if (isset($_GET['filter_cid']) && isset($cat_opts[$_GET['filter_cid']])) {
                            echo $cat_opts[$_GET['filter_cid']];
                        } else {
                            echo $this->locale['vid_007'];
                        }
                        echo ' <span class="caret"></span>';
                    echo '</a>';

                    echo '<ul class="dropdown-menu" style="max-height: 180px; width: 200px; overflow-y: auto;">';
                        foreach ($cat_opts as $cat_id => $cat_name) {
                            $active = isset($_GET['filter_cid']) && $_GET['filter_cid'] == $cat_id ? TRUE : FALSE;

                            echo '<li'.($active ? 'class="active"' : '').'>';
                                echo '<a class="text-smaller" href="'.clean_request('filter_cid='.$cat_id, ['section', 'rowstart', 'aid'], TRUE).'">';
                                    echo $cat_name;
                                echo '</a>';
                            echo '</li>';
                        }
                    echo '</ul>';
                echo '</div>';
            }

            if ($total_rows > $rows) {
                echo makepagenav($rowstart, $limit, $total_rows, $limit, clean_request('', ['aid', 'section'], TRUE).'&amp;');
            }
        echo '</div>';

        if ($rows > 0) {
            echo '<div class="row equal-height">';
                while ($data = dbarray($result)) {
                    echo '<div class="col-xs-12 col-sm-4">';
                        echo '<div class="panel panel-default"><div class="panel-body">';
                        echo '<div class="pull-left m-r-10">';
                            echo '<div class="display-inline-block image-wrap thumb text-center overflow-hide">';
                                echo '<img style="object-fit: contain;height: 100px; width: 100px;" class="img-responsive" src="'.get_video_thumb($data).'" alt="'.$data['video_title'].'"/>';
                            echo '</div>';
                        echo '</div>';

                        echo '<div class="overflow-hide">';
                            echo '<span class="strong text-dark">'.$data['video_title'].'</span><br/>';

                            echo '<div>';
                                echo $this->locale['vid_009'].' <a class="badge" href="'.FUSION_SELF.$aidlink.'&amp;section=categories&amp;action=edit&amp;cat_id='.$data['video_cat_id'].'">'.$data['video_cat_name'].'</a>';
                                echo '<br/><span><i class="fa fa-clock-o"></i> '.$data['video_length'].'</span>';
                            echo '</div>';

                            echo '<div class="m-t-5">';
                                echo '<a class="m-r-10" href="'.FUSION_SELF.$aidlink.'&amp;action=edit&amp;section=form&amp;video_id='.$data['video_id'].'">'.$this->locale['edit'].'</a>';
                                echo '<a class="m-r-10" href="'.FUSION_SELF.$aidlink.'&amp;action=delete&amp;section=form&amp;video_id='.$data['video_id'].'">'.$this->locale['delete'].'</a>';
                            echo '</div>';
                        echo '</div>';
                        echo '</div></div>';
                    echo '</div>';
                }
            echo '</div>';
        } else {
            echo '<div class="well text-center">'.$this->locale['vid_008'].'</div>';
        }
    }

    public function displayAdmin() {
        add_to_title($this->locale['vid_title']);

        add_breadcrumb(['link' => INFUSIONS.'videos/admin.php'.fusion_get_aidlink(), 'title' => $this->locale['vid_title']]);

        $edit = (isset($_GET['action']) && $_GET['action'] == 'edit') && isset($_GET['video_id']) ? TRUE : FALSE;

        if (!empty($_GET['section'])) {
            switch ($_GET['section']) {
                case 'form':
                    add_breadcrumb(['link' => FUSION_REQUEST, 'title' => $edit ? $this->locale['edit'] : $this->locale['add']]);
                    break;
                case 'categories':
                    add_breadcrumb(['link' => FUSION_REQUEST, 'title' => $this->locale['vid_001']]);
                    break;
                case 'submissions':
                    add_breadcrumb(['link' => FUSION_REQUEST, 'title' => $this->locale['vid_002']]);
                    break;
                case 'settings':
                    add_breadcrumb(['link' => FUSION_REQUEST, 'title' => $this->locale['vid_003']]);
                    break;
            }
        }

        opentable($this->locale['vid_title']);

        $allowed_section = ['list', 'form', 'categories', 'submissions', 'settings'];
        $_GET['section'] = isset($_GET['section']) && in_array($_GET['section'], $allowed_section) ? $_GET['section'] : 'list';

        if (isset($_GET['section']) && $_GET['section'] == 'form') {
            $tab['title'][] = $this->locale['back'];
            $tab['id'][]    = 'back';
            $tab['icon'][]  = 'fa fa-fw fa-arrow-left';
        }

        $tab['title'][] = $this->locale['vid_title'];
        $tab['id'][]    = 'list';
        $tab['icon'][]  = 'fa fa-fw fa-play';
        $tab['title'][] = $edit ? $this->locale['edit'] : $this->locale['add'];
        $tab['id'][]    = 'form';
        $tab['icon'][]  = 'fa fa-'.($edit ? 'pencil' : 'plus');
        $tab['title'][] = $this->locale['vid_001'];
        $tab['id'][]    = 'categories';
        $tab['icon'][]  = 'fa fa-folder';
        $tab['title'][] = $this->locale['vid_002'].'&nbsp;<span class="badge">'.dbcount("(submit_id)", DB_SUBMISSIONS, "submit_type='v'").'</span>';
        $tab['id'][]    = 'submissions';
        $tab['icon'][]  = 'fa fa-inbox';
        $tab['title'][] = $this->locale['vid_003'];
        $tab['id'][]    = 'settings';
        $tab['icon'][]  = 'fa fa-cogs';

        echo opentab($tab, $_GET['section'], 'videoadmin', TRUE, 'nav-tabs m-b-20');
        switch ($_GET['section']) {
            case 'form':
            if (dbcount("(video_cat_id)", DB_VIDEO_CATS)) {
                    require_once 'admin/videos.php';
                } else {
                    echo '<div class="well text-center">'.$this->locale['vid_004'].'</div>';
                }
                break;
            case 'categories':
                require_once 'admin/video_cats.php';
                break;
            case 'submissions':
                require_once 'admin/video_submissions.php';
                break;
            case 'settings':
                require_once 'admin/video_settings.php';
                break;
            default:
                $this->listing();
                break;
        }
        echo closetab();

        closetable();
    }
}

$vid = new VideosAdmin();
$vid->displayAdmin();

require_once THEMES.'templates/footer.php';
