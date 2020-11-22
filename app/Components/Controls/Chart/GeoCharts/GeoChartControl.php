<?php

namespace FKSDB\Components\Controls\Chart;

use FKSDB\Components\Controls\Chart\IChart;
use FKSDB\Components\React\ReactComponent;
use Nette\DI\Container;
use Nette\InvalidStateException;

abstract class GeoChartControl extends ReactComponent implements IChart {
    protected const SCALE_LINEAR = 'linear';
    protected const SCALE_LOG = 'log';

    public function __construct(Container $container, string $scale) {
        parent::__construct($container, $this->getReactId($scale));
    }

    private function getReactId(string $scale): string {
        switch ($scale) {
            case self::SCALE_LINEAR;
                return 'chart.items-per-country-linear';
            case self::SCALE_LOG;
                return 'chart.items-per-country-log';
        }
        throw new InvalidStateException();
    }

}