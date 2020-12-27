<?php

namespace FKSDB\Models\Payment\PriceCalculator\PreProcess;

use FKSDB\Models\ORM\Models\ModelPayment;
use FKSDB\Models\Payment\Price;

/**
 * Interface IPreprocess
 * @author Michal Červeňák <miso@fykos.cz>
 */
interface IPreprocess {

    public static function calculate(ModelPayment $modelPayment): Price;

    public static function getGridItems(ModelPayment $modelPayment): array;
}
