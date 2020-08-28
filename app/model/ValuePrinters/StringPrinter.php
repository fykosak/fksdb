<?php

namespace FKSDB\ValuePrinters;

use Nette\Utils\Html;

/**
 * Class StringPrinter
 * @author Michal Červeňák <miso@fykos.cz>
 */
class StringPrinter extends AbstractValuePrinter {
    /***
     * @param string $value
     * @return Html
     */
    protected function getHtml($value): Html {
        return Html::el('span')->addText($value);
    }
}
