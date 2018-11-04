<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: sitemap_panel/classes/SitemapIndex.php
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
 * A class for generating Sitemap index (https://www.sitemaps.org/protocol.html#index)
 */
class SitemapIndex {
    /**
     * @var XMLWriter
     */
    private $writer;

    /**
     * @var string index file path
     */
    private $filePath;

    /**
     * @var bool whether to gzip the resulting file or not
     */
    private $useGzip = FALSE;

    /**
     * @param string $filePath index file path
     */
    public function __construct($filePath) {
        $this->filePath = $filePath;
    }

    /**
     * Creates new file
     */
    private function createNewFile() {
        $this->writer = new XMLWriter();
        $this->writer->openMemory();
        $this->writer->startDocument('1.0', 'UTF-8');
        $this->writer->setIndent(TRUE);
        $this->writer->writeComment('XML Sitemap Generator by RobiNN <https://github.com/RobiNN1>');
        $this->writer->startElement('sitemapindex');
        $this->writer->writeAttribute('xmlns', 'http://www.sitemaps.org/schemas/sitemap/0.9');
    }

    /**
     * Adds sitemap link to the index file
     *
     * @param string  $location URL of the sitemap
     * @param integer $lastModified unix timestamp of sitemap modification time
     *
     * @throws \InvalidArgumentException
     */
    public function addSitemap($location, $lastModified = NULL) {
        if (FALSE === $this->validateURL($location)) {
            $this->saveError("The location must be a valid URL. You have specified: {$location}.");
        }

        if ($this->writer === NULL) {
            $this->createNewFile();
        }

        $this->writer->startElement('sitemap');
        $this->writer->writeElement('loc', $location);

        if ($lastModified !== NULL) {
            $this->writer->writeElement('lastmod', date('c', $lastModified));
        }

        $this->writer->endElement();
    }

    /**
     * @return string index file path
     */
    public function getFilePath() {
        return $this->filePath;
    }

    /**
     * Finishes writing
     */
    public function write() {
        if ($this->writer instanceof XMLWriter) {
            $this->writer->endElement();
            $this->writer->endDocument();
            $filePath = $this->getFilePath();

            if ($this->useGzip) {
                $filePath = 'compress.zlib://'.$filePath;
            }

            file_put_contents($filePath, $this->writer->flush());
        }
    }

    /**
     * Sets whether the resulting file will be gzipped or not.
     *
     * @param bool $value
     *
     * @throws \RuntimeException when trying to enable gzip while zlib is not available
     */
    public function setUseGzip($value) {
        if ($value && !extension_loaded('zlib')) {
            $this->saveError('Zlib extension must be installed to gzip the sitemap.');
        }

        $this->useGzip = $value;
    }

    /**
     * Validate URL
     *
     * @param $url
     *
     * @return bool
     */
    private function validateURL($url) {
        if (function_exists('curl_version')) {
            $fp = curl_init($url);
            curl_setopt($fp, CURLOPT_TIMEOUT, 20);
            curl_setopt($fp, CURLOPT_FAILONERROR, TRUE);
            curl_setopt($fp, CURLOPT_REFERER, $url);
            curl_setopt($fp, CURLOPT_RETURNTRANSFER, TRUE);
            curl_setopt($fp, CURLOPT_USERAGENT, 'Googlebot/2.1 (+http://www.google.com/bot.html)');
            curl_setopt($fp, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_exec($fp);
            if (curl_errno($fp) != 0) {
                curl_close($fp);
                return FALSE;
            } else {
                curl_close($fp);
                return $url;
            }
        } else if (filter_var($url, FILTER_VALIDATE_URL)) {
            return $url;
        } else if (preg_match("/\b(?:(?:https?|ftp):\/\/|www\.)[-a-z0-9+&@#\/%?=~_|!:,.;]*[-a-z0-9+&@#\/%=~_|]/i", $url)) {
            return $url;
        }

        return FALSE;
    }

    /**
     * Custom error handler
     *
     * @param string $message
     */
    private function saveError($message) {
        setError(2, $message, debug_backtrace()[1]['file'], debug_backtrace()[1]['line'], '');
    }
}
