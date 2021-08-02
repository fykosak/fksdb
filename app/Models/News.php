<?php

declare(strict_types=1);

namespace FKSDB\Models;

use FKSDB\Models\ORM\Models\ModelContest;
use Nette\DI\Container;
use Nette\SmartObject;

class News
{
    use SmartObject;

    private Container $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    public function getNews(ModelContest $contest, string $lang): array
    {
        if (!isset($this->container->getParameters()[$contest->getContestSymbol()]['news'][$lang])) {
            return [];
        }
        $news = $this->container->getParameters()[$contest->getContestSymbol()]['news'][$lang];
        if ($news) {
            return $news;
        } else {
            return [];
        }
    }
}
