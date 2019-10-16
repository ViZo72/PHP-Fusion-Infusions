<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: videos/video_submit.php
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
$video_settings = get_settings('videos');

add_to_title($locale['global_200'].$locale['vid_076']);

opentable('<i class="fa fa-play fa-lg fa-fw"></i>'.$locale['vid_076']);

if (iMEMBER && $video_settings['video_allow_submission']) {
    $criteria_array = [
        'video_cat'         => 0,
        'video_title'       => '',
        'video_description' => '',
        'video_keywords'    => '',
        'video_length'      => '',
        'video_type'        => '',
        'video_file'        => '',
        'video_url'         => '',
        'video_embed'       => '',
        'video_image'       => ''
    ];

    if (isset($_POST['submit_video'])) {
        $criteria_array = [
            'video_cat'         => form_sanitizer($_POST['video_cat'], 0, 'video_cat'),
            'video_title'       => form_sanitizer($_POST['video_title'], '', 'video_title'),
            'video_description' => form_sanitizer($_POST['video_description'], '', 'video_description'),
            'video_keywords'    => form_sanitizer($_POST['video_keywords'], '', 'video_keywords'),
            'video_length'      => form_sanitizer($_POST['video_length'], '', 'video_length'),
            'video_type'        => form_sanitizer($_POST['video_type'], '', 'video_type'),
            'video_file'        => '',
            'video_url'         => '',
            'video_embed'       => '',
            'video_image'       => ''
        ];

        if (\defender::safe() && !empty($_FILES['video_file']['name']) && is_uploaded_file($_FILES['video_file']['tmp_name'])) {
            $upload = form_sanitizer($_FILES['video_file'], '', 'video_file');

            if (empty($upload['error']) && !empty($_FILES['video_file']['size'])) {
                if (!empty($upload['image_name'])) {
                    $criteria_array['video_file'] = $upload['image_name'];
                } else if (!empty($upload['target_file'])) {
                    $criteria_array['video_file'] = $upload['target_file'];
                } else {
                    \defender::stop();
                    addNotice('warning', $locale['vid_078']);
                }
            }

            unset($upload);
        } else if (!empty($_POST['video_url']) && empty($data['video_file'])) {
            $criteria_array['video_url'] = form_sanitizer($_POST['video_url'], '', 'video_url');
        } else if (!empty($_POST['video_embed']) && empty($data['video_file'])) {
            $criteria_array['video_embed'] = form_sanitizer($_POST['video_embed'], '', 'video_embed');
        } else if (empty($data['video_file']) && empty($data['video_url']) && empty($data['video_embed'])) {
            \defender::stop();
            addNotice('danger', $locale['vid_notice_04']);
        }

        if (\defender::safe() && !empty($_FILES['video_image']['name']) && is_uploaded_file($_FILES['video_image']['tmp_name'])) {
            $upload = form_sanitizer($_FILES['video_image'], '', 'video_image');
            if (empty($upload['error'])) {
                $criteria_array['video_image'] = $upload['image_name'];
                unset($upload);
            }
        }

        if (defender::safe()) {
            $input_array = [
                'submit_type'      => 'v',
                'submit_user'      => fusion_get_userdata('user_id'),
                'submit_datestamp' => time(),
                'submit_criteria'  => serialize($criteria_array)
            ];

            dbquery_insert(DB_SUBMISSIONS, $input_array, 'save');
            addNotice('success', $locale['vid_079']);
            redirect(clean_request('submitted=v', ['stype'], TRUE));
        }
    }

    if (isset($_GET['submitted']) && $_GET['submitted'] == 'v') {
        echo '<div class="well text-center">';
            echo '<p><strong>'.$locale['vid_079'].'</strong></p>';
            echo '<p><a href="'.BASEDIR.'submit.php?stype=v">'.$locale['vid_080'].'</a></p>';
            echo '<p><a href="'.BASEDIR.'index.php">'.str_replace('[SITENAME]', fusion_get_settings('sitename'), $locale['vid_081']).'</a></p>';
        echo '</div>';
    } else {
        if (dbcount("(video_cat_id)", DB_VIDEO_CATS, multilang_table('VL') ? in_group('video_cat_language', LANGUAGE) : '')) {
            echo openform('submit_form', 'post', BASEDIR.'submit.php?stype=v', ['enctype' => TRUE]);

            echo '<div class="panel panel-default"><div class="panel-body">';
                echo '<div class="alert alert-info m-b-20 submission-guidelines">'.str_replace('[SITENAME]', fusion_get_settings('sitename'), $locale['vid_082']).'</div>';

                echo form_text('video_title', $locale['vid_010'], $criteria_array['video_title'], [
                    'required'   => TRUE,
                    'inline'     => TRUE,
                    'error_text' => $locale['vid_011']
                ]);

                echo form_select_tree('video_cat', $locale['vid_009'], $criteria_array['video_cat'], [
                    'inline'      => TRUE,
                    'no_root'     => TRUE,
                    'placeholder' => $locale['choose'],
                    'query'       => (multilang_table('VL') ? "WHERE ".in_group('video_cat_language', LANGUAGE) : '')
                ], DB_VIDEO_CATS, 'video_cat_name', 'video_cat_id', 'video_cat_parent');

                echo form_select('video_keywords', $locale['vid_012'], $criteria_array['video_keywords'], [
                    'placeholder' => $locale['vid_013'],
                    'max_length'  => 320,
                    'inline'      => TRUE,
                    'width'       => '100%',
                    'tags'        => 1,
                    'multiple'    => 1
                ]);

                echo form_text('video_length', $locale['vid_013a'], $criteria_array['video_length'], [
                    'required'    => TRUE,
                    'inline'      => TRUE,
                    'placeholder' => '00:00',
                    'error_text'  => $locale['vid_013b']
                ]);

                echo form_textarea('video_description', $locale['vid_014'], $criteria_array['video_description'], [
                    'no_resize' => TRUE,
                    'form_name' => 'submit_form',
                    'type'      => fusion_get_settings('tinymce_enabled') ? 'tinymce' : 'bbcode',
                    'tinymce'   => fusion_get_settings('tinymce_enabled') && iADMIN ? 'advanced' : 'simple',
                    'autosize'  => TRUE
                ]);

                echo '<div class="well">'.$locale['vid_017'].'</div>';

                echo form_select('video_type', $locale['vid_017a'], $criteria_array['video_type'], [
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
                $tab_video_type_active = tab_active($tab_video_type, 0);

                echo opentab($tab_video_type, $tab_video_type_active, 'videotab', FALSE, 'nav-tabs m-b-10');
                    echo opentabbody($tab_video_type['title'][0], $tab_video_type['id'][0], $tab_video_type_active);
                        echo form_fileinput('video_file', $locale['vid_021'], $criteria_array['video_file'], [
                            'class'       => 'm-t-10',
                            'required'    => TRUE,
                            'width'       => '100%',
                            'upload_path' => VIDEOS.'submissions/',
                            'max_byte'    => $video_settings['video_max_b'],
                            'valid_ext'   => $video_settings['video_types'],
                            'error_text'  => $locale['vid_022'],
                            'type'        => 'video',
                            'preview_off' => TRUE,
                            'ext_tip'     => sprintf($locale['vid_023'], parsebytesize($video_settings['video_max_b']), str_replace(',', ' ', $video_settings['video_types']))
                        ]);
                    echo closetabbody();

                    echo opentabbody($tab_video_type['title'][1], $tab_video_type['id'][1], $tab_video_type_active);
                        echo form_text('video_url', $locale['vid_019'], $criteria_array['video_url'], [
                            'required'    => TRUE,
                            'class'       => 'm-t-10',
                            'inline'      => TRUE,
                            'error_text'  => $locale['vid_024'],
                            'ext_tip'     => '<span id="type-youtube">YouTube: <span class="required">https://www.youtube.com/watch?v=2MpUj-Aua48</span><br/></span>'.
                                             '<span id="type-vimeo">Vimeo: <span class="required">https://vimeo.com/56282283</span><br/></span>'.
                                             '<span id="type-url">'.$locale['vid_019a'].': <span class="required">https://www.example.com/file.flv</span><br/></span>'.$locale['vid_019b']
                        ]);
                    echo closetabbody();

                    echo opentabbody($tab_video_type['title'][2], $tab_video_type['id'][2], $tab_video_type_active);
                        echo form_textarea('video_embed', $locale['vid_020'], $criteria_array['video_embed'], [
                            'required'   => TRUE,
                            'inline'     => TRUE,
                            'error_text' => $locale['vid_025'],
                            'maxlength'  => '255',
                            'autosize'   => fusion_get_settings('tinymce_enabled') ? FALSE : TRUE
                        ]);
                    echo closetabbody();
                echo closetab();

                echo form_fileinput('video_image', $locale['vid_015'], $criteria_array['video_image'], [
                    'upload_path'     => VIDEOS.'submissions/images/',
                    'max_width'       => $video_settings['video_screen_max_w'],
                    'max_height'      => $video_settings['video_screen_max_w'],
                    'max_byte'        => $video_settings['video_screen_max_b'],
                    'type'            => 'image',
                    'delete_original' => FALSE,
                    'valid_ext'       => implode('.', array_keys(img_mimeTypes())),
                    'width'           => '100%',
                    'template'        => 'modern',
                    'inline'          => TRUE,
                    'ext_tip'         => sprintf($locale['vid_016'], parsebytesize($video_settings['video_screen_max_b']), str_replace(',', ' ', '.jpg,.gif,.png'), $video_settings['video_screen_max_w'], $video_settings['video_screen_max_h']).'<br/>'.$locale['vid_015a']
                ]);

            echo '</div></div>';

            echo form_button('submit_video', $locale['vid_076'], $locale['vid_076'], ['class' => 'btn-success', 'icon' => 'fa fa-hdd-o']);

            echo closeform();
        } else {
            echo '<div class="well text-center">'.$locale['vid_045'].'</div>';
        }
    }
} else {
    echo '<div class="well text-center">'.$locale['vid_077'].'</div>';
}

closetable();
