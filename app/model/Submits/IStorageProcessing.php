<?php

namespace FKSDB\Submits;

use FKSDB\ORM\Models\ModelSubmit;

/**
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
interface IStorageProcessing {

    /**
     * @param string $filename
     * @return mixed
     */
    public function setInputFile(string $filename);

    /**
     * @param string $filename
     * @return mixed
     */
    public function setOutputFile(string $filename);

    /**
     * @param \FKSDB\ORM\Models\ModelSubmit $submit
     * @return mixed
     */
    public function process(ModelSubmit $submit);
}


