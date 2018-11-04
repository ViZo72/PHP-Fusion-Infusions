<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: sitemap_panel/classes/TempFileGZIPWriter.php
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
 * Flushes buffer into temporary stream and compresses stream into a file on finish
 */
class TempFileGZIPWriter implements WriterInterface {
    /**
     * @var string Name of target file
     */
    private $filename;

    /**
     * @var string ressource for php://temp stream
     */
    private $tempFile;

    /**
     * @param string $filename target file
     */
    public function __construct($filename) {
        $this->filename = $filename;
        $this->tempFile = fopen('php://temp/', 'wb');
    }

    /**
     * Store data in a temporary stream/file
     *
     * @param string $data
     */
    public function append($data) {
        assert($this->tempFile !== NULL);

        fwrite($this->tempFile, $data);
    }

    /**
     * Deflate buffered data
     */
    public function finish() {
        assert($this->tempFile !== NULL);

        $file = fopen('compress.zlib://'.$this->filename, 'wb');
        rewind($this->tempFile);
        stream_copy_to_stream($this->tempFile, $file);

        fclose($file);
        fclose($this->tempFile);
        $this->tempFile = NULL;
    }
}
