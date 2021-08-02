<?php

declare(strict_types=1);

namespace FKSDB\Models\Astrid;

use FKSDB\Models\ORM\Models\ModelContestYear;
use Nette\DI\Container;

class Downloader
{

    private string $httpUser;

    private string $httpPassword;

    /** without scheme (only domain name/IP) */
    private string $host;

    /** path to directory for temporary data */
    private string $tmpDir;

    private Container $container;

    public function __construct(
        string $httpUser,
        string $httpPassword,
        string $host,
        string $tmpDir,
        Container $container
    ) {
        $this->httpUser = $httpUser;
        $this->httpPassword = $httpPassword;
        $this->host = $host;
        $this->tmpDir = $tmpDir;
        $this->container = $container;
    }

    public function downloadSeriesTasks(ModelContestYear $contestYear, int $series): string
    {
        $mask = $this->container->getParameters()['tasks']['paths'];

        $path = sprintf($mask, $contestYear->getContest()->getContestSymbol(), $contestYear->year, $series);
        return $this->download($path);
    }

    private function download(string $path): string
    {
        $src = "https://{$this->httpUser}:{$this->httpPassword}@{$this->host}{$path}";
        $dst = tempnam($this->tmpDir, 'task');

        if (!copy($src, $dst)) {
            throw new DownloadException("Cannot copy file '$src'.");
        }
        return $dst;
    }
}
