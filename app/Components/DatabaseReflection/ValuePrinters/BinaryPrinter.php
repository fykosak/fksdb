<?php

namespace FKSDB\Components\DatabaseReflection\ValuePrinters;

use Nette\Utils\Html;

/**
 * Class BinaryPrinter
 * @author Michal Červeňák <miso@fykos.cz>
 */
class BinaryPrinter extends AbstractValuePrinter {
    /**
     * @param int|bool $value
     * @return Html
     */
    protected function getHtml($value): Html {
        if ($value) {
            return Html::el('span')->addAttributes(['class' => 'fa fa-check text-success']);
        } else {
            return Html::el('span')->addAttributes(['class' => 'fa fa-times text-danger']);
        }
    }
}
