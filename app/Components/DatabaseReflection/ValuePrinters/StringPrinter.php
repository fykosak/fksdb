<?php

namespace FKSDB\Components\DatabaseReflection\ValuePrinters;

use FKSDB\Components\Controls\Badges\NotSetBadge;
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
