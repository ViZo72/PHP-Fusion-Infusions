<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: videos/admin/video_settings.php
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

if (isset($_POST['savesettings'])) {
    $settings = [
        'video_max_b'            => form_sanitizer($_POST['calc_b'], 52428800, 'calc_b') * form_sanitizer($_POST['calc_c'], 1, 'calc_c'),
        'video_types'            => form_sanitizer($_POST['video_types'], '.flv,.mp4,.mov,.f4v,.3gp,.3g2,.mp3,.flac', 'video_types'),
        'video_screen_max_b'     => form_sanitizer($_POST['calc_bb'], 153600, 'calc_bb') * form_sanitizer($_POST['calc_cc'], 1, 'calc_cc'),
        'video_screen_max_w'     => form_sanitizer($_POST['video_screen_max_w'], 1024, 'video_screen_max_w'),
        'video_screen_max_h'     => form_sanitizer($_POST['video_screen_max_h'], 768, 'video_screen_max_h'),
        'video_pagination'       => form_sanitizer($_POST['video_pagination'], 15, 'video_pagination'),
        'video_allow_submission' => form_sanitizer($_POST['video_allow_submission'], 0, 'video_allow_submission'),
        'video_allow_likes'      => form_sanitizer($_POST['video_allow_likes'], 0, 'video_allow_likes')
    ];

    if (\defender::safe()) {
        foreach ($settings as $key => $value) {
            if (\defender::safe()) {
                $data = [
                    'settings_name'  => $key,
                    'settings_value' => $value,
                    'settings_inf'   => 'videos'
                ];
                dbquery_insert(DB_SETTINGS_INF, $data, 'update', ['primary_key' => 'settings_name']);
            }
        }

        addNotice('success', $locale['vid_notice_09']);
    }

    redirect(FUSION_SELF.fusion_get_aidlink().'&section=settings');
}

$calc_opts = fusion_get_locale('1020', LOCALE.LOCALESET.'admin/settings.php');
$calc_c = calculate_byte($this->video_settings['video_max_b']);
$calc_b = $this->video_settings['video_max_b'] / $calc_c;
$calc_cc = calculate_byte($this->video_settings['video_screen_max_b']);
$calc_bb = $this->video_settings['video_screen_max_b'] / $calc_cc;

echo '<div class="well m-t-10">'.$locale['vid_059'].'</div>';

echo openform('settingsform', 'post', FUSION_REQUEST);
echo '<div class="row">';
    echo '<div class="col-xs-12 col-sm-8">';
        openside('');
        echo form_text('video_pagination', $locale['vid_060'], $this->video_settings['video_pagination'], [
            'max_length'  => 4,
            'type'        => 'number',
            'inline'      => TRUE,
            'inner_width' => '150px',
            'width'       => '150px'
        ]);
        closeside();

        openside('');
            echo '<div class="display-block overflow-hide">';
                echo '<label class="control-label col-xs-12 col-sm-3 col-md-3 col-lg-3 p-l-0" for="video_screen_max_w">'.$locale['vid_061'].'</label>';
                echo '<div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">';
                    echo form_text('video_screen_max_w', '', $this->video_settings['video_screen_max_w'], [
                        'class'      => 'pull-left',
                        'max_length' => 4,
                        'type'       => 'number',
                        'width'      => '150px'
                    ]);
                    echo '<i class="fa fa-close pull-left m-r-5 m-l-5 m-t-10"></i>';
                    echo form_text('video_screen_max_h', '', $this->video_settings['video_screen_max_h'], [
                        'class'      => 'pull-left',
                        'max_length' => 4,
                        'type'       => 'number',
                        'width'      => '150px'
                    ]);
                    echo '<small class="mid-opacity text-uppercase pull-left m-t-10 m-l-5">('.$locale['vid_062'].')</small>';
                echo '</div>';
            echo '</div>';

            echo '<div class="display-block overflow-hide">';
                echo '<label class="control-label col-xs-12 col-sm-3 col-md-3 col-lg-3 p-l-0" for="calc_bb">'.$locale['vid_063'].'</label>';
                echo '<div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">';
                    echo form_text('calc_bb', '', $calc_bb, [
                        'required'   => TRUE,
                        'type'       => 'number',
                        'inline'     => TRUE,
                        'width'      => '100px',
                        'max_length' => 4,
                        'class'      => 'pull-left m-r-10'
                    ]);
                    echo form_select('calc_cc', '', $calc_cc, [
                        'options'     => $calc_opts,
                        'placeholder' => $locale['choose'],
                        'class'       => 'pull-left',
                        'inner_width' => '100%',
                        'width'       => '180px'
                    ]);
                echo '</div>';
            echo '</div>';

            echo '<div class="display-block overflow-hide">';
                echo '<label class="control-label col-xs-12 col-sm-3 col-md-3 col-lg-3 p-l-0" for="calc_b">'.$locale['vid_064'].'</label>';
                    echo '<div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">';
                    echo form_text('calc_b', '', $calc_b, [
                        'required'   => TRUE,
                        'type'       => 'number',
                        'inline'     => TRUE,
                        'width'      => '100px',
                        'max_length' => 4,
                        'class'      => 'pull-left m-r-10'
                    ]);
                    echo form_select('calc_c', '', $calc_c, [
                        'options'     => $calc_opts,
                        'placeholder' => $locale['choose'],
                        'class'       => 'pull-left',
                        'inner_width' => '100%',
                        'width'       => '180px'
                    ]);
                echo '</div>';
            echo '</div>';
        closeside();
    echo '</div>';

    echo '<div class="col-xs-12 col-sm-4">';
        openside('');
        echo form_select('video_allow_submission', $locale['vid_065'], $this->video_settings['video_allow_submission'], [
            'inline'  => TRUE,
            'options' => [$locale['disable'], $locale['enable']]
        ]);

        echo form_select('video_allow_likes', $locale['vid_083'], $this->video_settings['video_allow_likes'], [
            'inline'  => TRUE,
            'options' => [$locale['disable'], $locale['enable']]
        ]);
        closeside();

        $mime = mimeTypes();
        $mime_opts = [];

        foreach ($mime as $m => $Mime) {
            $ext = ".$m";
            $mime_opts[$ext] = $ext;
        }

        openside();
        echo form_select('video_types[]', $locale['vid_066'], $this->video_settings['video_types'], [
            'options'     => $mime_opts,
            'input_id'    => 'vdtype',
            'placeholder' => $locale['choose'],
            'multiple'    => TRUE,
            'tags'        => TRUE,
            'width'       => '100%'
        ]);

        closeside();
    echo '</div>';
echo '</div>';

echo form_button('savesettings', $locale['save'], $locale['save'], ['class' => 'btn-success', 'icon' => 'fa fa-hdd-o']);
echo closeform();
