<?php

declare(strict_types=1);

namespace FKSDB\Models\Exports;

use Nette\Application\Response;

interface ExportFormat
{
    public function getResponse(): Response;
}
