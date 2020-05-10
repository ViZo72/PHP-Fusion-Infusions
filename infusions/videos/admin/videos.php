<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: videos/admin/videos.php
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
defined('IN_FUSION') || exit;

$locale = fusion_get_locale();
$aidlink = fusion_get_aidlink();

$data = [
    'video_id'             => 0,
    'video_cat'            => 0,
    'video_user'           => fusion_get_userdata('user_id'),
    'video_title'          => '',
    'video_description'    => '',
    'video_keywords'       => '',
    'video_length'         => '',
    'video_datestamp'      => '',
    'video_visibility'     => 0,
    'video_type'           => '',
    'video_file'           => '',
    'video_url'            => '',
    'video_embed'          => '',
    'video_image'          => '',
    'video_allow_comments' => 0,
    'video_allow_ratings'  => 0,
    'video_allow_likes'    => 0
];

if ((isset($_GET['action']) && $_GET['action'] == 'delete') && (isset($_GET['video_id']) && isnum($_GET['video_id']))) {
    $result = dbquery("SELECT * FROM ".DB_VIDEOS." WHERE video_id='".$_GET['video_id']."'");

    if (dbrows($result)) {
        $data = dbarray($result);

        if (!empty($data['video_file']) && file_exists(VIDEOS.'videos/'.$data['video_file'])) {
            @unlink(VIDEOS.'videos/'.$data['video_file']);
        }

        if (!empty($data['video_image']) && file_exists(VIDEOS.'images/'.$data['video_image'])) {
            @unlink(VIDEOS.'images/'.$data['video_image']);
        }

        dbquery("DELETE FROM ".DB_VIDEOS." WHERE video_id='".$_GET['video_id']."'");
        dbquery("DELETE FROM ".DB_VIDEO_LIKES." WHERE video_id='".$_GET['video_id']."'");
    }

    addNotice('success', $locale['vid_notice_03']);
    redirect(FUSION_SELF.$aidlink);
}

if (isset($_POST['delete_video']) && isnum($_POST['delete_video'])) {
    $result = dbquery("SELECT * FROM ".DB_VIDEOS." WHERE video_id=".intval($_POST['delete_video'])."");

    if (dbrows($result) > 0) {
        $data = dbarray($result);
        if (!empty($data['video_file']) && file_exists(VIDEOS.'videos/'.$data['video_file'])) {
            @unlink(VIDEOS.'videos/'.$data['video_file']);
        }

        $data['video_file'] = '';

        dbquery_insert(DB_VIDEOS, $data, 'update');
        redirect(FUSION_REQUEST);
    }
}

if (isset($_POST['save_video'])) {
    $data = [
        'video_id'             => form_sanitizer($_POST['video_id'], '0', 'video_id'),
        'video_cat'            => form_sanitizer($_POST['video_cat'], '0', 'video_cat'),
        'video_user'           => form_sanitizer($_POST['video_user'], '', "video_user"),
        'video_title'          => form_sanitizer($_POST['video_title'], '', 'video_title'),
        'video_description'    => form_sanitizer(descript($_POST['video_description']), '', 'video_description'),
        'video_keywords'       => form_sanitizer($_POST['video_keywords'], '', 'video_keywords'),
        'video_length'         => form_sanitizer($_POST['video_length'], '', 'video_length'),
        'video_datestamp'      => isset($_POST['update_datestamp']) || empty($_POST['video_datestamp']) ? TIME : $_POST['video_datestamp'],
        'video_visibility'     => form_sanitizer($_POST['video_visibility'], '0', 'video_visibility'),
        'video_type'           => form_sanitizer($_POST['video_type'], '0', 'video_type'),
        'video_file'           => isset($_POST['video_file']) ? form_sanitizer($_POST['video_file'], '', 'video_file') : '',
        'video_url'            => '',
        'video_embed'          => '',
        'video_image'          => isset($_POST['video_image']) ? form_sanitizer($_POST['video_image'], '', 'video_image') : '',
        'video_allow_comments' => isset($_POST['video_allow_comments']) ? 1 : 0,
        'video_allow_ratings'  => isset($_POST['video_allow_ratings']) ? 1 : 0,
        'video_allow_likes'    => isset($_POST['video_allow_likes']) ? 1 : 0
    ];

    if (\defender::safe() && !empty($_FILES['video_file']['name']) && is_uploaded_file($_FILES['video_file']['tmp_name'])) {
        $upload = form_sanitizer($_FILES['video_file'], '', 'video_file');

        if (empty($upload['error'])) {
            $data['video_file'] = !empty($upload['target_file']) ? $upload['target_file'] : $upload['name'];
        }
    } else if (!empty($_POST['video_url']) && empty($data['video_file'])) {
        $data['video_url'] = form_sanitizer($_POST['video_url'], '', 'video_url');
        $data['video_file'] = '';
        $data['video_embed'] = '';
    } else if (!empty($_POST['video_embed']) && empty($data['video_file'])) {
        $data['video_embed'] = form_sanitizer($_POST['video_embed'], '', 'video_embed');
        $data['video_file'] = '';
        $data['video_url'] = '';
    } else if (empty($data['video_file']) && empty($data['video_url']) && empty($data['video_embed'])) {
        \defender::stop();
        addNotice('danger', $locale['vid_notice_04']);
    }

    if (\defender::safe() && isset($_POST['delete_image']) && isset($_GET['video_id']) && isnum($_GET['video_id'])) {
        $result = dbquery("SELECT video_image FROM ".DB_VIDEOS." WHERE video_id='".$_GET['video_id']."'");
        if (dbrows($result)) {
            $data += dbarray($result);
            if (!empty($data['video_image']) && file_exists(VIDEOS.'images/'.$data['video_image'])) {
                @unlink(VIDEOS.'images/'.$data['video_image']);
            }
        }

        $data['video_image'] = '';
    } else if (\defender::safe() && !empty($_FILES['video_image']['name']) && is_uploaded_file($_FILES['video_image']['tmp_name'])) {
        $upload = form_sanitizer($_FILES['video_image'], '', 'video_image');
        if (empty($upload['error'])) {
            $data['video_image'] = $upload['image_name'];
        }
    }

    if (dbcount("(video_id)", DB_VIDEOS, "video_id='".$data['video_id']."'")) {
        dbquery_insert(DB_VIDEOS, $data, 'update');
        if (\defender::safe()) {
            addNotice('success', $locale['vid_notice_01']);
            redirect(FUSION_SELF.$aidlink);
        }
    } else {
        dbquery_insert(DB_VIDEOS, $data, 'save');
        if (\defender::safe()) {
            addNotice('success', $locale['vid_notice_02']);
            redirect(FUSION_SELF.$aidlink);
        }
    }
}

if ((isset($_GET['action']) && $_GET['action'] == 'edit') && (isset($_GET['video_id']) && isnum($_GET['video_id']))) {
    $result = dbquery("SELECT * FROM ".DB_VIDEOS." WHERE video_id='".intval($_GET['video_id'])."'");
    if (dbrows($result)) {
        $data = dbarray($result);
    } else {
        redirect(FUSION_SELF.$aidlink);
    }
}

echo openform('inputform', 'post', FUSION_REQUEST, ['enctype' => TRUE]);

echo '<div class="row">';
    echo '<div class="col-xs-12 col-sm-8">';
        openside('');
        echo form_hidden('video_id', '', $data['video_id']);
        echo form_hidden('video_user', '', $data['video_user']);
        echo form_hidden('video_datestamp', '', $data['video_datestamp']);
        echo form_text('video_title', $locale['vid_010'], $data['video_title'], [
            'required'   => TRUE,
            'inline'     => TRUE,
            'error_text' => $locale['vid_011']
        ]);
        echo form_select('video_keywords',  $locale['vid_012'], $data['video_keywords'], [
            'placeholder' =>  $locale['vid_013'],
            'max_length'  => 320,
            'inline'      => TRUE,
            'width'       => '100%',
            'inner_width' => '100%',
            'tags'        => 1,
            'multiple'    => 1
        ]);
        echo form_text('video_length', $locale['vid_013a'], $data['video_length'], [
            'required'    => TRUE,
            'inline'      => TRUE,
            'placeholder' => '00:00',
            'error_text'  => $locale['vid_013b']
        ]);
        echo form_textarea('video_description', $locale['vid_014'], $data['video_description'], [
            'no_resize' => TRUE,
            'form_name' => 'inputform',
            'type'      => 'bbcode'
        ]);

        closeside();

        echo '<div class="well">'.$locale['vid_017'].'</div>';

        echo form_select('video_type', $locale['vid_017a'], $data['video_type'], [
            'options'  => [
                'file'    => $locale['vid_018'],
                'url'     => $locale['vid_019'],
                'youtube' => 'YouTube',
                'vimeo'   => 'Vimeo',
                'embed'   => $locale['vid_020']
            ],
            'required' => TRUE,
            'inline'   => TRUE
        ]);

        add_to_jquery('
            $("#type-youtube").hide();
            $("#type-vimeo").hide();

            $("#video_type").on("change", function (e) {
                if ($(this).val() == "file") {
                    $("#videotab li #tab-videofile").tab("show");
                } else if ($(this).val() == "url" || $(this).val() == "youtube" || $(this).val() == "vimeo") {
                    $("#videotab li #tab-videourl").tab("show");
                } else if ($(this).val() == "embed") {
                    $("#videotab li #tab-videoembed").tab("show");
                }

                if ($(this).val() == "youtube") {
                    $("#type-youtube").show();
                    $("#type-vimeo").hide();
                    $("#type-url").hide();
                }

                if ($(this).val() == "vimeo") {
                    $("#type-youtube").hide();
                    $("#type-vimeo").show();
                    $("#type-url").hide();
                }

                if ($(this).val() == "url") {
                    $("#type-youtube").hide();
                    $("#type-vimeo").hide();
                    $("#type-url").show();
                }
            });
        ');

        $tab_video_type['title'][] = $locale['vid_018'];
        $tab_video_type['id'][]    = 'videofile';
        $tab_video_type['icon'][]  = 'fa fa-file-zip-o fa-fw';
        $tab_video_type['title'][] = $locale['vid_019'];
        $tab_video_type['id'][]    = 'videourl';
        $tab_video_type['icon'][]  = 'fa fa-link fa-fw';
        $tab_video_type['title'][] = $locale['vid_020'];
        $tab_video_type['id'][]    = 'videoembed';
        $tab_video_type['icon'][]  = 'fa fa-code fa-fw';

        if (!empty($data['video_type'])) {
            if ($data['video_type'] === 'file') {
                $tab_video_type_active = 'videofile';
            } else if ($data['video_type'] === 'url' || $data['video_type'] === 'youtube' || $data['video_type'] === 'vimeo') {
                $tab_video_type_active = 'videourl';
            } else if ($data['video_type'] === 'embed') {
                $tab_video_type_active = 'videoembed';
            }
        } else {
            $tab_video_type_active = tab_active($tab_video_type, 0);
        }

        echo opentab($tab_video_type, $tab_video_type_active, 'videotab', FALSE, 'nav-tabs m-b-10');
            echo opentabbody($tab_video_type['title'][0], $tab_video_type['id'][0], $tab_video_type_active);
                if (!empty($data['video_file'])) {
                    echo '<div class="m-t-20 m-b-20">';
                        echo $locale['vid_021'].' - <a href="'.VIDEOS.'videos/'.$data['video_file'].'">'.VIDEOS.'videos/'.$data['video_file'].'</a>';
                        echo form_button('delete_video', $locale['delete'], $data['video_id'], ['class' => 'm-b-0 pull-right btn-danger', 'icon' => 'fa fa-trash fa-fw']);
                        echo form_hidden('video_file', '', $data['video_file']);
                    echo '</div>';
                } else {
                    echo form_fileinput('video_file', $locale['vid_021'], '', [
                        'class'       => 'm-t-10',
                        // 'required'    => TRUE,
                        'width'       => '100%',
                        'upload_path' => VIDEOS.'videos/',
                        'max_byte'    => $this->video_settings['video_max_b'],
                        'valid_ext'   => $this->video_settings['video_types'],
                        'error_text'  => $locale['vid_022'],
                        'type'        => 'video',
                        'preview_off' => TRUE,
                        'ext_tip'     => sprintf($locale['vid_023'], parsebytesize($this->video_settings['video_max_b']), str_replace(',', ' ', $this->video_settings['video_types']))
                    ]);
                }
            echo closetabbody();

            echo opentabbody($tab_video_type['title'][1], $tab_video_type['id'][1], $tab_video_type_active);
                if (empty($data['video_file'])) {
                    echo form_text('video_url', $locale['vid_019'], $data['video_url'], [
                        // 'required'    => TRUE,
                        'class'       => 'm-t-10',
                        'inline'      => TRUE,
                        'type'        => 'url',
                        'error_text'  => $locale['vid_024'],
                        'ext_tip'     => '<span id="type-youtube">YouTube: <span class="required">https://www.youtube.com/watch?v=2MpUj-Aua48</span><br/></span>'.
                                         '<span id="type-vimeo">Vimeo: <span class="required">https://vimeo.com/56282283</span><br/></span>'.
                                         '<span id="type-url">'.$locale['vid_019a'].': <span class="required">https://www.example.com/file.flv</span><br/></span>'.$locale['vid_019b']
                    ]);
                } else {
                    echo form_hidden('video_url', '', $data['video_url']);
                    echo '<div class="m-10">'.$locale['vid_046'].'</div>';
                }
            echo closetabbody();

            echo opentabbody($tab_video_type['title'][2], $tab_video_type['id'][2], $tab_video_type_active);
                if (empty($data['video_file'])) {
                    echo form_textarea('video_embed', $locale['vid_020'], $data['video_embed'], [
                        // 'required'   => TRUE,
                        'inline'     => TRUE,
                        'error_text' => $locale['vid_025'],
                        'maxlength'  => '255',
                        'autosize'   => fusion_get_settings('tinymce_enabled') ? FALSE : TRUE
                    ]);
                } else {
                    echo form_hidden('video_embed', '', $data['video_embed']);
                    echo '<div class="m-10">'.$locale['vid_046'].'</div>';
                }
            echo closetabbody();
        echo closetab();

        if (!empty($data['video_image'])) {
            echo '<div class="clearfix list-group-item m-b-20">';
                echo '<div class="pull-left m-r-10">';
                    echo thumbnail(VIDEOS.'images/'.$data['video_image'], '80px');
                echo '</div>';
                echo '<div class="overflow-hide">';
                    echo '<span class="text-dark strong">'.$locale['vid_015'].'</span>';
                    echo form_checkbox('delete_image', $locale['delete'], '');
                    echo form_hidden('video_image', '', $data['video_image']);
                echo '</div>';
            echo '</div>';
        } else {
            require_once INCLUDES.'mimetypes_include.php';
            echo form_fileinput('video_image', $locale['vid_015'], '', [
                'upload_path'     => VIDEOS.'images/',
                'max_width'       => $this->video_settings['video_screen_max_w'],
                'max_height'      => $this->video_settings['video_screen_max_w'],
                'max_byte'        => $this->video_settings['video_screen_max_b'],
                'type'            => 'image',
                'delete_original' => FALSE,
                'width'           => '100%',
                'inline'          => TRUE,
                'template'        => 'thumbnail',
                'ext_tip'         => sprintf($locale['vid_016'], parsebytesize($this->video_settings['video_screen_max_b']), str_replace(',', ' ', '.jpg,.gif,.png'), $this->video_settings['video_screen_max_w'], $this->video_settings['video_screen_max_h']).'<br/>'.$locale['vid_015a']
            ]);
        }

    echo '</div>';

    echo '<div class="col-xs-12 col-sm-4">';
        openside();
        if (fusion_get_settings('comments_enabled') == '0' || fusion_get_settings('ratings_enabled') == '0') {
            if (fusion_get_settings('comments_enabled') == '0' && fusion_get_settings('ratings_enabled') == '0') {
                $sys = $locale['comments_ratings'];
            } else if (fusion_get_settings('comments_enabled') == '0') {
                $sys = $locale['comments'];
            } else {
                $sys = $locale['ratings'];
            }

            echo '<div class="well">'.sprintf($locale['vid_026'], $sys).'</div>';
        }

        echo form_select_tree('video_cat', $locale['vid_009'], $data['video_cat'], [
            'no_root'     => 1,
            'placeholder' => $locale['choose'],
            'width'       => '100%',
            'query'       => (multilang_table('VL') ? "WHERE ".in_group('video_cat_language', LANGUAGE) : '')
        ], DB_VIDEO_CATS, 'video_cat_name', 'video_cat_id', 'video_cat_parent');
        echo form_select('video_visibility', $locale['vid_027'], $data['video_visibility'], [
            'options'     => fusion_get_groups(),
            'placeholder' => $locale['choose'],
            'width'       => '100%'
        ]);
        echo form_button('save_video', $locale['save'], $locale['save'], ['class' => 'btn-success m-r-10', 'icon' => 'fa fa-hdd-o']);
        closeside();

        openside('');
            echo form_checkbox('video_allow_comments', $locale['vid_028'], $data['video_allow_comments'], ['class' => 'm-b-0', 'reverse_label' => TRUE]);
            echo form_checkbox('video_allow_ratings', $locale['vid_029'], $data['video_allow_ratings'], ['class' => 'm-b-0', 'reverse_label' => TRUE]);
            echo form_checkbox('video_allow_likes', $locale['vid_083'], $data['video_allow_likes'], ['class' => 'm-b-0', 'reverse_label' => TRUE]);

            if (isset($_GET['action']) && $_GET['action'] == 'edit') {
                echo form_checkbox('update_datestamp', $locale['vid_030'], 0, ['class' => 'm-b-0', 'reverse_label' => TRUE]);
            }
        closeside();
    echo '</div>';
echo '</div>';

echo form_button('save_video', $locale['vid_031'], $locale['vid_031'], [
    'class'    => 'btn-success m-r-10',
    'icon'     => 'fa fa-hdd-o',
    'input_id' => 'savevideo'
]);

if (isset($_GET['action']) && $_GET['action'] == 'edit') {
    echo '<button type="reset" name="reset" value="'.$locale['cancel'].'" class="button btn btn-default">'.$locale['cancel'].'</button>';
}

echo closeform();
