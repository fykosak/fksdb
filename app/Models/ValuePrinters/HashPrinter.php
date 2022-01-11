<?php

namespace FKSDB\Models\ValuePrinters;

use Nette\Utils\Html;

class HashPrinter extends AbstractValuePrinter {
    /**
     * @param string $value
     */
    protected function getHtml($value): Html {
        return Html::el('span')->addAttributes(['class' => 'badge bg-success'])->addText(_('Is set'));
    }
}
