<?php

namespace FKSDB\Components\Controls\Chart\Contestants\Core;

use Nette\ComponentModel\IComponent;

/**
 * Interface IChart
 * @author Michal Červeňák <miso@fykos.cz>
 */
interface Chart {

    public function getTitle(): string;

    public function getControl(): IComponent;

    public function getDescription(): ?string;
}
