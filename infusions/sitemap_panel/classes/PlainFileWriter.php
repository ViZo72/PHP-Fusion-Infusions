<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: sitemap_panel/classes/PlainFileWriter.php
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
 * Writes the given data as-is into a file
 */
class PlainFileWriter implements WriterInterface {
    /**
     * @var resource for target file
     */
    private $file;

    /**
     * @param string $filename target file
     */
    public function __construct($filename) {
        $this->file = fopen($filename, 'ab');
    }

    /**
     * @inheritdoc
     */
    public function append($data) {
        assert($this->file !== NULL);

        fwrite($this->file, $data);
    }

    /**
     * @inheritdoc
     */
    public function finish() {
        assert($this->file !== NULL);

        fclose($this->file);
        $this->file = NULL;
    }
}
