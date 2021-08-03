<?php

declare(strict_types=1);

namespace FKSDB\Components\PDFGenerators;

interface PageComponent
{
    /**
     * @param mixed $row
     */
    public function render($row): void;
}
