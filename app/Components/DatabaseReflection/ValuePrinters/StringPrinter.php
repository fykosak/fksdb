<?php

namespace FKSDB\Components\DatabaseReflection\ValuePrinters;

use FKSDB\Components\Controls\Helpers\Badges\NotSetBadge;
use Nette\Utils\Html;

/**
 * Class StringPrinter
 * @package FKSDB\Components\DatabaseReflection\ValuePrinters
 */
class StringPrinter extends AbstractValuePrinter {
    /**
     * @param $value
     * @return Html
     */
    protected function getHtml($value): Html {
        if (\is_null($value)) {
            return NotSetBadge::getHtml();
        } else {
            return Html::el('span')->addText($value);
        }
    }
}
