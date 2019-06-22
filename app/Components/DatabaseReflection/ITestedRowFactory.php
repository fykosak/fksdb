<?php

namespace FKSDB\Components\Forms\Factories;

use FKSDB\ORM\AbstractModelSingle;
use FKSDB\ValidationTest\ValidationLog;

/**
 * Interface ITestedRowFactory
 * @package FKSDB\Components\Forms\Factories
 */
interface ITestedRowFactory {
    /**
     * @param AbstractModelSingle $model
     * @return ValidationLog
     */
    public function runTest(AbstractModelSingle $model): ValidationLog;
}
