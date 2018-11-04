<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: sitemap_panel/classes/DefalteWriter.php
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
 * Flushes buffer into file with incremental deflating data, available in PHP 7.0+
 */
class DeflateWriter implements WriterInterface {
    /**
     * @var resource for target file
     */
    private $file;

    /**
     * @var resource for writable incremental deflate context
     */
    private $deflateContext;

    /**
     * @param string $filename target file
     */
    public function __construct($filename) {
        $this->file = fopen($filename, 'ab');
        $this->deflateContext = deflate_init(ZLIB_ENCODING_GZIP);
    }

    /**
     * Deflate data in a deflate context and write it to the target file
     *
     * @param string $data
     * @param int    $flushMode zlib flush mode to use for writing
     */
    private function write($data, $flushMode) {
        assert($this->file !== NULL);

        $compressedChunk = deflate_add($this->deflateContext, $data, $flushMode);
        fwrite($this->file, $compressedChunk);
    }

    /**
     * Store data in a deflate stream
     *
     * @param string $data
     */
    public function append($data) {
        $this->write($data, ZLIB_NO_FLUSH);
    }

    /**
     * Make sure all data was written
     */
    public function finish() {
        $this->write('', ZLIB_FINISH);

        $this->file = NULL;
        $this->deflateContext = NULL;
    }
}
