<?php

namespace FKSDB\Components\DatabaseReflection\ValuePrinters;

use FKSDB\Components\Controls\Badges\NotSetBadge;
use Nette\Utils\Html;

/**
 * Class HashPrinter
 * @package FKSDB\Components\DatabaseReflection\ValuePrinters
 */
class HashPrinter extends AbstractValuePrinter {
    /**
     * @param $value
     * @return Html
     */
    protected function getHtml($value): Html {
        if (\is_null($value)) {
            return NotSetBadge::getHtml();
        } else {
            return Html::el('span')->addAttributes(['class' => 'badge badge-success'])->addText(_('Is set'));
        }
    }
}
