<?php

namespace FKSDB\Astrid;

use FKSDB\Config\GlobalParameters;
use FKSDB\ORM\Models\ModelContest;

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

    /**
     * Downloader constructor.
     * @param string $httpUser
     * @param string $httpPassword
     * @param string $host
     * @param string $tmpDir
     * @param array $contestMap
     * @param GlobalParameters $parameters
     */
    public function __construct(string $httpUser, string $httpPassword, string $host, string $tmpDir, array $contestMap, GlobalParameters $parameters) {
        $this->httpUser = $httpUser;
        $this->httpPassword = $httpPassword;
        $this->host = $host;
        $this->tmpDir = $tmpDir;
        $this->contestMap = $contestMap;
        $this->parameters = $parameters;
    }
    /**
     * @param ModelContest $contest
     * @param int $year
     * @param int $series
     * @return string filename of downloaded XML file
     */
    public function downloadSeriesTasks(ModelContest $contest, int $year, int $series): string {
        $mask = $this->parameters['tasks']['paths'];
        $contestName = isset($this->contestMap[$contest->contest_id]) ? $this->contestMap[$contest->contest_id] : $contest->contest_id;

        $path = sprintf($mask, $contestName, $year, $series);
        return $this->download($path);
    }

    /**
     * @param $path
     * @return bool|string
     */
    private function download(string $path) {
        $src = "https://{$this->httpUser}:{$this->httpPassword}@{$this->host}{$path}";
        $dst = tempnam($this->tmpDir, 'task');

        if (!@copy($src, $dst)) {
            throw new DownloadException("Cannot copy file '$src'.");
        }
        return $dst;
    }
}
