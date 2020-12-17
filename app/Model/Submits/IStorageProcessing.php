<?php

namespace FKSDB\Model\Submits;

use FKSDB\Model\ORM\Models\ModelSubmit;

/**
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
interface IStorageProcessing {

    public function setInputFile(string $filename): void;

    public function setOutputFile(string $filename): void;

    public function process(ModelSubmit $submit): void;
}