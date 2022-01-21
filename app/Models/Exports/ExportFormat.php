<?php

namespace FKSDB\Models\Exports;

use Nette\Application\Response;

interface ExportFormat
{
    public function getResponse(): Response;
}
