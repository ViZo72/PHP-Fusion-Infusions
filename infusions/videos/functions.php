<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: videos/functions.php
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

function GetVideoData($url, $type = 'youtube') {
    $json_url = '';

    if ($type === 'youtube') {
        $url = filter_var($url, FILTER_VALIDATE_URL) == FALSE ? 'https://www.youtube.com/watch?v='.$url : $url;
        $json_url = 'https://www.youtube.com/oembed?url='.$url.'&format=json';
    } else if ($type === 'vimeo') {
        $json_url = 'https://vimeo.com/api/oembed.json?url='.$url;
    }

    if (!empty($json_url)) {
        $curl = curl_init($json_url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $return = curl_exec($curl);
        curl_close($curl);
        $json = json_decode($return, TRUE);

        if ($type === 'youtube') {
            preg_match('%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/ ]{11})%i', $url, $match);
            $json['video_id'] = $match[1];
        }

        return $json;
    }

    return NULL;
}

function GetVideoThumb($data) {
    if ($data['video_type'] == 'youtube' || $data['video_type'] == 'vimeo') {
        if (!empty($data['video_image']) && file_exists(VIDEOS.'images/'.$data['video_image'])) {
            $thumb = VIDEOS.'images/'.$data['video_image'];
        } else {
            $video_data = GetVideoData($data['video_url'], $data['video_type']);

            if (!empty($video_data['thumbnail_url'])) {
                $thumb = $video_data['thumbnail_url'];
            } else {
                $thumb = VIDEOS.'images/default_thumbnail.jpg';
            }
        }
    } else if (!empty($data['video_image']) && file_exists(VIDEOS.'images/'.$data['video_image'])) {
        $thumb = VIDEOS.'images/'.$data['video_image'];
    } else {
        $thumb = VIDEOS.'images/default_thumbnail.jpg';
    }

    return $thumb;
}
