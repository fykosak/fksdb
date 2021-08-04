<?php

declare(strict_types=1);

namespace FKSDB\Components\PDFGenerators\Providers;

use FKSDB\Components\Controls\BaseComponent;

abstract class AbstractPageComponent extends BaseComponent
{
    /**
     * @param mixed $row
     */
    abstract public function render($row): void;
}
