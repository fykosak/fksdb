<?php

namespace FKSDB\Components\Controls\Helpers\ValuePrinters;

use FKSDB\Components\DatabaseReflection\ValuePrinters\AbstractValuePrinter;
use FKSDB\Components\DatabaseReflection\ValuePrinters\DatePrinter;

/**
 * Class DateValueControl
 * @package FKSDB\Components\Controls\Helpers\ValuePrinters
 */
class DateValueControl extends PrimitiveValueControl {
    /**
     * @return AbstractValuePrinter
     */
    protected static function getPrinter(): AbstractValuePrinter {
        return new DatePrinter;
    }
}
