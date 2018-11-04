<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: sitemap_panel/classes/WriterInterface.php
| Author: Alexander Makarov <sam@rmcreative.ru>
| Github: https://github.com/samdark/sitemap
| Release: 2.2.0
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/

/**
 * WriterInterface represents a data sink
 *
 * Data is successively given by calling append. After calling finish all of it
 * should have been written to the target.
 */
interface WriterInterface {
    /**
     * Queue data for writing to the target
     *
     * @param string $data
     */
    public function append($data);

    /**
     * Ensure all queued data is written and close the target
     *
     * No further data may be appended after this.
     */
    public function finish();
}
