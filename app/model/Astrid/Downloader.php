<?php

namespace Astrid;

use FKS\Config\GlobalParameters;
use ModelContest;
use Nette\InvalidStateException;
use RuntimeException;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 * 
 * @author Michal Koutný <michal@fykos.cz>
 */
class Downloader {

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
     * @var string path to directory for temporary data
     */
    private $tmpDir;

    /**
     * @var array   contestId => contest name
     */
    private $contestMap;

    /**
     * @var GlobalParameters
     */
    private $parameters;

    public function __construct($httpUser, $httpPassword, $host, $tmpDir, $contestMap, GlobalParameters $parameters) {
        $this->httpUser = $httpUser;
        $this->httpPassword = $httpPassword;
        $this->host = $host;
        $this->tmpDir = $tmpDir;
        $this->contestMap = $contestMap;
        $this->parameters = $parameters;
    }

    /**
     * @param \Tasks\ModelContest $contest
     * @param int $year
     * @param int $series
     * @param string $language
     * @return string filename of downloaded XML file
     */
    public function downloadSeriesTasks(ModelContest $contest, $year, $series, $language) {
        if (!array_key_exists($language, $this->parameters['tasks']['paths'])) {
            throw new InvalidStateException("Unspecified path mask for language '$language'.");
        }

        $mask = $this->parameters['tasks']['paths'][$language];
        $contestName = isset($this->contestMap[$contest->contest_id]) ? $this->contestMap[$contest->contest_id] : $contest->contest_id;

        $path = sprintf($mask, $contestName, $year, $series);
        return $this->download($path);
    }

    private function download($path) {
        $src = "https://{$this->httpUser}:{$this->httpPassword}@{$this->host}{$path}";
        $dst = tempnam($this->tmpDir, 'task');

        if (!@copy($src, $dst)) {
            throw new DownloadException("Cannot copy file '$src'.");
        }

        return $dst;
    }

}

class DownloadException extends RuntimeException {
    
}
