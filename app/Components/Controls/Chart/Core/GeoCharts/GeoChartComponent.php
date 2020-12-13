<?php

namespace FKSDB\Components\Controls\Chart\Contestants\Core\Core\GeoCharts;

use FKSDB\Components\Controls\Chart\Contestants\Core\Chart;
use FKSDB\Components\React\ReactComponent;
use Nette\DI\Container;
use Nette\InvalidStateException;

abstract class GeoChartComponent extends ReactComponent implements Chart {

    protected const KEY_COUNT = 'count';

    public function __construct(Container $container, string $reactId) {
        parent::__construct($container, $reactId);
    }

    public function getControl(): self {
        return $this;
    }
}
