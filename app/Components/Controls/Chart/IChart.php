<?php

namespace FKSDB\Components\Controls\Chart;

use Nette\Application\UI\Control;

/**
 * Interface IChart
 * @author Michal Červeňák <miso@fykos.cz>
 */
interface IChart {

    public function getTitle(): string;

    public function getControl(): Control;

    /**
     * @return string|null
     */
    public function getDescription();
}
