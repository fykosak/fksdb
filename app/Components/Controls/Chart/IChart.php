<?php

namespace FKSDB\Components\Controls\Chart;

use Nette\Application\UI\Control;

/**
 * Interface IChart
 * @package FKSDB\Components\Controls\Chart
 */
interface IChart {
    /**
     * @return string
     */
    public function getAction(): string;

    /**
     * @return string
     */
    public function getTitle(): string;

    /**
     * @return Control
     */
    public function getControl(): Control;

    /**
     * @return string
     */
    public function getDescription();
}
