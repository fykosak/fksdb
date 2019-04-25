<?php


namespace FKSDB\Components\DatabaseReflection\ValuePrinters;

use Nette\Utils\Html;

/**
 * Class EmailPrinter
 * @package FKSDB\Components\DatabaseReflection\ValuePrinters
 */
class EmailPrinter extends AbstractValuePrinter {
    /**
     * @param null $value
     * @return Html
     */
    protected function getHtml($value): Html {
        return Html::el('a')->addAttributes(['href' => 'mailto:' . $value])->addText($value);
    }
}
