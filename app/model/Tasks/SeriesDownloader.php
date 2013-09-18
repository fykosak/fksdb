<?php

namespace Tasks;

use ModelContest;
use RuntimeException;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 * 
 * @author Michal Koutný <michal@fykos.cz>
 */
class SeriesDownloader {
    /**
     * Name prefix of temporary created files.
     */

    const FILE_PREFIX = 'series';

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
     * With leading slash.
     * 
     * @var string sprintf mask for arguments:  contestName, year, series
     */
    private $pathMask;

    /**
     * @var string path to directory for temporary data
     */
    private $tmpDir;

    /**
     * @var array   contestId => contest name
     */
    private $contestMap;

    public function __construct($httpUser, $httpPassword, $host, $pathMask, $tmpDir, $contestMap) {
        $this->httpUser = $httpUser;
        $this->httpPassword = $httpPassword;
        $this->host = $host;
        $this->pathMask = $pathMask;
        $this->tmpDir = $tmpDir;
        $this->contestMap = $contestMap;
    }

    /**
     * @param \Tasks\ModelContest $contest
     * @param int $year
     * @param int $series
     * @return string filename of downloaded XML file
     */
    public function download(ModelContest $contest, $year, $series) {
        $contestName = isset($this->contestMap[$contest->contest_id]) ? $this->contestMap[$contest->contest_id] : $contest->contest_id;

        $path = sprintf($this->pathMask, $contestName, $year, $series);
        $src = "http://{$this->httpUser}:{$this->httpPassword}@{$this->host}{$path}";
        $dst = tempnam($this->tmpDir, 'task');

        if (!@copy($src, $dst)) {
            throw new DownloadException("Cannot copy file '$src'.");
        }

        return $dst;
    }

}

class DownloadException extends RuntimeException {
    
}
