<?php

namespace Tasks;

use Nette\InvalidStateException;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 * 
 * @author Michal Koutný <michal@fykos.cz>
 */
class DownloaderFactory {

    /**
     * @var string
     */
    private $httpUser;

    /**
     * @var string
     */
    private $httpPassword;

    /**
     * @var string without scheme (only domain name/IP)
     */
    private $host;

    /**
     * With leading slash for each language (ISO 639-1).
     * 
     * @var array of string sprintf mask for arguments:  contestName, year, series
     */
    private $pathMasks;

    /**
     * @var string path to directory for temporary data
     */
    private $tmpDir;

    /**
     * @var array   contestId => contest name
     */
    private $contestMap;

    public function __construct($httpUser, $httpPassword, $host, $pathMasks, $tmpDir, $contestMap) {
        $this->httpUser = $httpUser;
        $this->httpPassword = $httpPassword;
        $this->host = $host;
        $this->pathMasks = $pathMasks;
        $this->tmpDir = $tmpDir;
        $this->contestMap = $contestMap;
    }

    /**
     * 
     * @param string $language ISO 639-1
     * @return \Tasks\SeriesDownloader
     * @throws InvalidStateException
     */
    public function create($language) {
        if (!array_key_exists($language, $this->pathMasks)) {
            throw new InvalidStateException("Unspecified path mask for language '$language'.");
        }

        $downloader = new SeriesDownloader(
                $this->httpUser, $this->httpPassword, $this->host, $this->pathMasks[$language], $this->tmpDir, $this->contestMap
        );

        return $downloader;
    }

}
