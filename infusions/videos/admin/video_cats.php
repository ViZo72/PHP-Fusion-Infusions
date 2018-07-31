<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: videos/admin/video_cats.php
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

$locale = fusion_get_locale();

$data = [
    'video_cat_id'          => 0,
    'video_cat_parent'      => 0,
    'video_cat_name'        => '',
    'video_cat_description' => '',
    'video_cat_sort_by'     => '',
    'video_cat_sort_order'  => 'ASC',
    'video_cat_language'    => LANGUAGE,
    'video_cat_hidden'      => []
];

if ((isset($_GET['action']) && $_GET['action'] == 'delete') && (isset($_GET['cat_id']) && isnum($_GET['cat_id']))) {
    if (dbcount("(video_cat)", DB_VIDEOS, "video_cat='".intval($_GET['cat_id'])."'")
        || dbcount("(video_cat_id)", DB_VIDEO_CATS, "video_cat_parent='".intval($_GET['cat_id'])."'")
    ) {
        addNotice('danger', $locale['VID_032']);
        redirect(clean_request('cat_view=1', ['section', 'aid'], TRUE));
    } else {

        dbquery("DELETE FROM ".DB_VIDEO_CATS." WHERE video_cat_id='".intval($_GET['cat_id'])."'");
        addNotice('success', $locale['VID_notice_05']);
        redirect(clean_request('cat_view=1', ['section', 'aid'], TRUE));
    }
}

if (isset($_POST['save_cat'])) {
    $data = [
        'video_cat_id'          => form_sanitizer($_POST['video_cat_id'], '', 'video_cat_id'),
        'video_cat_parent'      => form_sanitizer($_POST['video_cat_parent'], '', 'video_cat_parent'),
        'video_cat_name'        => form_sanitizer($_POST['video_cat_name'], '', 'video_cat_name'),
        'video_cat_description' => form_sanitizer($_POST['video_cat_description'], '', 'video_cat_description'),
        'video_cat_sort_by'     => form_sanitizer($_POST['video_cat_sort_by'], '', 'video_cat_sort_by'),
        'video_cat_sort_order'  => form_sanitizer($_POST['video_cat_sort_order'], 'DESC', 'video_cat_sort_order'),
        'video_cat_language'    => form_sanitizer($_POST['video_cat_language'], '', 'video_cat_language'),
        'video_cat_hidden'      => []
    ];

    switch ($data['video_cat_sort_by']) {
        case 1:
            $data['video_cat_sorting'] = 'video_id '.($data['video_cat_sort_order'] == 'ASC' ? 'ASC' : 'DESC');
            break;
        case 2:
            $data['video_cat_sorting'] = 'video_title '.($_POST['video_cat_sort_order'] == 'ASC' ? 'ASC' : 'DESC');
            break;
        case 3:
            $data['video_cat_sorting'] = 'video_datestamp '.($_POST['video_cat_sort_order'] == 'ASC' ? 'ASC' : 'DESC');
            break;
        default:
            $data['video_cat_sorting'] = 'video_title ASC';
    }

    $category_name_check = [
        'when_updating' => "video_cat_name='".$data['video_cat_name']."' AND video_cat_id !='".$data['video_cat_id']."'",
        'when_saving'   => "video_cat_name='".$data['video_cat_name']."'",
    ];

    if (dbcount("(video_cat_id)", DB_VIDEO_CATS, "video_cat_id='".$data['video_cat_id']."'")) {
        if (!dbcount("(video_cat_id)", DB_VIDEO_CATS, $category_name_check['when_updating'])) {
            if (\defender::safe()) {
                dbquery_insert(DB_VIDEO_CATS, $data, 'update');
                addNotice('success', $locale['VID_notice_06']);
                redirect(clean_request('cat_view=1', ['section', 'aid'], TRUE));
            }
        } else {
            \defender::stop();
            addNotice('danger', $locale['VID_notice_07']);
        }
    } else {
        if (!dbcount("(video_cat_id)", DB_VIDEO_CATS, $category_name_check['when_saving'])) {
            if (\defender::safe()) {
                dbquery_insert(DB_VIDEO_CATS, $data, 'save');
                addNotice('success', $locale['VID_notice_08']);
                redirect(clean_request('cat_view=1', ['section', 'aid'], TRUE));
            }
        } else {
            \defender::stop();
            addNotice('danger', $locale['VID_notice_07']);
        }
    }
}

if ((isset($_GET['action']) && $_GET['action'] == 'edit') && (isset($_GET['cat_id']) && isnum($_GET['cat_id']))) {
    $result = dbquery("SELECT * FROM ".DB_VIDEO_CATS." ".(multilang_table('VL') ? "WHERE video_cat_language='".LANGUAGE."' AND" : "WHERE")." video_cat_id='".$_GET['cat_id']."'");

    if (dbrows($result)) {
        $data = dbarray($result);
        $data['video_cat_hidden'] = [$data['video_cat_id']];
        $cat_sorting = explode(' ', $data['video_cat_sorting']);
        $data['video_cat_sort_by'] = '';

        if ($cat_sorting[0] == 'video_id') {
            $data['video_cat_sort_by'] = 1;
        } else if ($cat_sorting[0] == 'video_title') {
            $data['video_cat_sort_by'] = 2;
        } else if ($cat_sorting[0] == 'video_datestamp') {
            $data['video_cat_sort_by'] = 3;
        }

        $data['video_cat_sort_order'] = $cat_sorting[1];
    } else {
        redirect(clean_request('', ['section', 'aid'], TRUE));
    }
}

$tab_cats['title'][] = $locale['VID_043'];
$tab_cats['id'][]    = 'form';
$tab_cats['icon'][]  = '';
$tab_cats['title'][] = $locale['VID_044'];
$tab_cats['id'][]    = 'cats';
$tab_cats['icon'][]  = '';
$tab_cats_active = tab_active($tab_cats, isset($_GET['cat_view']) ? 1 : 0);

echo opentab($tab_cats, $tab_cats_active, 'categories', FALSE, 'nav-tabs m-b-10');
    echo opentabbody($tab_cats['title'][0], $tab_cats['id'][0], $tab_cats_active);
    echo openform('addcat', 'post', FUSION_REQUEST, ['enctype' => 1, 'class' => 'm-t-20']);
    echo '<div class="row">';
        echo '<div class="col-xs-12 col-sm-8">';
            openside('');
            echo form_hidden('video_cat_id', '', $data['video_cat_id']);
            echo form_text('video_cat_name', $locale['VID_033'], $data['video_cat_name'], [
                'required'   => TRUE,
                'error_text' => $locale['VID_034']
            ]);
            echo form_textarea('video_cat_description', $locale['VID_035'], $data['video_cat_description'], [
                'resize'   => 0,
                'autosize' => TRUE,
            ]);

            echo '<div class="row">';
                echo '<div class="col-xs-12 col-sm-7">';
                    echo '<label class="control-label">'.$locale['VID_037'].'</label>';
                    echo form_select('video_cat_sort_by', '', $data['video_cat_sort_by'], [
                        'options'     => [
                            '1' => $locale['VID_038'],
                            '2' => $locale['VID_010'],
                            '3' => $locale['VID_039']
                        ],
                        'class'       => 'pull-left',
                        'inner_width' => '200px',
                        'inline'      => TRUE
                    ]);
                echo '</div>';

                echo '<div class="col-xs-12 col-sm-5">';
                    echo '<label class="control-label"><!-- --></label>';
                    echo form_select('video_cat_sort_order', '', $data['video_cat_sort_order'], [
                        'options'     => ['ASC' => $locale['VID_040'], 'DESC' => $locale['VID_041']],
                        'inner_width' => '200px',
                        'inline'      => TRUE
                    ]);
                echo '</div>';
            echo '</div>';

            closeside();
        echo '</div>';

        echo '<div class="col-xs-12 col-sm-4">';
            openside('');
            echo form_select_tree('video_cat_parent', $locale['VID_042'], $data['video_cat_parent'], [
                'disable_opts'  => $data['video_cat_hidden'],
                'hide_disabled' => TRUE,
                'width'         => '100%'
            ], DB_VIDEO_CATS, 'video_cat_name', 'video_cat_id', 'video_cat_parent');

            if (multilang_table('VL')) {
                echo form_select('video_cat_language', $locale['global_ML100'], $data['video_cat_language'], [
                    'options'     => fusion_get_enabled_languages(),
                    'placeholder' => $locale['choose'],
                    'width'       => '100%'
                ]);
            } else {
                echo form_hidden('video_cat_language', '', $data['video_cat_language']);
            }
            closeside();
        echo '</div>';
    echo '</div>';

    echo form_button('save_cat', $locale['save'], $locale['save'], ['class' => 'btn-success', 'icon' => 'fa fa-hdd-o']);
    echo closeform();
    echo closetabbody();

    echo opentabbody($tab_cats['title'][1], $tab_cats['id'][1], $tab_cats_active);

    $result = dbquery("SELECT vc_.video_cat_id, vc_.video_cat_name, vc_.video_cat_description, count(v.video_id) 'video_count', vc.video_cat_id 'child_categories'
        FROM ".DB_VIDEO_CATS." vc_
        LEFT JOIN ".DB_VIDEO_CATS." vc ON vc.video_cat_parent=vc_.video_cat_id
        LEFT JOIN ".DB_VIDEOS." v ON v.video_cat=vc_.video_cat_id
        ".(multilang_table('VL') ? "WHERE vc_.video_cat_language='".LANGUAGE."'" : '')."
        GROUP by vc_.video_cat_id
        ORDER BY vc_.video_cat_name
    ");

    if (dbrows($result) != 0) {
        echo '<div class="row m-t-15">';
            while ($data = dbarray($result)) {
                echo '<div class="col-xs-12 col-sm-3">';
                    echo '<div class="well clearfix">';
                        echo '<div class="overflow-hide p-r-10">';
                            echo '<span class="display-inline-block m-r-10 strong text-bigger">'.get_video_cat_path($data['video_cat_id']).'</span>';
                            if ($data['video_cat_description']) {
                                echo '<br /><small>'.fusion_first_words($data['video_cat_description'], 50).'</small>';
                            }
                        echo '</div>';

                        echo '<div class="btn-group">';
                            echo '<a class="btn btn-sm btn-default" href="'.clean_request("action=edit&cat_id=".$data['video_cat_id'], ['section', 'aid'], TRUE).'" title="'.$locale['edit'].'"><i class="fa fa-pencil"></i></a>';
                            echo '<a class="btn btn-sm btn-danger '.($data['video_count'] || $data['child_categories'] ? 'disabled' : '').'" href="'.clean_request("action=delete&cat_id=".$data['video_cat_id'], ['section', 'aid'], TRUE).'" title="'.$locale['delete'].'"><i class="fa fa-trash"></i></a>';
                        echo '</div>';
                    echo '</div>';
                echo '</div>';
            }
        echo '</div>';
    } else {
        echo '<div class="well text-center">'.$locale['VID_045'].'</div>';
    }
    echo closetabbody();
echo closetab();

function get_video_cat_path($item_id) {
    $full_path = '';

    while ($item_id > 0) {
        $result = dbquery("SELECT video_cat_id, video_cat_name, video_cat_parent FROM ".DB_VIDEO_CATS." WHERE video_cat_id='".$item_id."'".(multilang_table('VL') ? " AND video_cat_language='".LANGUAGE."'" : ''));

        if (dbrows($result)) {
            $data = dbarray($result);
            if ($full_path) {
                $full_path = ' / '.$full_path;
            }

            $full_path = $data['video_cat_name'].$full_path;
            $item_id = $data['video_cat_parent'];
        }
    }

    return $full_path;
}
