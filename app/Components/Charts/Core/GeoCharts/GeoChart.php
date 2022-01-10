<?php

declare(strict_types=1);

namespace FKSDB\Components\Charts\Core\GeoCharts;

use FKSDB\Components\Charts\Core\Chart;
use Fykosak\NetteFrontendComponent\Components\FrontEndComponent;
use Nette\DI\Container;

abstract class GeoChart extends FrontEndComponent implements Chart
{
    protected const KEY_COUNT = 'count';

    public function __construct(Container $container, string $reactId)
    {
        parent::__construct($container, $reactId);
    }
}
