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
     * @return void
     */
    public function setInputFile(string $filename);

    /**
     * @param string $filename
     * @return void
     */
    public function setOutputFile(string $filename);

    /**
     * @param ModelSubmit $submit
     * @return void
     */
    public function process(ModelSubmit $submit);
}
