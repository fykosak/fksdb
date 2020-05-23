<?php

namespace FKSDB\Components\Controls\Chart;

use Nette\Application\UI\Control;

/**
 * Interface IChart
 * @package FKSDB\Components\Controls\Chart
 */
interface IChart {

    public function getAction(): string;

    public function getTitle(): string;

    public function getControl(): Control;

    /**
     * @return string
     */
    public function getDescription();
}
