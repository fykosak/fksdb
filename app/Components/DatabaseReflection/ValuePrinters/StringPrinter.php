<?php

namespace FKSDB\Components\DatabaseReflection\ValuePrinters;

use FKSDB\Components\Controls\Badges\NotSetBadge;
use Nette\Utils\Html;

/**
 * Class StringPrinter
 * *
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
