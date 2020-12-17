<?php

namespace FKSDB\Model\Payment\PriceCalculator\PreProcess;

use FKSDB\Model\ORM\Models\ModelPayment;
use FKSDB\Model\Payment\Price;

/**
 * Interface IPreprocess
 * @author Michal Červeňák <miso@fykos.cz>
 */
interface IPreprocess {

    public static function calculate(ModelPayment $modelPayment): Price;

    public static function getGridItems(ModelPayment $modelPayment): array;
}
