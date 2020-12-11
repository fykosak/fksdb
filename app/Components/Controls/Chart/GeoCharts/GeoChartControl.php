<?php

namespace FKSDB\Components\Controls\Chart\GeoCharts;

use FKSDB\Components\Controls\Chart\IChart;
use Fykosak\Utils\FrontEndComponents\FrontEndComponent;
use Nette\DI\Container;
use Nette\InvalidStateException;

abstract class GeoChartControl extends FrontEndComponent implements IChart {
    protected const SCALE_LINEAR = 'linear';
    protected const SCALE_LOG = 'log';

    protected const KEY_COUNT = 'count';

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

    public function getControl(): self {
        return $this;
    }
}
