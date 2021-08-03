<?php

declare(strict_types=1);

namespace FKSDB\Components\PDFGenerators\Provider;

use FKSDB\Components\PDFGenerators\PageComponent;

interface Provider
{
    public function createComponentPage(): PageComponent;

    public function getItems(): iterable;

    public function getFormat(): string;
}
