<?php

namespace FKSDB\Components\DatabaseReflection\ValuePrinters;

use FKSDB\Components\Controls\Helpers\Badges\NotSetBadge;
use Nette\Utils\Html;
use function is_null;

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
        if (is_null($value)) {
            return NotSetBadge::getHtml();
        } else {
            return Html::el('span')->addAttributes(['class' => 'badge badge-success'])->addText(_('Is set'));
        }
    }
}
