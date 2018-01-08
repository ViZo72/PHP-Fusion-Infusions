<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: sitemap_panel/Sitemap.php
| Author: Alexander Makarov <sam@rmcreative.ru>
| Co-Author: RobiNN - several code modifications for PHP-Fusion 9
| Github: https://github.com/samdark/sitemap
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
namespace samdark\sitemap;

use XMLWriter;

/**
 * Class Sitemap
 * A class for generating Sitemaps (http://www.sitemaps.org/)
 * @package samdark\sitemap
 */
class Sitemap {
    const ALWAYS = 'always';
    const HOURLY = 'hourly';
    const DAILY = 'daily';
    const WEEKLY = 'weekly';
    const MONTHLY = 'monthly';
    const YEARLY = 'yearly';
    const NEVER = 'never';

    /**
     * @var integer Maximum allowed number of URLs in a single file.
     */
    private $maxUrls = 50000;

    /**
     * @var integer number of URLs added
     */
    private $urlsCount = 0;

    /**
     * @var string path to the file to be written
     */
    private $filePath;

    /**
     * @var integer number of files written
     */
    private $fileCount = 0;

    /**
     * @var array path of files written
     */
    private $writtenFilePaths = [];

    /**
     * @var integer number of URLs to be kept in memory before writing it to file
     */
    private $bufferSize = 1000;

    /**
     * @var bool if XML should be indented
     */
    private $useIndent = TRUE;

    /**
     * @var array valid values for frequency parameter
     */
    private $validFrequencies = [
        self::ALWAYS,
        self::HOURLY,
        self::DAILY,
        self::WEEKLY,
        self::MONTHLY,
        self::YEARLY,
        self::NEVER
    ];

    /**
     * @var bool whether to gzip the resulting files or not
     */
    private $useGzip = FALSE;

    /**
     * @var XMLWriter
     */
    private $writer;

    /**
     * @var resource for writable incremental deflate context
     */
    private $deflateContext;

    /**
     * @var resource for php://temp stream
     */
    private $tempFile;

    /**
     * @param string $filePath path of the file to write to
     */
    public function __construct($filePath) {
        $dir = dirname($filePath);
        if (!is_dir($dir)) {
            $this->saveError("Please specify valid file path. Directory not exists. You have specified: {$dir}.");
        }

        $this->filePath = $filePath;
    }

    /**
     * Get array of generated files
     * @return array
     */
    public function getWrittenFilePath() {
        return $this->writtenFilePaths;
    }

    /**
     * Creates new file
     */
    private function createNewFile() {
        $this->fileCount++;
        $filePath = $this->getCurrentFilePath();
        $this->writtenFilePaths[] = $filePath;

        if (file_exists($filePath)) {
            $filePath = realpath($filePath);
            if (is_writable($filePath)) {
                unlink($filePath);
            } else {
                $this->saveError("File \"$filePath\" is not writable.");
            }
        }

        $this->writer = new XMLWriter();
        $this->writer->openMemory();
        $this->writer->startDocument('1.0', 'UTF-8');
        $this->writer->setIndent($this->useIndent);
        $this->writer->writeComment('XML Sitemap generator by RobiNN <https://github.com/RobiNN1>');
        $this->writer->startElement('urlset');
        $this->writer->writeAttribute('xmlns', 'http://www.sitemaps.org/schemas/sitemap/0.9');
    }

    /**
     * Writes closing tags to current file
     */
    private function finishFile() {
        if ($this->writer !== NULL) {
            $this->writer->endElement();
            $this->writer->endDocument();
            $this->flush(TRUE);
        }
    }

    /**
     * Finishes writing
     */
    public function write() {
        $this->finishFile();
    }

    /**
     * Flushes buffer into file
     *
     * @param bool $finishFile Pass true to close the file to write to, used only when useGzip is true
     */
    private function flush($finishFile = FALSE) {
        if ($this->useGzip) {
            $this->flushGzip($finishFile);
            return;
        }

        file_put_contents($this->getCurrentFilePath(), $this->writer->flush(TRUE), FILE_APPEND);
    }

    /**
     * Decides how to flush buffer into compressed file
     *
     * @param bool $finishFile Pass true to close the file to write to
     */
    private function flushGzip($finishFile = FALSE) {
        if (function_exists('deflate_init') && function_exists('deflate_add')) {
            $this->flushWithIncrementalDeflate($finishFile);
            return;
        }

        $this->flushWithTempFileFallback($finishFile);
    }

    /**
     * Flushes buffer into file with incremental deflating data, available in php 7.0+
     *
     * @param bool $finishFile Pass true to write last chunk with closing headers
     */
    private function flushWithIncrementalDeflate($finishFile = FALSE) {
        $flushMode = $finishFile ? ZLIB_FINISH : ZLIB_NO_FLUSH;

        if (empty($this->deflateContext)) {
            $this->deflateContext = deflate_init(ZLIB_ENCODING_GZIP);
        }

        $compressedChunk = deflate_add($this->deflateContext, $this->writer->flush(TRUE), $flushMode);
        file_put_contents($this->getCurrentFilePath(), $compressedChunk, FILE_APPEND);

        if ($finishFile) {
            $this->deflateContext = NULL;
        }
    }

    /**
     * Flushes buffer into temporary stream and compresses stream into a file on finish
     *
     * @param bool $finishFile Pass true to compress temporary stream into desired file
     */
    private function flushWithTempFileFallback($finishFile = FALSE) {
        if (empty($this->tempFile) || !is_resource($this->tempFile)) {
            $this->tempFile = fopen('php://temp/', 'w');
        }

        fwrite($this->tempFile, $this->writer->flush(TRUE));

        if ($finishFile) {
            $file = fopen('compress.zlib://'.$this->getCurrentFilePath(), 'w');
            rewind($this->tempFile);
            stream_copy_to_stream($this->tempFile, $file);
            fclose($file);
            fclose($this->tempFile);
        }
    }

    /**
     * Takes a string and validates, if the string
     * is a valid url
     *
     * @param string $location
     */
    protected function validateLocation($location) {
        if (FALSE === filter_var($location, FILTER_VALIDATE_URL)) {
            $this->saveError("The location must be a valid URL. You have specified: {$location}.");
        }
    }

    /**
     * Adds a new item to sitemap
     *
     * @param string  $location location item URL
     * @param integer $lastModified last modification timestamp
     * @param float   $changeFrequency change frequency. Use one of self:: constants here
     * @param string  $priority item's priority (0.0-1.0). Default null is equal to 0.5
     */
    public function addItem($location, $lastModified = NULL, $changeFrequency = NULL, $priority = NULL) {
        if ($this->urlsCount === 0) {
            $this->createNewFile();
        } else if ($this->urlsCount % $this->maxUrls === 0) {
            $this->finishFile();
            $this->createNewFile();
        }

        if ($this->urlsCount % $this->bufferSize === 0) {
            $this->flush();
        }

        $this->writer->startElement('url');

        $this->validateLocation($location);

        $this->writer->writeElement('loc', $location);

        if (!empty($lastModified)) {
            if ($lastModified !== NULL) {
                $this->writer->writeElement('lastmod', date('c', $lastModified));
            }
        }

        if (!empty($changeFrequency)) {
            if ($changeFrequency !== NULL) {
                if (!in_array($changeFrequency, $this->validFrequencies, TRUE)) {
                    $this->saveError('Please specify valid changeFrequency. Valid values are: '.implode(', ', $this->validFrequencies)."You have specified: {$changeFrequency}.");
                }

                $this->writer->writeElement('changefreq', $changeFrequency);
            }
        }

        if (!empty($priority)) {
            if ($priority !== NULL) {
                if (!is_numeric($priority) || $priority < 0 || $priority > 1) {
                    $this->saveError("Please specify valid priority. Valid values range from 0.0 to 1.0. You have specified: {$priority}.");
                }

                $this->writer->writeElement('priority', number_format($priority, 1, '.', ','));
            }
        }

        $this->writer->endElement();

        $this->urlsCount++;
    }

    /**
     * @return string path of currently opened file
     */
    private function getCurrentFilePath() {
        if ($this->fileCount < 2) {
            return $this->filePath;
        }

        $parts = pathinfo($this->filePath);

        if ($parts['extension'] === 'gz') {
            $filenameParts = pathinfo($parts['filename']);
            if (!empty($filenameParts['extension'])) {
                $parts['filename'] = $filenameParts['filename'];
                $parts['extension'] = $filenameParts['extension'].'.gz';
            }
        }

        return $parts['dirname'].DIRECTORY_SEPARATOR.$parts['filename'].'_'.$this->fileCount.'.'.$parts['extension'];
    }

    /**
     * Returns an array of URLs written
     *
     * @param string $baseUrl base URL of all the sitemaps written
     *
     * @return array URLs of sitemaps written
     */
    public function getSitemapUrls($baseUrl) {
        $urls = [];
        foreach ($this->writtenFilePaths as $file) {
            $urls[] = $baseUrl.pathinfo($file, PATHINFO_BASENAME);
        }

        return $urls;
    }

    /**
     * Sets maximum number of URLs to write in a single file.
     * Default is 50000.
     *
     * @param integer $number
     */
    public function setMaxUrls($number) {
        $this->maxUrls = (int)$number;
    }

    /**
     * Sets number of URLs to be kept in memory before writing it to file.
     * Default is 1000.
     *
     * @param integer $number
     */
    public function setBufferSize($number) {
        $this->bufferSize = (int)$number;
    }

    /**
     * Sets if XML should be indented.
     * Default is true.
     *
     * @param bool $value
     */
    public function setUseIndent($value) {
        $this->useIndent = (bool)$value;
    }

    /**
     * Sets whether the resulting files will be gzipped or not.
     *
     * @param bool $value
     */
    public function setUseGzip($value) {
        if ($value && !extension_loaded('zlib')) {
            $this->saveError('Zlib extension must be enabled to gzip the sitemap.');
        }

        if ($this->urlsCount && $value != $this->useGzip) {
            $this->saveError('Cannot change the gzip value once items have been added to the sitemap.');
        }

        $this->useGzip = $value;
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
