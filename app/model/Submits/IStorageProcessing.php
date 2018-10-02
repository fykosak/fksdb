<?php

namespace Submits;

use FKSDB\ORM\ModelSubmit;

/**
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
interface IStorageProcessing {

    public function setInputFile($filename);

    public function setOutputFile($filename);

    public function process(ModelSubmit $submit);
}

?>
