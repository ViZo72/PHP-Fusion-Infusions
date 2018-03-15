<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: snow_panel.php
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
add_to_footer('<script type="text/javascript" src="'.INFUSIONS.'snow_panel/js/jsnow.min.js"></script>');

add_to_jquery('jQuery().jSnow({
    followScroll: true,
    flakes: 25,
    fallingSpeedMin: 1,
    fallingSpeedMax: 3,
    flakeMaxSize: 20,
    flakeMinSize: 10,
    flakeColor: [ "#efefef" ],
    vSize: 500,
    fadeAway: 1,
    zIndex: 100000,
    flakeCode: ["&#10053;"]
});');
