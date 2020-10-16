<?php

namespace FKSDB\ValuePrinters;

use Nette\Utils\Html;

/**
 * Class HashPrinter
 * @author Michal Červeňák <miso@fykos.cz>
 */
class HashPrinter extends AbstractValuePrinter {
    /**
     * @param string $value
     * @return Html
     */
    protected function getHtml($value): Html {
        return Html::el('span')->addAttributes(['class' => 'badge badge-success'])->addText(_('Is set'));
    }
}
