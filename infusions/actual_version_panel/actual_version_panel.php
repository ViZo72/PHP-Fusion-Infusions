<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: actual_version_panel/actual_version_panel.php
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

if (!function_exists('actual_version_panel')) {
    function actual_version_panel() {
        $locale = fusion_get_locale('', AV_LOCALE);
        $settings = get_settings('actual_version_panel');

        opentable('PHP-Fusion');
            echo '<div class="row">';
                echo '<div class="col-xs-12 col-sm-6 col-md-6 col-lg-6">';
                    echo '<div class="text-center text-bold">'.$locale['AV_103'].'</div>';
                echo '</div>';

                echo '<div class="col-xs-12 col-sm-6 col-md-6 col-lg-6">';
                    echo '<div class="row">';
                        echo '<div class="col-xs-6 col-sm-6 col-md-6 col-lg-6"><div class="text-center">';
                            echo '<img style="width: 100px; height: 100px;" class="center-x img-responsive m-b-10" src="'.INFUSIONS.'actual_version_panel/php-fusion-icon.svg" alt="Icon"/>';
                            echo '<span><strong class="text-dark">'.$locale['AV_title'].'</strong>: '.$settings['actual_version'].'</span>';
                            echo '<br/><span><a href="'.$settings['phpfusion_dl_link'].'" target="_blank"><i class="fa fa-cloud-download"></i> '.$locale['AV_001'].'</a></span>';
                        echo '</div></div>';

                        echo '<div class="col-xs-6 col-sm-6 col-md-6 col-lg-6"><div class="text-center">';
                            echo '<img style="width: 100px; height: 100px;" class="center-x img-responsive m-b-10" src="'.INFUSIONS.'actual_version_panel/language.svg" alt="Language"/>';
                            echo '<span><strong class="text-dark">'.$locale['AV_002'].'</strong></span>';
                            echo '<br/><span><a href="'.$settings['translate_dl_link'].'" target="_blank"><i class="fa fa-cloud-download"></i> '.$locale['AV_001'].'</a></span>';
                        echo '</div></div>';
                    echo '</div>';

                echo '</div>';
            echo '</div>';
        closetable();
    }
}

echo actual_version_panel();
