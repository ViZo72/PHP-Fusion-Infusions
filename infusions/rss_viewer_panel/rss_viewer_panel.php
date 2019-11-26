<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: rss_viewer_panel.php
| Author: RobiNN
| Version: 1.0.0
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

$sites = [
    [
        'name' => 'Blog',
        'url'  => 'http://pf.host/infusions/rss_feeds_panel/feeds/rss_blog.php'
    ],
    [
        'name' => 'News',
        'url'  => 'http://pf.host/infusions/rss_feeds_panel/feeds/rss_news.php'
    ],
];

echo '<div class="row">';

foreach ($sites as $site) {
    $dom = new \DOMDocument();
    $rss = $dom->load($site['url']);
    $channel = $dom->getElementsByTagName('channel')->item(0);

    echo '<div class="col-xs-12 col-sm-6">';
    openside($site['name']);

    foreach($channel->getElementsByTagName('item') as $item) {
        $title = $item->getElementsByTagName('title')->item(0)->firstChild->data;
        $link = $item->getElementsByTagName('link')->item(0)->firstChild->data;
        //$description = $item->getElementsByTagName('description')->item(0)->firstChild->data;

        echo '<a href="'.$link.'" target="_blank">'.$title.'</a>';
        /*echo '<div>';
            echo trimlink(strip_tags(parse_textarea($description, FALSE, TRUE)), 100);
        echo '</div>';*/
        echo '<hr class="m-0">';
    }

    closeside();
    echo '</div>';
}

echo '</div>';
