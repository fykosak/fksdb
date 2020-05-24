<?php

namespace FKSDB\Components\DatabaseReflection\ValuePrinters;

use FKSDB\Components\Controls\Badges\NotSetBadge;
use Nette\Utils\Html;

/**
 * Class BinaryPrinter
 * *
 */
class BinaryPrinter extends AbstractValuePrinter {
    /**
     * @param int|bool|null $value
     * @return Html
     */
    protected function getHtml($value): Html {
        if (\is_null($value)) {
            return NotSetBadge::getHtml();
        } elseif ($value) {
            return Html::el('span')->addAttributes(['class' => 'fa fa-check text-success']);
        } else {
            return Html::el('span')->addAttributes(['class' => 'fa fa-times text-danger']);
        }
    }
}
