<?php

namespace FKSDB\Components\Charts\Contestants;

use FKSDB\Components\Charts\Core\Chart;
use FKSDB\Components\React\ReactComponent;
use FKSDB\Models\ORM\Models\ModelContest;
use Nette\DI\Container;

abstract class AbstractPerSeriesChart extends ReactComponent implements Chart
{

    protected ModelContest $contest;

    public function __construct(Container $container, ModelContest $contest)
    {
        parent::__construct($container, 'chart.contestants.per-series');
        $this->contest = $contest;
    }

    public function getControl(): self
    {
        return $this;
    }
}
