<?php

namespace FKSDB\Model;

use FKSDB\Model\ORM\Models\ModelContest;
use Nette\DI\Container;
use Nette\SmartObject;

/**
 * Class News
 */
class News {
    use SmartObject;

    private Container $container;

    public function __construct(Container $container) {
        $this->container = $container;
    }

    public function getNews(ModelContest $contest, string $lang): array {
        $contestName = $this->container->getParameters()['contestMapping'][$contest->contest_id];
        if (!isset($this->container->getParameters()[$contestName]['news'][$lang])) {
            return [];
        }
        $news = $this->container->getParameters()[$contestName]['news'][$lang];
        if ($news) {
            return $news;
        } else {
            return [];
        }
    }
}
