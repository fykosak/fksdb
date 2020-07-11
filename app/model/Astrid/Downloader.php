<?php

namespace FKSDB\Astrid;

use FKSDB\ORM\Models\ModelContest;
use Nette\DI\Container;

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

    /** @var Container */
    private $container;

    /**
     * Downloader constructor.
     * @param string $httpUser
     * @param string $httpPassword
     * @param string $host
     * @param string $tmpDir
     * @param array $contestMap
     * @param Container $container
     */
    public function __construct(string $httpUser, string $httpPassword, string $host, string $tmpDir, array $contestMap, Container $container) {
        $this->httpUser = $httpUser;
        $this->httpPassword = $httpPassword;
        $this->host = $host;
        $this->tmpDir = $tmpDir;
        $this->contestMap = $contestMap;
        $this->container = $container;
    }

    /**
     * @param ModelContest $contest
     * @param int $year
     * @param int $series
     * @return string filename of downloaded XML file
     */
    public function downloadSeriesTasks(ModelContest $contest, int $year, int $series): string {
        $mask = $this->container->getParameters()['tasks']['paths'];
        $contestName = isset($this->contestMap[$contest->contest_id]) ? $this->contestMap[$contest->contest_id] : $contest->contest_id;

        $path = sprintf($mask, $contestName, $year, $series);
        return $this->download($path);
    }

    private function download(string $path): string {
        $src = "https://{$this->httpUser}:{$this->httpPassword}@{$this->host}{$path}";
        $dst = tempnam($this->tmpDir, 'task');

        if (!@copy($src, $dst)) {
            throw new DownloadException("Cannot copy file '$src'.");
        }
        return $dst;
    }
}
