<?php

namespace FKSDB\Components\Controls\Helpers\ValuePrinters;

use FKSDB\Components\DatabaseReflection\ValuePrinters\AbstractValuePrinter;
use FKSDB\Components\DatabaseReflection\ValuePrinters\BinaryPrinter;
use Nette\Templating\FileTemplate;

/**
 * Class BinaryValueControl
 * @property FileTemplate $template
 */
class BinaryValueControl extends PrimitiveValueControl {

    /**
     * @return AbstractValuePrinter
     */
    protected static function getPrinter(): AbstractValuePrinter {
        return new BinaryPrinter;
    }
}
