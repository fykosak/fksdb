<?php

declare(strict_types=1);

namespace FKSDB\Models;

use FKSDB\Models\ORM\Models\ContestModel;
use FKSDB\Modules\Core\Language;
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

    public function getNews(ContestModel $contest, Language $lang): array
    {
        if (!isset($this->container->getParameters()[$contest->getContestSymbol()]['news'][$lang->value])) {
            return [];
        }
        $news = $this->container->getParameters()[$contest->getContestSymbol()]['news'][$lang->value];
        if ($news) {
            return $news;
        } else {
            return [];
        }
    }
}
