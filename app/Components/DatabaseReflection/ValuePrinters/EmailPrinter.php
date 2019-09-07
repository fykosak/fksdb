<?php


namespace FKSDB\Components\DatabaseReflection\ValuePrinters;

use FKSDB\Components\Controls\Helpers\Badges\NotSetBadge;
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
        if (\is_null($value)) {
            return NotSetBadge::getHtml();
        }else{
            return Html::el('a')->addAttributes(['href' => 'mailto:' . $value])->addText($value);
        }
    }
}
