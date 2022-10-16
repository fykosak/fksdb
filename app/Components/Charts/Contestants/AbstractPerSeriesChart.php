<?php

declare(strict_types=1);

namespace FKSDB\Components\Charts\Contestants;

use FKSDB\Components\Charts\Core\Chart;
use FKSDB\Models\ORM\Models\ContestModel;
use Fykosak\NetteFrontendComponent\Components\FrontEndComponent;
use Nette\DI\Container;

abstract class AbstractPerSeriesChart extends FrontEndComponent implements Chart
{
    protected ContestModel $contest;

    public function __construct(Container $container, ContestModel $contest)
    {
        parent::__construct($container, 'chart.contestants.per-series');
        $this->contest = $contest;
    }
}
