<?php

namespace FKSDB\Payment\PriceCalculator\PreProcess;

use FKSDB\ORM\Models\ModelPayment;
use FKSDB\Payment\Price;

/**
 * Interface IPreprocess
 * @author Michal Červeňák <miso@fykos.cz>
 */
interface IPreprocess {

    public static function calculate(ModelPayment $modelPayment): Price;

    public static function getGridItems(ModelPayment $modelPayment): array;
}
