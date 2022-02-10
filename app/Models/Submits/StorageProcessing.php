<?php

declare(strict_types=1);

namespace FKSDB\Models\Submits;

use FKSDB\Models\ORM\Models\ModelSubmit;

interface StorageProcessing
{
    public function setInputFile(string $filename): void;

    public function setOutputFile(string $filename): void;

    public function process(ModelSubmit $submit): void;
}
