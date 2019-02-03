<?php

namespace Submits;

use FKSDB\ORM\ModelSubmit;

/**
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
interface IStorageProcessing {

    public function setInputFile(string $filename);

    public function setOutputFile(string $filename);

    public function process(ModelSubmit $submit);
}


