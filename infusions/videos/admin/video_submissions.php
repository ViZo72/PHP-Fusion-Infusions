<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: videos/admin/video_submissions.php
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

if (isset($_GET['submit_id']) && isnum($_GET['submit_id'])) {
    if (isset($_POST['publish']) && (isset($_GET['submit_id']) && isnum($_GET['submit_id']))) {
        $result = dbquery("SELECT s.*, u.user_id, u.user_name
            FROM ".DB_SUBMISSIONS." s
            LEFT JOIN ".DB_USERS." u ON s.submit_user=u.user_id
            WHERE submit_id='".$_GET['submit_id']."'
        ");

        if (dbrows($result)) {
            $callback_data = dbarray($result);

            $callback_data = [
                'video_id'             => 0,
                'video_cat'            => form_sanitizer($_POST['video_cat'], 0, 'video_cat'),
                'video_user'           => $callback_data['submit_user'],
                'video_title'          => form_sanitizer($_POST['video_title'], '', 'video_title'),
                'video_description'    => form_sanitizer($_POST['video_description'], '', 'video_description'),
                'video_keywords'       => form_sanitizer($_POST['video_keywords'], '', 'video_keywords'),
                'video_length'         => form_sanitizer($_POST['video_length'], '', 'video_length'),
                'video_datestamp'      => $callback_data['submit_datestamp'],
                'video_visibility'     => form_sanitizer($_POST['video_visibility'], '', 'video_visibility'),
                'video_type'           => form_sanitizer($_POST['video_type'], '0', 'video_type'),
                'video_file'           => form_sanitizer($_POST['video_file'], '', 'video_file'),
                'video_url'            => form_sanitizer($_POST['video_url'], '', 'video_url'),
                'video_embed'          => form_sanitizer($_POST['video_embed'], '', 'video_embed'),
                'video_image'          => !empty($_POST['video_image']) ? form_sanitizer($_POST['video_image'], '', 'video_image') : '',
                'video_allow_comments' => isset($_POST['video_allow_comments']) ? 1 : 0,
                'video_allow_ratings'  => isset($_POST['video_allow_ratings']) ? 1 : 0
            ];

            if (\defender::safe()) {
                if (!empty($callback_data['video_file']) && file_exists(VIDEOS.'submissions/'.$callback_data['video_file'])) {
                    $dest = VIDEOS.'videos/';
                    $temp_file = $callback_data['video_file'];
                    $callback_data['video_file'] = filename_exists($dest, $callback_data['video_file']);
                    copy(VIDEOS.'submissions/'.$temp_file, $dest.$callback_data['video_file']);
                    chmod($dest.$callback_data['video_file'], 0644);
                    unlink(VIDEOS.'submissions/'.$temp_file);
                }

                if (!empty($callback_data['video_image']) && file_exists(VIDEOS.'submissions/images/'.$callback_data['video_image'])) {
                    $dest = VIDEOS.'images/';
                    $temp_file = $callback_data['video_image'];
                    $callback_data['video_image'] = filename_exists($dest, $callback_data['video_image']);
                    copy(VIDEOS.'submissions/images/'.$temp_file, $dest.$callback_data['video_image']);
                    chmod($dest.$callback_data['video_image'], 0644);
                    unlink(VIDEOS.'submissions/images/'.$temp_file);
                }

                dbquery_insert(DB_VIDEOS, $callback_data, 'save');
                dbquery("DELETE FROM ".DB_SUBMISSIONS." WHERE submit_id='".intval($_GET['submit_id'])."'");
                addNotice('success', $locale['vid_047']);
                redirect(clean_request('', ['submit_id'], FALSE));
            }
        } else  {
            redirect(clean_request('', ['submit_id'], FALSE));
        }
    } else {
        if (isset($_POST['delete']) && (isset($_GET['submit_id']) && isnum($_GET['submit_id']))) {
            $result = dbquery("SELECT s.submit_id, s.submit_datestamp, s.submit_criteria
                FROM ".DB_SUBMISSIONS." s
                WHERE submit_type='v' and submit_id='".intval($_GET['submit_id'])."'
            ");

            if (dbrows($result) > 0) {
                $callback_data = dbarray($result);
                $delete_criteria = unserialize($callback_data['submit_criteria']);

                if (!empty($delete_criteria['video_file']) && file_exists(VIDEOS.'submisisons/'.$delete_criteria['video_file'])) {
                    unlink(VIDEOS.'submisisons/'.$delete_criteria['video_file']);
                }

                if (!empty($delete_criteria['video_image']) && file_exists(VIDEOS.'submisisons/images/'.$delete_criteria['video_image'])) {
                    unlink(VIDEOS.'submisisons/images/'.$delete_criteria['video_image']);
                }

                dbquery("DELETE FROM ".DB_SUBMISSIONS." WHERE submit_id='".intval($callback_data['submit_id'])."'");
                addNotice('success', $locale['vid_048']);
            }
            redirect(clean_request('', ['submit_id'], FALSE));
        } else {
            $result = dbquery("SELECT s.submit_id, s.submit_datestamp, s.submit_criteria, u.user_id, u.user_name, u.user_avatar, u.user_status
                FROM ".DB_SUBMISSIONS." s
                LEFT JOIN ".DB_USERS." u ON s.submit_user=u.user_id
                WHERE submit_type='v' AND submit_id='".$_GET['submit_id']."'
            ");

            if (dbrows($result) > 0) {
                $data = dbarray($result);
                $submit_criteria = unserialize($data['submit_criteria']);

                $callback_data = [
                    'video_id'             => 0,
                    'video_cat'            => $submit_criteria['video_cat'],
                    'video_title'          => $submit_criteria['video_title'],
                    'video_description'    => parse_textarea($submit_criteria['video_description']),
                    'video_keywords'       => $submit_criteria['video_keywords'],
                    'video_length'         => $submit_criteria['video_length'],
                    'video_datestamp'      => $data['submit_datestamp'],
                    'video_visibility'     => iGUEST,
                    'video_type'           => $submit_criteria['video_type'],
                    'video_file'           => $submit_criteria['video_file'],
                    'video_url'            => $submit_criteria['video_url'],
                    'video_embed'          => $submit_criteria['video_embed'],
                    'video_image'          => $submit_criteria['video_image'],
                    'video_allow_comments' => 1,
                    'video_allow_ratings'  => 1
                ];

                add_to_title($locale['global_200'].$locale['global_201'].$callback_data['video_title'].'?');

                echo openform('publish_video', 'post', FUSION_REQUEST);
                echo '<div class="well clearfix">';
                    echo '<div class="pull-left">';
                        echo display_avatar($data, '30px', '', FALSE, 'img-rounded m-t-5 m-r-5');
                    echo '</div>';

                    echo '<div class="overflow-hide">';
                        echo $locale['vid_049'].profile_link($data['user_id'], $data['user_name'], $data['user_status']).'<br/>';
                        echo $locale['vid_050'].timer($data['submit_datestamp']).' - '.showdate('shortdate', $data['submit_datestamp']);
                    echo '</div>';
                echo '</div>';

                echo '<div class="row">';
                    echo '<div class="col-xs-12 col-sm-8">';
                        openside('');
                        echo form_hidden('submit_id', '', $data['submit_id']);
                        echo form_hidden('video_datestamp', '', $callback_data['video_datestamp']);

                        echo form_text('video_title', $locale['vid_010'], $callback_data['video_title'], [
                            'required'   => TRUE,
                            'inline'     => TRUE,
                            'error_text' => $locale['vid_011']
                        ]);
                        echo form_select('video_keywords',  $locale['vid_012'], $callback_data['video_keywords'], [
                            'placeholder' =>  $locale['vid_013'],
                            'max_length'  => 320,
                            'inline'      => TRUE,
                            'width'       => '100%',
                            'inner_width' => '100%',
                            'tags'        => 1,
                            'multiple'    => 1
                        ]);
                        echo form_text('video_length', $locale['vid_013a'], $callback_data['video_length'], [
                            'required'    => TRUE,
                            'inline'      => TRUE,
                            'placeholder' => '00:00',
                            'error_text'  => $locale['vid_013b']
                        ]);
                        echo form_textarea('video_description', $locale['vid_014'], $callback_data['video_description'], [
                            'no_resize' => TRUE,
                            'form_name' => 'inputform',
                            'type'      => fusion_get_settings('tinymce_enabled') ? 'tinymce' : 'html',
                            'tinymce'   => fusion_get_settings('tinymce_enabled') ? 'simple' : '',
                            'autosize'  => fusion_get_settings('tinymce_enabled') ? FALSE : TRUE,
                            'preview'   => fusion_get_settings('tinymce_enabled') ? FALSE : TRUE,
                            'height'    => '300px'
                        ]);
                        closeside();
                    echo '</div>';

                    echo '<div class="col-xs-12 col-sm-4">';
                        echo '<div class="well clearfix">';
                            if (!empty($callback_data['video_image'])) {
                                echo '<div class="pull-left m-r-10">';
                                    echo thumbnail(VIDEOS.'submissions/images/'.$callback_data['video_image'], '80px');
                                    echo form_hidden('video_image', '', $callback_data['video_image']);
                                echo '</div>';
                            }

                            echo '<div class="overflow-hide p-l-10">';
                                echo form_select('video_type', $locale['vid_017a'], $callback_data['video_type'], [
                                    'options'  => [
                                        'file'    => $locale['vid_018'],
                                        'url'     => $locale['vid_019'],
                                        'youtube' => 'YouTube',
                                        'embed'   => $locale['vid_020']
                                    ],
                                    'required' => TRUE,
                                    'inline'   => TRUE
                                ]);
                                if (!empty($callback_data['video_file'])) {
                                    echo '<strong>'.$locale['vid_021'].'</strong>';
                                    echo '<a class="btn btn-default" href="'.VIDEOS.'submissions/'.$callback_data['video_file'].'">../'.$callback_data['video_file'].'</a>';
                                    echo form_hidden('video_file', '', $callback_data['video_file']);
                                    echo form_hidden('video_url', '', '');
                                    echo form_hidden('video_embed', '', '');
                                } else if (!empty($callback_data['video_url'])) {
                                    echo '<strong>'.$locale['vid_019'].'</strong>';
                                    echo form_text('video_url', '', $callback_data['video_url']);
                                    echo form_hidden('video_file', '', '');
                                    echo form_hidden('video_embed', '', '');
                                } else {
                                    echo '<strong>'.$locale['vid_020'].'</strong>';
                                    echo form_textarea('video_embed', '', $callback_data['video_embed']);
                                    echo form_hidden('video_file', '', '');
                                    echo form_hidden('video_url', '', '');
                                }
                            echo '</div>';
                        echo '</div>';

                        openside();
                        if (fusion_get_settings('comments_enabled') == 0 || fusion_get_settings('ratings_enabled') == 0) {
                            if (fusion_get_settings('comments_enabled') == 0 && fusion_get_settings('ratings_enabled') == 0) {
                                $sys = $locale['comments_ratings'];
                            } else if (fusion_get_settings('comments_enabled') == 0) {
                                $sys = $locale['comments'];
                            } else {
                                $sys = $locale['ratings'];
                            }

                            echo '<div class="well">'.sprintf($locale['vid_026'], $sys).'</div>';
                        }

                        echo form_select_tree('video_cat', $locale['vid_009'], $callback_data['video_cat'], [
                            'no_root'     => 1,
                            'placeholder' => $locale['choose'],
                            'width'       => '100%',
                            'query'       => (multilang_table('VL') ? "WHERE ".in_group('video_cat_language', LANGUAGE) : '')
                        ], DB_VIDEO_CATS, 'video_cat_name', 'video_cat_id', 'video_cat_parent');

                        echo form_select('video_visibility', $locale['vid_027'], $callback_data['video_visibility'], [
                            'options'     => fusion_get_groups(),
                            'placeholder' => $locale['choose'],
                            'width'       => '100%'
                        ]);
                        echo form_button('publish', $locale['vid_051'], $locale['vid_051'], ['class' => 'btn-success btn-sm', 'icon' => 'fa fa-hdd-o']);
                        closeside();

                        openside('');
                            echo form_checkbox('video_allow_comments', $locale['vid_028'], $callback_data['video_allow_comments'], ['class' => 'm-b-0', 'reverse_label' => TRUE]);
                            echo form_checkbox('video_allow_ratings', $locale['vid_029'], $callback_data['video_allow_ratings'], ['class' => 'm-b-0', 'reverse_label' => TRUE]);

                            if (isset($_GET['action']) && $_GET['action'] == 'edit') {
                                echo form_checkbox('update_datestamp', $locale['vid_030'], '', ['class' => 'm-b-0', 'reverse_label' => TRUE]);
                            }
                        closeside();
                    echo '</div>';

                echo '</div>';

                echo form_button('publish', $locale['vid_051'], $locale['vid_051'], ['class' => 'btn-success m-r-10', 'icon' => 'fa fa-hdd-o', 'input_id' => 'publishvideo']);
                echo form_button('delete', $locale['vid_052'], $locale['vid_052'], ['class' => 'btn-danger', 'icon' => 'fa fa-trash']);
                echo closeform();
            }
        }
    }
} else {
    $result = dbquery("SELECT s.submit_id, s.submit_datestamp, s.submit_criteria, u.user_id, u.user_name, u.user_avatar, u.user_status
        FROM ".DB_SUBMISSIONS." s
        LEFT JOIN ".DB_USERS." u ON s.submit_user=u.user_id
        WHERE submit_type='v'
        ORDER BY submit_datestamp DESC
    ");

    $rows = dbrows($result);

    if ($rows > 0) {
        echo '<div class="well">'.sprintf($locale['vid_053'], format_word($rows, $locale['fmt_submission'])).'</div>';

        echo '<div class="table-responsive"><table class="table table-striped">';
            echo '<thead><tr>';
                echo '<th>'.$locale['vid_054'].'</th>';
                echo '<th>'.$locale['vid_055'].'</th>';
                echo '<th>'.$locale['vid_056'].'</th>';
                echo '<th>'.$locale['vid_057'].'</th>';
            echo '</tr></thead>';
            echo '<tbody>';
                while ($callback_data = dbarray($result)) {
                    $submit_criteria = unserialize($callback_data['submit_criteria']);
                    echo '<tr>';
                        echo '<td>'.$callback_data['submit_id'].'</td>';
                        echo '<td>'.display_avatar($callback_data, '20px', '', TRUE, 'img-rounded m-r-5').profile_link($callback_data['user_id'], $callback_data['user_name'], $callback_data['user_status']).'</td>';
                        echo '<td>'.timer($callback_data['submit_datestamp']).'</td>';
                        echo '<td><a href="'.clean_request('submit_id='.$callback_data['submit_id'], ['section', 'aid'], TRUE).'">'.$submit_criteria['video_title'].'</a></td>';
                    echo '</tr>';
                }
            echo '</tbody>';
        echo '</table></div>';
    } else {
        echo '<div class="well text-center m-t-20">'.$locale['vid_058'].'</div>';
    }
}
