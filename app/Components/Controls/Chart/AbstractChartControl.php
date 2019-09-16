<?php

namespace FKSDB\Components\Controls\Chart;

use FKSDB\Components\React\ReactComponent;

/**
 * Class AbstractChartControl
 */
abstract class AbstractChartControl extends ReactComponent {
    /**
     * @return string
     */
    public final function getModuleName(): string {
        return 'chart';
    }

    /**
     * @return string
     */
    abstract function getAction(): string;
}