<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: sitemap_panel/classes/Sitemap.php
| Author: Alexander Makarov <sam@rmcreative.ru>
| Co-Author: RobiNN - several code modifications for PHP-Fusion 9
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
 * A class for generating Sitemaps (http://www.sitemaps.org/)
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
     * More info about URLs limit https://www.sitemaps.org/faq.html#faq_sitemap_size
     */
    private $maxUrls = 50000;

    /**
     * @var integer number of URLs added
     */
    private $urlsCount = 0;

    /**
     * @var integer Maximum allowed number of bytes in a single file.
     */
    private $maxBytes = 10485760;

    /**
     * @var integer number of bytes already written to the current file, before compression
     */
    private $byteCount = 0;

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
    private $bufferSize = 10;

    /**
     * @var bool if XML should be indented
     */
    private $useIndent = TRUE;

    /**
     * @var bool if should XHTML namespace be specified
     * Useful for multi-language sitemap to point crawler to alternate language page via xhtml:link tag.
     * @see https://support.google.com/webmasters/answer/2620865?hl=en
     */
    private $useXhtml = FALSE;

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
     * @var WriterInterface that does the actual writing
     */
    private $writerBackend;

    /**
     * @var XMLWriter
     */
    private $writer;

    /**
     * @var bool
     */
    private $video = FALSE;

    /**
     * @var array
     */
    private $video_opt = [];

    /**
     * @param string $filePath path of the file to write to
     * @param bool   $useXhtml is XHTML namespace should be specified
     *
     * @throws \InvalidArgumentException
     */
    public function __construct($filePath, $useXhtml = FALSE) {
        $dir = dirname($filePath);
        if (!is_dir($dir)) {
            $this->saveError("Please specify valid file path. Directory not exists. You have specified: {$dir}.");
        }

        $this->filePath = $filePath;
        $this->useXhtml = $useXhtml;
    }

    /**
     * Get array of generated files
     *
     * @return array
     */
    public function getWrittenFilePath() {
        return $this->writtenFilePaths;
    }

    /**
     * Creates new file
     *
     * @throws \RuntimeException if file is not writeable
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

        if ($this->useGzip) {
            if (function_exists('deflate_init') && function_exists('deflate_add')) {
                $this->writerBackend = new DeflateWriter($filePath);
            } else {
                $this->writerBackend = new TempFileGZIPWriter($filePath);
            }
        } else {
            $this->writerBackend = new PlainFileWriter($filePath);
        }

        $this->writer = new XMLWriter();
        $this->writer->openMemory();
        $this->writer->startDocument('1.0', 'UTF-8');
        $this->writer->setIndent($this->useIndent);
        $this->writer->writeComment('XML Sitemap Generator by RobiNN <https://github.com/RobiNN1>');
        $this->writer->startElement('urlset');
        $this->writer->writeAttribute('xmlns', 'http://www.sitemaps.org/schemas/sitemap/0.9');

        if ($this->useXhtml) {
            $this->writer->writeAttribute('xmlns:xhtml', 'http://www.w3.org/1999/xhtml');
        }

        $this->writer->writeAttribute('xmlns:video', 'http://www.google.com/schemas/sitemap-video/1.1');

        /*
         * XMLWriter does not give us much options, so we must make sure, that
         * the header was written correctly and we can simply reuse any <url>
         * elements that did not fit into the previous file. (See self::flush)
         */
        $this->writer->text("\n");
        $this->flush(TRUE);
    }

    /**
     * Writes closing tags to current file
     */
    private function finishFile() {
        if ($this->writer !== NULL) {
            $this->writer->endElement();
            $this->writer->endDocument();

            /* To prevent infinite recursion through flush */
            $this->urlsCount = 0;

            $this->flush(0);
            $this->writerBackend->finish();
            $this->writerBackend = NULL;

            $this->byteCount = 0;
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
     * @param int $footSize Size of the remaining closing tags
     *
     * @throws \OverflowException
     */
    private function flush($footSize = 10) {
        $data = $this->writer->flush(TRUE);
        $dataSize = mb_strlen($data, '8bit');

        /*
         * Limit the file size of each single site map
         *
         * We use a heuristic of 10 Bytes for the remainder of the file,
         * i.e. </urlset> plus a new line.
         */
        if ($this->byteCount + $dataSize + $footSize > $this->maxBytes) {
            $this->saveError('The buffer size is too big for the defined file size limit');

            $this->finishFile();
            $this->createNewFile();
        }

        $this->writerBackend->append($data);
        $this->byteCount += $dataSize;
    }

    /**
     * Takes a string and validates, if the string
     * is a valid url
     *
     * @param string $location
     *
     * @throws \InvalidArgumentException
     */
    protected function validateLocation($location) {
        if (FALSE === filter_var($location, FILTER_VALIDATE_URL)) {
            $this->saveError("The location must be a valid URL. You have specified: {$location}.");
        }
    }

    /**
     * Adds a new item to sitemap
     *
     * @param string|array $location location item URL
     * @param integer      $lastModified last modification timestamp
     * @param string       $changeFrequency change frequency. Use one of self:: constants here
     * @param string       $priority item's priority (0.0-1.0). Default null is equal to 0.5
     *
     * @throws \InvalidArgumentException
     */
    public function addItem($location, $lastModified = NULL, $changeFrequency = NULL, $priority = NULL) {
        if ($this->urlsCount >= $this->maxUrls) {
            $this->finishFile();
        }

        if ($this->writerBackend === NULL) {
            $this->createNewFile();
        }

        $lastModified = !empty($lastModified) ? $lastModified : time();

        if (is_array($location)) {
            $this->addMultiLanguageItem($location, $lastModified, $changeFrequency, $priority);
        } else {
            $this->addSingleLanguageItem($location, $lastModified, $changeFrequency, $priority);
        }

        $this->urlsCount++;

        if ($this->urlsCount % $this->bufferSize === 0) {
            $this->flush();
        }
    }

    /**
     * Adds a new single item to sitemap
     *
     * @param string  $location location item URL
     * @param integer $lastModified last modification timestamp
     * @param float   $changeFrequency change frequency. Use one of self:: constants here
     * @param string  $priority item's priority (0.0-1.0). Default null is equal to 0.5
     *
     * @throws \InvalidArgumentException
     *
     * @see addItem
     */
    private function addSingleLanguageItem($location, $lastModified, $changeFrequency, $priority) {
        $this->validateLocation($location);

        $this->writer->startElement('url');

        $this->writer->writeElement('loc', $location);

        if ($lastModified !== NULL) {
            if ($this->video == FALSE) {
                $this->writer->writeElement('lastmod', date('c', $lastModified));
            }
        }

        if ($changeFrequency !== NULL) {
            if (!in_array($changeFrequency, $this->validFrequencies, TRUE)) {
                $this->saveError('Please specify valid changeFrequency. Valid values are: '.implode(', ', $this->validFrequencies)."You have specified: {$changeFrequency}.");
            }

            if ($this->video == FALSE) {
                $this->writer->writeElement('changefreq', $changeFrequency);
            }
        }

        if ($priority !== NULL) {
            if (!is_numeric($priority) || $priority < 0 || $priority > 1) {
                $this->saveError("Please specify valid priority. Valid values range from 0.0 to 1.0. You have specified: {$priority}.");
            }

            if ($this->video == FALSE) {
                $this->writer->writeElement('priority', number_format($priority, 1, '.', ','));
            }
        }

        if ($this->video == TRUE) {
            $this->writer->startElement('video:video');
            $this->writer->writeElement('video:title', $this->video_opt['title']);
            $this->writer->writeElement('video:thumbnail_loc', $this->video_opt['thumbnail']);

            if (!empty($this->video_opt['description'])) {
                $this->writer->writeElement('video:description', $this->video_opt['description']);
            }

            if (!empty($this->video_opt['video'])) {
                $this->writer->writeElement('video:content_loc', $this->video_opt['video']);
            }

            if (!empty($this->video_opt['player_loc'])) {
                $this->writer->writeElement('video:player_loc', $this->video_opt['player_loc']);
            }

            $this->writer->writeElement('video:view_count', $this->video_opt['views']);
            $this->writer->writeElement('video:publication_date', date('c', $lastModified));
            $this->writer->endElement(); // end video:video
        }

        $this->writer->endElement();
    }

    /**
     * Adds a multi-language item, based on multiple locations with alternate hrefs to sitemap
     *
     * @param array   $locations array of language => link pairs
     * @param integer $lastModified last modification timestamp
     * @param float   $changeFrequency change frequency. Use one of self:: constants here
     * @param string  $priority item's priority (0.0-1.0). Default null is equal to 0.5
     *
     * @throws \InvalidArgumentException
     *
     * @see addItem
     */
    private function addMultiLanguageItem($locations, $lastModified, $changeFrequency, $priority) {
        foreach ($locations as $language => $url) {
            $this->validateLocation($url);

            $this->writer->startElement('url');

            $this->writer->writeElement('loc', $url);

            if ($lastModified !== NULL) {
                $this->writer->writeElement('lastmod', date('c', $lastModified));
            }

            if ($changeFrequency !== NULL) {
                if (!in_array($changeFrequency, $this->validFrequencies, TRUE)) {
                    $this->saveError('Please specify valid changeFrequency. Valid values are: '.implode(', ', $this->validFrequencies)."You have specified: {$changeFrequency}.");
                }

                $this->writer->writeElement('changefreq', $changeFrequency);
            }

            if ($priority !== NULL) {
                if (!is_numeric($priority) || $priority < 0 || $priority > 1) {
                    $this->saveError("Please specify valid priority. Valid values range from 0.0 to 1.0. You have specified: {$priority}.");
                }

                $this->writer->writeElement('priority', number_format($priority, 1, '.', ','));
            }

            foreach ($locations as $hreflang => $href) {
                $this->writer->startElement('xhtml:link');
                $this->writer->startAttribute('rel');
                $this->writer->text('alternate');
                $this->writer->endAttribute();

                $this->writer->startAttribute('hreflang');
                $this->writer->text($hreflang);
                $this->writer->endAttribute();

                $this->writer->startAttribute('href');
                $this->writer->text($href);
                $this->writer->endAttribute();
                $this->writer->endElement();
            }

            $this->writer->endElement();
        }
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
     * Sets maximum number of bytes to write in a single file.
     * Default is 10485760 or 10 MiB.
     *
     * @param integer $number
     */
    public function setMaxBytes($number) {
        $this->maxBytes = (int)$number;
    }

    /**
     * Sets number of URLs to be kept in memory before writing it to file.
     * Default is 10.
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
     *
     * @throws \RuntimeException when trying to enable gzip while zlib is not available or when trying to change
     * setting when some items are already written
     */
    public function setUseGzip($value) {
        if ($value && !extension_loaded('zlib')) {
            $this->saveError('Zlib extension must be enabled to gzip the sitemap.');
        }

        if ($this->writerBackend !== NULL && $value != $this->useGzip) {
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

    /**
     * @param bool  $video
     * @param array $options
     */
    public function setVideoOptions($video, $options) {
        $this->video = (bool)$video;
        $this->video_opt = (array)$options;
    }
}
