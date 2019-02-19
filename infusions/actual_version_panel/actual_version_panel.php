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
defined('IN_FUSION') || exit;

$locale = fusion_get_locale('', AV_LOCALE);
$settings = get_settings('actual_version_panel');

opentable('PHP-Fusion');
?><div class="row">
        <div class="col-xs-12 col-sm-6 col-md-6 col-lg-6">
            <div class="text-center text-bold"><?php echo $locale['AV_103']; ?></div>
        </div>

        <div class="col-xs-12 col-sm-6 col-md-6 col-lg-6">
            <div class="row">
                <div class="col-xs-6 col-sm-6 col-md-6 col-lg-6"><div class="text-center">
                    <img style="width: 100px; height: 100px;" class="center-x img-responsive m-b-10" src="<?php echo INFUSIONS; ?>actual_version_panel/php-fusion-icon.svg" alt="Icon"/>
                    <span><strong class="text-dark"><?php echo $locale['AV_title']; ?></strong>: <?php echo $settings['actual_version']; ?></span>
                        <br/><span><a href="<?php echo $settings['phpfusion_dl_link']; ?>" target="_blank"><i class="fa fa-cloud-download"></i> <?php echo $locale['AV_001']; ?></a></span>
                </div></div>

                <div class="col-xs-6 col-sm-6 col-md-6 col-lg-6"><div class="text-center">
                    <img style="width: 100px; height: 100px;" class="center-x img-responsive m-b-10" src="<?php echo INFUSIONS; ?>actual_version_panel/language.svg" alt="Language"/>
                    <span><strong class="text-dark"><?php echo $locale['AV_002']; ?></strong></span>
                    <br/><span><a href="<?php echo $settings['translate_dl_link']; ?>" target="_blank"><i class="fa fa-cloud-download"></i> <?php echo $locale['AV_001']; ?></a></span>
                </div></div>
            </div>

        </div>
    </div><?php
closetable();
