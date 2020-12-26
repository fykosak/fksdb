<?php

namespace FKSDB\Models\Submits;

use FKSDB\Models\ORM\Models\ModelSubmit;

/**
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
interface IStorageProcessing {

    public function setInputFile(string $filename): void;

    public function setOutputFile(string $filename): void;

    public function process(ModelSubmit $submit): void;
}
