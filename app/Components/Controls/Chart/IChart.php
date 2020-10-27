<?php

namespace FKSDB\Components\Controls\Chart;

use Nette\ComponentModel\IComponent;

/**
 * Interface IChart
 * @author Michal Červeňák <miso@fykos.cz>
 */
interface IChart {

    public function getTitle(): string;

    public function getControl(): IComponent;

    public function getDescription(): ?string;
}
